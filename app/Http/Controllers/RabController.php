<?php

namespace App\Http\Controllers;

use App\Exports\RabExport;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\RabTemplateExporter;
use App\Services\BudgetAllocationService;
use Illuminate\Validation\Rule;

class RabController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }
    }

    public function index(Request $request)
    {
        $selectedKomponen = $request->query('komponen');
        $selectedMenu = $request->query('menu');
        $selectedKegiatan = $request->query('kegiatan');

        $base = Rab::query();
        if ($selectedKomponen) $base->where('komponen', $selectedKomponen);
        if ($selectedMenu) $base->where('rincian_menu', $selectedMenu);
        if ($selectedKegiatan) $base->where('kegiatan', $selectedKegiatan);

        $rabs = (clone $base)->latest()->paginate(10)->appends($request->query());

        // Filtered charts datasets
        $byComponent = (clone $base)
            ->select('komponen', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('komponen')
            ->orderByDesc(DB::raw('SUM(total)'))
            ->get();

        $byMenu = (clone $base)
            ->select('rincian_menu', DB::raw('SUM(total) as total'))
            ->groupBy('rincian_menu')
            ->orderByDesc(DB::raw('SUM(total)'))
            ->limit(10)
            ->get();

        $byKegiatan = (clone $base)
            ->select('kegiatan', DB::raw('SUM(total) as total'))
            ->groupBy('kegiatan')
            ->orderByDesc(DB::raw('SUM(total)'))
            ->limit(10)
            ->get();

        // Filter lists (dependent)
        $componentsList = Rab::select('komponen')->distinct()->orderBy('komponen')->pluck('komponen');
        $menuList = Rab::when($selectedKomponen, fn($q) => $q->where('komponen', $selectedKomponen))
            ->select('rincian_menu')->distinct()->orderBy('rincian_menu')->pluck('rincian_menu');
        $kegiatanList = Rab::when($selectedKomponen, fn($q) => $q->where('komponen', $selectedKomponen))
            ->when($selectedMenu, fn($q) => $q->where('rincian_menu', $selectedMenu))
            ->select('kegiatan')->distinct()->orderBy('kegiatan')->pluck('kegiatan');

        return view('rabs.index', compact(
            'rabs', 'byComponent', 'byMenu', 'byKegiatan',
            'componentsList', 'menuList', 'kegiatanList',
            'selectedKomponen', 'selectedMenu', 'selectedKegiatan'
        ));
    }

    public function create()
    {
        $this->ensureSuperAdmin();
        $components = \App\Models\Rab::components();
        return view('rabs.create', compact('components'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'komponen' => ['required','string','max:255', Rule::in(array_values(Rab::components()))],
            'rab_menu_id' => ['nullable','integer','exists:rab_menus,id'],
            'rab_kegiatan_id' => ['nullable','integer','exists:rab_kegiatans,id', Rule::unique('rabs','rab_kegiatan_id')],
            'rincian_menu' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.label' => 'required|string|max:255',
            'items.*.type' => 'nullable|string|max:100',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.factors' => 'nullable|array',
            'items.*.factors.*.label' => 'nullable|string|max:100',
            'items.*.factors.*.key' => 'nullable|string|max:100',
            'items.*.factors.*.value' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $rab = Rab::create([
                'komponen' => $validated['komponen'],
                'rab_menu_id' => $validated['rab_menu_id'] ?? null,
                'rab_kegiatan_id' => $validated['rab_kegiatan_id'] ?? null,
                'rincian_menu' => $validated['rincian_menu'],
                'kegiatan' => $validated['kegiatan'],
                'total' => 0,
                'created_by' => Auth::id(),
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $factors = $item['factors'] ?? [];
                // normalize factors to ensure numeric values
                $normalized = [];
                foreach ($factors as $f) {
                    if (!is_array($f)) continue;
                    $normalized[] = [
                        'key' => $f['key'] ?? ($f['label'] ?? ''),
                        'label' => $f['label'] ?? ($f['key'] ?? ''),
                        'value' => isset($f['value']) ? (float) $f['value'] : 0,
                    ];
                }

                $rabItem = new RabItem([
                    'label' => $item['label'],
                    'type' => $item['type'] ?? null,
                    'factors' => $normalized,
                    'unit_price' => (float) $item['unit_price'],
                ]);
                $rabItem->subtotal = $rabItem->computeSubtotal();
                $rab->items()->save($rabItem);
                $total += $rabItem->subtotal;
            }

            $rab->total = $total;
            $rab->save();

            // Ensure Activity exists for this kegiatan
            $this->ensureActivityExists($rab->kegiatan);

            // Auto-create/update Budget Allocation for current year
            app(BudgetAllocationService::class)->ensureForRab($rab, (int) date('Y'));

            return redirect()->route('rabs.show', $rab)
                ->with('success', 'RAB berhasil dibuat.');
        });
    }

    public function show(Rab $rab)
    {
        $rab->load('items');
        return view('rabs.show', compact('rab'));
    }

    public function edit(Rab $rab)
    {
        $this->ensureSuperAdmin();
        $rab->load('items');
        $components = \App\Models\Rab::components();
        return view('rabs.edit', compact('rab','components'));
    }

    public function update(Request $request, Rab $rab)
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'komponen' => ['required','string','max:255', Rule::in(array_values(Rab::components()))],
            'rab_menu_id' => ['nullable','integer','exists:rab_menus,id'],
            'rab_kegiatan_id' => ['nullable','integer','exists:rab_kegiatans,id', Rule::unique('rabs','rab_kegiatan_id')->ignore($rab->id)],
            'rincian_menu' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.label' => 'required|string|max:255',
            'items.*.type' => 'nullable|string|max:100',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.factors' => 'nullable|array',
            'items.*.factors.*.label' => 'nullable|string|max:100',
            'items.*.factors.*.key' => 'nullable|string|max:100',
            'items.*.factors.*.value' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $rab) {
            $rab->update([
                'komponen' => $validated['komponen'],
                'rab_menu_id' => $validated['rab_menu_id'] ?? null,
                'rab_kegiatan_id' => $validated['rab_kegiatan_id'] ?? null,
                'rincian_menu' => $validated['rincian_menu'],
                'kegiatan' => $validated['kegiatan'],
            ]);

            // Replace all items for simplicity
            $rab->items()->delete();

            $total = 0;
            foreach ($validated['items'] as $item) {
                $factors = $item['factors'] ?? [];
                $normalized = [];
                foreach ($factors as $f) {
                    if (!is_array($f)) continue;
                    $normalized[] = [
                        'key' => $f['key'] ?? ($f['label'] ?? ''),
                        'label' => $f['label'] ?? ($f['key'] ?? ''),
                        'value' => isset($f['value']) ? (float) $f['value'] : 0,
                    ];
                }

                $rabItem = new RabItem([
                    'label' => $item['label'],
                    'type' => $item['type'] ?? null,
                    'factors' => $normalized,
                    'unit_price' => (float) $item['unit_price'],
                ]);
                $rabItem->subtotal = $rabItem->computeSubtotal();
                $rab->items()->save($rabItem);
                $total += $rabItem->subtotal;
            }

            $rab->total = $total;
            $rab->save();

            // Ensure Activity exists for this kegiatan
            $this->ensureActivityExists($rab->kegiatan);

            // Sync Budget Allocation amount with updated RAB total (current year)
            app(BudgetAllocationService::class)->ensureForRab($rab, (int) date('Y'));

            return redirect()->route('rabs.show', $rab)
                ->with('success', 'RAB berhasil diperbarui.');
        });
    }

    private function ensureActivityExists(?string $name): void
    {
        $name = trim((string) $name);
        if ($name === '') return;
        Activity::firstOrCreate(['nama' => $name], ['nama' => $name]);
    }

    public function destroy(Rab $rab)
    {
        $this->ensureSuperAdmin();
        $rab->delete();
        return redirect()->route('rabs.index')->with('success', 'RAB berhasil dihapus.');
    }

    public function export(Rab $rab)
    {
        $this->ensureSuperAdmin();
        $rab->load('items');
        $filename = 'RAB_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $rab->kegiatan) . '.xlsx';
        $templatePath = resource_path('templates/templaterab.xlsx');
        if (file_exists($templatePath)) {
            return app(RabTemplateExporter::class)->download($rab, $filename);
        }
        // Fallback to generic export view
        return Excel::download(new RabExport($rab), $filename);
    }

    // API: Get RAB info by kegiatan name
    public function infoByKegiatan(Request $request)
    {
        $name = trim((string) $request->query('kegiatan', ''));
        if ($name === '') {
            return response()->json(['data' => null]);
        }
        $rab = Rab::where('kegiatan', $name)->latest()->with('items')->first();
        if (!$rab) {
            return response()->json(['data' => null]);
        }
        $transport = null;
        $perDiem = null;
        $orang = null;

        foreach ($rab->items as $item) {
            $label = strtolower((string) $item->label);
            $type = strtolower((string) ($item->type ?? ''));
            // Capture transport unit price (prefer items marked transport_*)
            if (str_contains($label, 'transport') || str_starts_with($type, 'transport')) {
                $transport = max($transport ?? 0, (float) $item->unit_price);
            }
            // Capture per diem / uang harian
            if (str_contains($label, 'harian') || $type === 'uang_harian') {
                $perDiem = max($perDiem ?? 0, (float) $item->unit_price);
            }
            // Find factor orang
            if (is_array($item->factors)) {
                foreach ($item->factors as $f) {
                    $flabel = strtolower((string) ($f['label'] ?? $f['key'] ?? ''));
                    if (str_contains($flabel, 'orang')) {
                        $val = (int) round((float) ($f['value'] ?? 0));
                        $orang = max($orang ?? 0, $val);
                    }
                }
            }
        }

        return response()->json([
            'data' => [
                'rab_id' => $rab->id,
                'transport_unit_price' => $transport,
                'per_diem_rate' => $perDiem,
                'orang' => $orang,
            ]
        ]);
    }

    // API: basic info by RAB id (total & estimated occurrences)
    public function basic(Rab $rab)
    {
        $occ = 0;
        $participantLimit = 0;
        foreach ($rab->items as $item) {
            if (!is_array($item->factors)) continue;
            foreach ($item->factors as $f) {
                $label = strtolower((string) ($f['label'] ?? $f['key'] ?? ''));
                $val = (int) round((float) ($f['value'] ?? 0));
                if (str_contains($label, 'kali')) {
                    $occ = max($occ, $val);
                }
                if (str_contains($label, 'orang')) {
                    $participantLimit = max($participantLimit, $val);
                }
            }
        }
        return response()->json([
            'data' => [
                'rab_id' => $rab->id,
                'kegiatan' => $rab->kegiatan,
                'total' => (float) $rab->total,
                'estimated_occurrences' => $occ,
                'participant_limit' => $participantLimit,
            ]
        ]);
    }
}
