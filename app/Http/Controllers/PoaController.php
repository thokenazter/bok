<?php

namespace App\Http\Controllers;

use App\Models\Poa;
use App\Models\Rab;
use App\Models\AnnualBudget;
use App\Models\Employee;
use App\Models\Lpj;
use App\Models\User;
use App\Models\Village;
use App\Models\GlobalHoliday;
use App\Helpers\DateHelper;
use App\Models\BudgetAllocation;
use App\Services\TibaBerangkatService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PoaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $month = (int) $request->query('month', 0);
        $selectedKomponen = trim((string) $request->query('komponen', ''));
        $selectedMenu = trim((string) $request->query('rincian_menu', ''));
        $selectedMonth = ($month >= 1 && $month <= 12) ? $month : null;
        $user = $request->user();
        $employeeId = $user?->employee_id;

        $poas = Poa::with(['budget', 'participants.employee', 'rab.items'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('kegiatan', 'like', "%$q%");
            })
            ->when($selectedKomponen !== '', function ($query) use ($selectedKomponen) {
                $query->whereHas('rab', fn($q) => $q->where('komponen', $selectedKomponen));
            })
            ->when($selectedMenu !== '', function ($query) use ($selectedMenu) {
                $query->whereHas('rab', fn($q) => $q->where('rincian_menu', $selectedMenu));
            })
            ->when($month >= 1 && $month <= 12, function ($query) use ($month) {
                $pathCount = "schedule->months->{$month}->count";
                $pathAmount = "schedule->months->{$month}->amount";
                $pathClaim = "schedule->months->{$month}->claimed_at";
                $pathMarked = "schedule->months->{$month}->marked";
                $query->where(function($q) use ($pathCount, $pathAmount, $pathClaim, $pathMarked) {
                    $q->where($pathCount, '>', 0)
                      ->orWhere($pathAmount, '>', 0)
                      ->orWhereNotNull($pathClaim)
                      ->orWhere($pathMarked, true);
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        // Reorder current page for selected month:
        // - Assigned & Not Claimed first (priority 2)
        // - Assigned & Claimed next (priority 1)
        // - Others last (priority 0)
        if ($selectedMonth && $employeeId) {
            $collection = $poas->getCollection();
            $sorted = $collection->sortByDesc(function($p) use ($selectedMonth, $employeeId) {
                $schedule = $p->schedule ?: [];
                $months = $schedule['months'] ?? [];
                $meta = $months[$selectedMonth] ?? [];
                $pids = collect($meta['participant_ids'] ?? [])->map(fn($id)=>(int)$id)->filter()->unique()->values()->all();
                $general = $p->participants->pluck('employee_id')->filter()->map(fn($id)=>(int)$id)->unique()->values()->all();
                $effective = !empty($pids) ? $pids : $general;
                $assigned = !empty($effective) && in_array((int)$employeeId, $effective, true);
                $claimed = !empty($meta['claimed_at']) || !empty($meta['sppt_lpj_id']) || !empty($meta['sppd_lpj_id']);
                if ($assigned && !$claimed) return 2;
                if ($assigned && $claimed) return 1;
                return 0;
            });
            $poas->setCollection($sorted->values());
        }

        // Enrich items for hover popover (participants, targets, optional items)
        $collection = $poas->getCollection();
        $collection->transform(function($p) use ($selectedMonth) {
            // Participants list (names) — prefer month-specific participants if selected
            $schedule = $p->schedule ?: [];
            $months = $schedule['months'] ?? [];
            $meta = $selectedMonth ? ($months[$selectedMonth] ?? []) : [];
            $monthPids = collect($meta['participant_ids'] ?? [])->map(fn($id) => (int) $id)->filter()->unique()->values()->all();
            $generalIds = $p->participants->pluck('employee_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all();
            $effectiveIds = !empty($monthPids) ? $monthPids : $generalIds;

            $names = [];
            if (!empty($effectiveIds)) {
                // Try resolve via relation first
                $byId = $p->participants
                    ->filter(fn($row) => in_array((int) $row->employee_id, $effectiveIds, true))
                    ->mapWithKeys(fn($row) => [(int) $row->employee_id => (string) ($row->employee?->nama ?? '')])
                    ->filter();
                $missing = array_values(array_diff($effectiveIds, array_map('intval', array_keys($byId->toArray()))));
                if (!empty($missing)) {
                    $extra = \App\Models\Employee::whereIn('id', $missing)->pluck('nama', 'id')->toArray();
                    $byId = $byId->toArray() + $extra;
                } else {
                    $byId = $byId->toArray();
                }
                $names = array_values(array_filter(array_map(fn($id) => $byId[$id] ?? null, $effectiveIds)));
            }
            $p->hover_participants = $names;
            // Village targets (darat / seberang) inferred from RAB items
            try {
                $p->hover_targets = $this->computeRabVillageTargets($p);
            } catch (\Throwable $e) {
                $p->hover_targets = ['darat' => null, 'seberang' => null];
            }
            // Optional items (snack, konsumsi, penggandaan, dll)
            $optionalTypes = ['snack', 'konsumsi', 'penggandaan', 'bahan_makanan', 'lainnya', 'transport_peserta'];
            $items = collect($p->rab?->items ?? []);
            $labels = $items->map(function($i) use ($optionalTypes) {
                $type = strtolower((string) ($i->type ?? ''));
                $label = (string) ($i->label ?? '');
                $labelLower = mb_strtolower($label, 'UTF-8');
                $isOptional = in_array($type, $optionalTypes, true);
                if (!$isOptional) {
                    foreach ($optionalTypes as $t) {
                        if (str_contains($labelLower, str_replace('_', ' ', $t))) { $isOptional = true; break; }
                    }
                }
                return $isOptional ? trim($label) : null;
            })->filter()->unique()->values();
            $p->hover_optional_items = $labels->take(12)->values()->all();
            return $p;
        });
        $poas->setCollection($collection);

        if ($request->ajax() || $request->boolean('ajax')) {
            return response()->view('poa._rows', [
                'poas' => $poas,
                'selectedMonth' => $selectedMonth,
            ]);
        }

        // Build per-month counts for current user
        // - userMonthCountsEligible: participant-aware and NOT claimed yet (eligible to claim)
        // - userMonthCountsClaimed: participant-aware and already claimed
        $userMonthCountsEligible = array_fill(1, 12, 0);
        $userMonthCountsClaimed = array_fill(1, 12, 0);
        if ($employeeId) {
            $allPoas = Poa::with('participants')->get();
            foreach ($allPoas as $p) {
                $schedule = $p->schedule ?: [];
                $months = $schedule['months'] ?? [];
                // Precompute participants general
                $general = $p->participants->pluck('employee_id')->filter()->map(fn($id)=>(int)$id)->unique()->values()->all();
                for ($mth=1; $mth<=12; $mth++) {
                    $meta = $months[$mth] ?? [];
                    $has = ((int)($meta['count'] ?? 0) > 0)
                        || ((float)($meta['amount'] ?? 0) > 0)
                        || !empty($meta['claimed_at'])
                        || !empty($meta['sppt_lpj_id'])
                        || !empty($meta['sppd_lpj_id'])
                        || !empty($meta['marked']);
                    if (!$has) continue;
                    $pids = collect($meta['participant_ids'] ?? [])->map(fn($id)=>(int)$id)->filter()->unique()->values()->all();
                    $effective = !empty($pids) ? $pids : $general;
                    $assigned = !empty($effective) && in_array((int)$employeeId, $effective, true);
                    $claimed = !empty($meta['claimed_at']) || !empty($meta['sppt_lpj_id']) || !empty($meta['sppd_lpj_id']);
                    if ($assigned) {
                        if ($claimed) {
                            $userMonthCountsClaimed[$mth]++;
                        } else {
                            $userMonthCountsEligible[$mth]++;
                        }
                    }
                }
            }
        }

        // Build filter lists for super admin: komponen & rincian menu
        $availableKomponen = \App\Models\Rab::query()
            ->select('komponen')
            ->whereNotNull('komponen')
            ->distinct()
            ->orderBy('komponen')
            ->pluck('komponen');
        $availableMenus = \App\Models\Rab::query()
            ->select('rincian_menu')
            ->whereNotNull('rincian_menu')
            ->distinct()
            ->orderBy('rincian_menu')
            ->pluck('rincian_menu');

        $selectedMonth = ($month >= 1 && $month <= 12) ? $month : null;
        return view('poa.index', compact(
            'poas',
            'selectedMonth',
            'userMonthCountsEligible',
            'userMonthCountsClaimed',
            'availableKomponen',
            'availableMenus',
            'selectedKomponen',
            'selectedMenu',
        ));
    }

    public function create()
    {
        $years = range(date('Y') - 1, date('Y') + 1);
        $budgets = AnnualBudget::orderByDesc('year')->get();
        // Filter RAB agar tidak menduplikasi POA untuk tahun terpilih (default tahun berjalan)
        $selectedYear = (int) request('year', (int) old('year', (int) date('Y')));
        $usedRabIds = Poa::where('year', $selectedYear)->pluck('rab_id');
        $rabs = Rab::whereNotIn('id', $usedRabIds)->orderByDesc('created_at')->limit(500)->get();
        $employees = Employee::orderBy('nama')->get();
        $participantLimit = 0;

        return view('poa.create', compact('years', 'budgets', 'rabs', 'employees', 'participantLimit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'annual_budget_id' => 'nullable|exists:annual_budgets,id',
            'rab_id' => [
                'required',
                'exists:rabs,id',
                Rule::unique('poas', 'rab_id')->where(fn($q) => $q->where('year', $request->input('year'))),
            ],
            'nomor_surat' => 'nullable|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'output_target' => 'nullable|string',
            'planned_total' => 'required|numeric|min:0',
            'schedule' => 'nullable|array',
            'participants' => 'nullable|array',
            'participants.*.employee_id' => 'required|exists:employees,id',
            'participants.*.role' => 'nullable|string|max:50',
            'participants.*.borrowed_employee_id' => 'nullable|exists:employees,id',
            'participants.*.note' => 'nullable|string',
        ]);

        $rab = Rab::with('items')->find($validated['rab_id']);
        $participantLimit = $this->computeRabParticipantLimit($rab);
        if ($participantLimit > 0) {
            $participantCount = collect($validated['participants'] ?? [])
                ->pluck('employee_id')
                ->filter()
                ->unique()
                ->count();
            if ($participantCount > $participantLimit) {
                return back()->withInput()->withErrors([
                    'participants' => 'Jumlah peserta melebihi kuota RAB (' . $participantLimit . ' orang).',
                ]);
            }
        }

        return DB::transaction(function () use ($validated) {
            $poa = Poa::create([
                'year' => $validated['year'],
                'annual_budget_id' => $validated['annual_budget_id'] ?? null,
                'rab_id' => $validated['rab_id'],
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'kegiatan' => $validated['kegiatan'],
                'output_target' => $validated['output_target'] ?? null,
                'schedule' => $validated['schedule'] ?? null,
                'planned_total' => (float) $validated['planned_total'],
                'created_by' => Auth::id(),
            ]);

            $participantsData = collect($validated['participants'] ?? [])->unique(fn($row) => $row['employee_id'] ?? null)->values();
            foreach ($participantsData as $p) {
                $poa->participants()->create([
                    'employee_id' => $p['employee_id'],
                    'role' => $p['role'] ?? null,
                    'borrowed_employee_id' => $p['borrowed_employee_id'] ?? null,
                    'note' => $p['note'] ?? null,
                ]);
            }

            return redirect()->route('poa.show', $poa)->with('success', 'POA berhasil dibuat.');
        });
    }

    

    public function show(Poa $poa)
    {
        $poa->load(['rab.items', 'budget', 'participants.employee', 'participants.borrowedEmployee']);

        $currentUser = Auth::user();
        $userIsAdmin = $currentUser && ($currentUser->isSuperAdmin() || $currentUser->email === 'admin@admin.com');
        $currentEmployeeId = $currentUser?->employee_id;

        $schedule = $poa->schedule ?: ['months' => []];
        $monthMeta = $schedule['months'] ?? [];

        // Semua user yang sudah login dapat melihat detail POA.
        // Akses klaim tetap dibatasi saat perhitungan claimStatus/route claim.

        $participantLimit = $this->computeRabParticipantLimit($poa->rab);
        $rabTargets = $this->computeRabVillageTargets($poa);
        $employees = Employee::orderBy('nama')->get();
        $employeeMap = $employees->keyBy('id');
        $villages = Village::orderBy('nama')->get();

        $claimStatus = [];
        for ($m = 1; $m <= 12; $m++) {
            $meta = $monthMeta[$m] ?? [];
            $participantIds = collect($meta['participant_ids'] ?? [])->map(fn($id) => (int) $id)->filter()->unique()->values()->all();
            $effectiveParticipantIds = $participantIds;
            if (count($effectiveParticipantIds) === 0) {
                $effectiveParticipantIds = $poa->participants->pluck('employee_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all();
            }
            $claimed = !empty($meta['claimed_at']) || !empty($meta['sppt_lpj_id']) || !empty($meta['sppd_lpj_id']);
            $claimedHidden = (bool) ($meta['claimed_hidden'] ?? false);
            $locked = (bool) ($meta['locked'] ?? false);
            $assigned = $currentEmployeeId ? in_array($currentEmployeeId, $effectiveParticipantIds, true) : false;
            $claimStatus[$m] = [
                'participant_ids' => $participantIds,
                'assigned' => $assigned,
                'claimed' => $claimed,
                'claimed_hidden' => $claimedHidden,
                'locked' => $locked,
                'claimed_visible' => $claimed && !$claimedHidden,
                'claimed_at' => $meta['claimed_at'] ?? null,
                'claimed_by' => $meta['claimed_by'] ?? null,
                'sppt_lpj_id' => $meta['sppt_lpj_id'] ?? null,
                'sppd_lpj_id' => $meta['sppd_lpj_id'] ?? null,
                'allowed' => ($userIsAdmin || $assigned) && !$claimed && (!$locked || $userIsAdmin),
                'no_surat_sppt' => $meta['no_surat_sppt'] ?? null,
                'no_surat_sppd' => $meta['no_surat_sppd'] ?? null,
                'tanggal_kegiatan_sppt' => $meta['tanggal_kegiatan_sppt'] ?? null,
                'tanggal_kegiatan_sppd' => $meta['tanggal_kegiatan_sppd'] ?? null,
                'tanggal_surat_sppt' => $meta['tanggal_surat_sppt'] ?? null,
                'tanggal_surat_sppd' => $meta['tanggal_surat_sppd'] ?? null,
            ];
        }

        $claimUserIds = collect($claimStatus)->pluck('claimed_by')->filter()->unique()->values()->all();
        $claimUsers = $claimUserIds ? User::whereIn('id', $claimUserIds)->get()->keyBy('id') : collect();

        $executed = array_fill(1, 12, 0);
        $executedDetails = array_fill(1, 12, null);
        $executedSums = array_fill(1, 12, 0.0);
        $lpjs = Lpj::where('kegiatan', $poa->kegiatan)
            ->with(['participants.employee', 'villages'])
            ->get();
        foreach ($lpjs as $l) {
            $my = DateHelper::getMonthYearFromActivity($l->tanggal_kegiatan) ?? DateHelper::getMonthYearFromDocument($l->tanggal_surat);
            $month = null;
            $year = null;
            if ($my) {
                $month = $my['month'];
                $year = $my['year'];
            } else {
                $month = (int) $l->created_at->month;
                $year = (int) $l->created_at->year;
            }
            if ($year !== (int) $poa->year || $month < 1 || $month > 12) {
                continue;
            }
            $executed[$month]++;

            if (!is_array($executedDetails[$month])) {
                $executedDetails[$month] = ['lpjs' => []];
            }

            $lpjVillages = [];
            $type = strtoupper((string) ($l->type ?? ''));
            if ($type === 'SPPT') {
                $text = trim((string) ($l->desa_tujuan_darat ?? ''));
                if ($text !== '') {
                    $lpjVillages = array_values(array_filter(array_map('trim', preg_split('/,|dan/i', $text))));
                } elseif ($l->relationLoaded('villages') && $l->villages && $l->villages->count()) {
                    $lpjVillages = $l->villages->pluck('nama')->filter()->values()->all();
                }
            } elseif ($type === 'SPPD') {
                $text = trim((string) ($l->desa_tujuan_seberang ?? ''));
                if ($text !== '') {
                    $lpjVillages = array_values(array_filter(array_map('trim', preg_split('/,|dan/i', $text))));
                } elseif ($l->relationLoaded('villages') && $l->villages && $l->villages->count()) {
                    $lpjVillages = $l->villages->pluck('nama')->filter()->values()->all();
                }
            } else {
                $text = trim((string) (($l->desa_tujuan_darat ?? '') . ' ' . ($l->desa_tujuan_seberang ?? '')));
                if ($text !== '') {
                    $lpjVillages = array_values(array_filter(array_map('trim', preg_split('/,|dan/i', $text))));
                } elseif ($l->relationLoaded('villages') && $l->villages && $l->villages->count()) {
                    $lpjVillages = $l->villages->pluck('nama')->filter()->values()->all();
                }
            }
            $lpjVillages = array_values(array_unique(array_map(function ($v) {
                $v = trim($v);
                $v = preg_replace('/^Desa\s+/i', '', $v);
                return $v;
            }, $lpjVillages)));

            $participants = $l->participants->map(fn($p) => $p->employee?->nama)->filter()->values()->all();
            $lpjAmount = (float) $l->participants->sum('total_amount');
            $executedSums[$month] += $lpjAmount;
            $executedDetails[$month]['lpjs'][] = [
                'id' => $l->id,
                'type' => $l->type ?? '-',
                'created_by' => $l->created_by,
                'tanggal' => $l->tanggal_kegiatan ?: ($l->tanggal_surat ?: $l->created_at->format('d F Y')),
                'participants' => $participants,
                'villages' => $lpjVillages,
                'amount' => $lpjAmount,
            ];
        }

        $allocation = BudgetAllocation::with('budget')
            ->where('rab_id', $poa->rab_id)
            ->whereHas('budget', fn($q) => $q->where('year', (int) $poa->year))
            ->latest()
            ->first();
        $allocatedAmount = $allocation ? (float) $allocation->allocated_amount : null;
        $realizedAmount = Lpj::where('kegiatan', $poa->kegiatan)
            ->whereYear('created_at', (int) $poa->year)
            ->with('participants')
            ->get()
            ->sum(fn($lpj) => $lpj->participants->sum('total_amount'));
        $remainingAmount = is_null($allocatedAmount) ? null : ($allocatedAmount - (float) $realizedAmount);

        $progress = $poa->item_progress['items'] ?? [];
        $progressMonths = $poa->item_progress['months'] ?? [];
        $rabItemsDetails = collect($poa->rab?->items ?? [])->map(function ($i) use ($progress) {
            $phrase = collect($i->factors ?? [])->map(function ($f) {
                $label = $f['label'] ?? ($f['key'] ?? '-');
                $value = (float)($f['value'] ?? 0);
                return $label . ' x ' . $value;
            })->implode(' × ');
            $prog = $progress[$i->id] ?? null;
            $type = (string) ($i->type ?? '');
            $labelLower = mb_strtolower((string) $i->label, 'UTF-8');
            $optionalTypes = ['snack', 'konsumsi', 'penggandaan', 'bahan_makanan', 'lainnya', 'transport_peserta'];
            $isOptional = in_array($type, $optionalTypes, true);
            if (!$isOptional) {
                foreach ($optionalTypes as $t) {
                    if (str_contains($labelLower, str_replace('_', ' ', $t))) {
                        $isOptional = true;
                        break;
                    }
                }
            }
            return [
                'id' => (int) $i->id,
                'label' => (string) $i->label,
                'type' => (string) ($i->type ?? ''),
                'unit_price' => (float) $i->unit_price,
                'factors_phrase' => $phrase,
                'completed' => (bool) ($prog['completed'] ?? false),
                'absorbed_amount' => (float) ($prog['absorbed_amount'] ?? 0),
                'is_optional' => $isOptional,
            ];
        });
        $optionalItemsDetails = $rabItemsDetails->filter(fn($ri) => !empty($ri['is_optional']))->values();
        $optionalItemsLabels = $optionalItemsDetails->pluck('label')->filter()->values()->all();

        $progressSums = array_fill(1, 12, 0.0);
        foreach ($progressMonths as $month => $pm) {
            if (!is_numeric($month)) {
                continue;
            }
            $m = (int) $month;
            if ($m < 1 || $m > 12) {
                continue;
            }
            $items = $pm['items'] ?? [];
            $sum = 0.0;
            foreach ($items as $pi) {
                $sum += (float) ($pi['absorbed_amount'] ?? 0);
            }
            $progressSums[$m] = $sum;
        }

        // Estimate optional item amounts per month when no absorbed amount recorded
        $countsByMonth = [];
        $sumCounts = 0;
        for ($mm=1; $mm<=12; $mm++) {
            $cnt = (int) ($poa->schedule['months'][$mm]['count'] ?? 0);
            $countsByMonth[$mm] = $cnt;
            $sumCounts += $cnt;
        }
        $rabItemMap = collect($poa->rab?->items ?? [])->keyBy('id');
        $optionalEstimatesByMonth = array_fill(1, 12, []);
        foreach ($optionalItemsDetails as $ri) {
            $raw = $rabItemMap->get($ri['id']);
            $unit = (float) ($ri['unit_price'] ?? 0);
            $factors = collect($raw?->factors ?? []);
            $hasKaliKegiatan = false;
            $multOther = 1.0;
            $multAll = 1.0;
            foreach ($factors as $f) {
                $label = strtolower((string) ($f['label'] ?? $f['key'] ?? ''));
                $val = (float) ($f['value'] ?? 0);
                $multAll *= max(0.0, $val);
                if (str_contains($label, 'kali') && str_contains($label, 'kegiatan')) {
                    $hasKaliKegiatan = true;
                } else {
                    $multOther *= max(0.0, $val);
                }
            }
            $yearlyTotal = $unit * $multAll;
            // Count how many months are marked completed for this item in item_progress
            $completedMonthsCount = 0;
            for ($mm=1; $mm<=12; $mm++) {
                $pi = $progressMonths[$mm]['items'][$ri['id']] ?? null;
                if (!empty($pi['completed'])) {
                    $completedMonthsCount++;
                }
            }
            for ($mm=1; $mm<=12; $mm++) {
                $c = (int) ($countsByMonth[$mm] ?? 0);
                $pi = $progressMonths[$mm]['items'][$ri['id']] ?? null;
                $completed = !empty($pi['completed']);
                $est = 0.0;
                if ($hasKaliKegiatan) {
                    // Monthly = unit * other factors * monthly schedule count
                    // If no schedule count, but marked completed, assume count = 1
                    $effCount = $c > 0 ? $c : ($completed ? 1 : 0);
                    $est = $unit * $multOther * max(0, $effCount);
                } else {
                    // Distribute yearly total proportionally by schedule counts
                    if ($sumCounts > 0) {
                        $est = $yearlyTotal * ($c / $sumCounts);
                    } else {
                        // No schedule counts provided: distribute equally across completed months
                        if ($completed && $completedMonthsCount > 0) {
                            $est = $yearlyTotal / $completedMonthsCount;
                        } else {
                            $est = 0.0;
                        }
                    }
                }
                $optionalEstimatesByMonth[$mm][$ri['id']] = (float) $est;
            }
        }

        return view('poa.show', compact(
                'poa',
                'executed',
                'executedDetails',
                'allocatedAmount',
                'realizedAmount',
                'remainingAmount',
                'rabItemsDetails',
                'optionalItemsDetails',
                'optionalItemsLabels',
                'progressMonths',
                'participantLimit',
                'employeeMap',
                'villages',
                'rabTargets',
                'claimStatus',
                'claimUsers',
                'currentEmployeeId',
                'userIsAdmin',
                'optionalEstimatesByMonth'
            ))
            ->with('executedSums', $executedSums)
            ->with('progressSums', $progressSums);
    }

    public function edit(Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin() && (auth()->user()?->email !== 'admin@admin.com')) {
            abort(403);
        }
        $poa->load(['participants', 'rab.items']);
        $years = range(date('Y') - 1, date('Y') + 1);
        $budgets = AnnualBudget::orderByDesc('year')->get();
        // Filter RAB agar tidak menduplikasi POA di tahun ini, kecuali RAB milik POA ini sendiri
        $usedRabIds = Poa::where('year', (int) $poa->year)
            ->where('id', '!=', $poa->id)
            ->pluck('rab_id');
        $rabs = Rab::whereNotIn('id', $usedRabIds)->orderByDesc('created_at')->limit(500)->get();
        $employees = Employee::orderBy('nama')->get();
        $participantLimit = $this->computeRabParticipantLimit($poa->rab);

        return view('poa.edit', compact('poa', 'years', 'budgets', 'rabs', 'employees', 'participantLimit'));
    }

    public function update(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin() && (auth()->user()?->email !== 'admin@admin.com')) {
            abort(403);
        }
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'annual_budget_id' => 'nullable|exists:annual_budgets,id',
            'rab_id' => [
                'required',
                'exists:rabs,id',
                Rule::unique('poas', 'rab_id')
                    ->ignore($poa->id)
                    ->where(fn($q) => $q->where('year', $request->input('year'))),
            ],
            'nomor_surat' => 'nullable|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'output_target' => 'nullable|string',
            'planned_total' => 'required|numeric|min:0',
            'schedule' => 'nullable|array',
            'participants' => 'nullable|array',
            'participants.*.employee_id' => 'required|exists:employees,id',
            'participants.*.role' => 'nullable|string|max:50',
            'participants.*.borrowed_employee_id' => 'nullable|exists:employees,id',
            'participants.*.note' => 'nullable|string',
        ]);

        $rab = Rab::with('items')->find($validated['rab_id']);
        $participantLimit = $this->computeRabParticipantLimit($rab);
        if ($participantLimit > 0) {
            $participantCount = collect($validated['participants'] ?? [])
                ->pluck('employee_id')
                ->filter()
                ->unique()
                ->count();
            if ($participantCount > $participantLimit) {
                return back()->withInput()->withErrors([
                    'participants' => 'Jumlah peserta melebihi kuota RAB (' . $participantLimit . ' orang).',
                ]);
            }
        }

        return DB::transaction(function () use ($validated, $poa) {
            $poa->update([
                'year' => $validated['year'],
                'annual_budget_id' => $validated['annual_budget_id'] ?? null,
                'rab_id' => $validated['rab_id'],
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'kegiatan' => $validated['kegiatan'],
                'output_target' => $validated['output_target'] ?? null,
                'schedule' => $validated['schedule'] ?? null,
                'planned_total' => (float) $validated['planned_total'],
            ]);

            $poa->participants()->delete();
            $participantsData = collect($validated['participants'] ?? [])->unique(fn($row) => $row['employee_id'] ?? null)->values();
            foreach ($participantsData as $p) {
                $poa->participants()->create([
                    'employee_id' => $p['employee_id'],
                    'role' => $p['role'] ?? null,
                    'borrowed_employee_id' => $p['borrowed_employee_id'] ?? null,
                    'note' => $p['note'] ?? null,
                ]);
            }

            return redirect()->route('poa.show', $poa)->with('success', 'POA berhasil diperbarui.');
        });
    }

    public function destroy(Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin() && (auth()->user()?->email !== 'admin@admin.com')) {
            abort(403);
        }
        $poa->delete();
        return redirect()->route('poa.index')->with('success', 'POA dihapus.');
    }

    public function carryOver(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
        ]);
        $m = (int) $request->month;
        $schedule = $poa->schedule ?: ['months' => []];
        $months = $schedule['months'] ?? [];
        $amt = (float) ($months[$m]['amount'] ?? 0);
        $real = (float) $request->input('realized', 0); // client can pass executedSums[m]
        $delta = $amt - $real; // remaining
        if ($delta > 0) {
            $next = $m == 12 ? 12 : $m + 1;
            $months[$next]['amount'] = (float) (($months[$next]['amount'] ?? 0) + $delta);
            $months[$m]['amount'] = (float) $real; // cap current month to realized
        }
        $schedule['months'] = $months;
        $poa->schedule = $schedule;
        $poa->save();
        return back()->with('success', 'Sisa anggaran bulan dialihkan ke bulan berikutnya.');
    }

    public function toggleMark(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'marked' => 'required|boolean',
        ]);
        $m = (int) $request->month;
        $marked = (bool) $request->marked;
        $schedule = $poa->schedule ?: ['months' => []];
        $months = $schedule['months'] ?? [];
        $months[$m]['marked'] = $marked;
        $schedule['months'] = $months;
        $poa->schedule = $schedule;
        $poa->save();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'month' => $m,
                'marked' => $marked,
            ]);
        }
        return back()->with('success', ($marked ? 'Menandai' : 'Menghapus tanda') . ' bulan sebagai sudah berjalan namun belum LPJ.');
    }

    public function toggleClaimLabel(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'hidden' => 'required|boolean',
        ]);
        $m = (int) $validated['month'];
        $hidden = (bool) $validated['hidden'];
        $schedule = $poa->schedule ?: ['months' => []];
        $months = $schedule['months'] ?? [];
        if (!isset($months[$m]) || !is_array($months[$m])) {
            $months[$m] = [];
        }
        $months[$m]['claimed_hidden'] = $hidden;
        $schedule['months'] = $months;
        $poa->schedule = $schedule;
        $poa->save();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'month' => $m,
                'hidden' => $hidden,
            ]);
        }
        return back()->with('success', $hidden ? 'Label "Sudah Diklaim" disembunyikan.' : 'Label "Sudah Diklaim" ditampilkan.');
    }

    public function toggleClaimLock(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'locked' => 'required|boolean',
        ]);
        $m = (int) $validated['month'];
        $locked = (bool) $validated['locked'];
        $schedule = $poa->schedule ?: ['months' => []];
        $months = $schedule['months'] ?? [];
        if (!isset($months[$m]) || !is_array($months[$m])) {
            $months[$m] = [];
        }
        $months[$m]['locked'] = $locked;
        $schedule['months'] = $months;
        $poa->schedule = $schedule;
        $poa->save();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'month' => $m,
                'locked' => $locked,
            ]);
        }
        return back()->with('success', $locked ? 'Klaim bulan dikunci untuk user.' : 'Klaim bulan dibuka untuk user.');
    }

    // Bulk lock/unlock claim for all POAs by month and year
    public function bulkToggleClaimLock(Request $request)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'locked' => 'required|boolean',
        ]);
        $year = (int) $validated['year'];
        $m = (int) $validated['month'];
        $locked = (bool) $validated['locked'];

        $count = 0;
        \App\Models\Poa::where('year', $year)
            ->orderBy('id')
            ->chunkById(200, function ($batch) use ($m, $locked, &$count) {
                foreach ($batch as $poa) {
                    $schedule = $poa->schedule ?: ['months' => []];
                    $months = $schedule['months'] ?? [];
                    if (!isset($months[$m]) || !is_array($months[$m])) {
                        $months[$m] = [];
                    }
                    $months[$m]['locked'] = $locked;
                    $schedule['months'] = $months;
                    $poa->schedule = $schedule;
                    $poa->save();
                    $count++;
                }
            });

        $monthName = $this->getMonthName($m);
        return back()->with('success', ($locked ? 'Mengunci' : 'Membuka kunci') . ' klaim untuk bulan ' . ($monthName ?: $m) . ' tahun ' . $year . ' pada ' . $count . ' POA.');
    }

    // API: RAB yang belum dipakai POA pada tahun tertentu
    public function availableRabs(Request $request)
    {
        $year = (int) ($request->query('year', date('Y')));
        $includeRabId = (int) ($request->query('include_rab_id', 0));
        $usedRabIds = Poa::where('year', $year)->pluck('rab_id');
        if ($includeRabId > 0) {
            $usedRabIds = $usedRabIds->filter(fn($id) => (int)$id !== $includeRabId);
        }
        $rabs = Rab::whereNotIn('id', $usedRabIds->all())
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get(['id', 'kegiatan', 'rincian_menu', 'komponen']);
        return response()->json([
            'data' => $rabs->map(fn($r) => [
                'id' => $r->id,
                'label' => '#' . $r->id . ' - ' . $r->kegiatan . ' (' . $r->rincian_menu . ' • ' . $r->komponen . ')',
            ]),
        ]);
    }

    public function updateItemProgress(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'rab_item_id' => 'required|integer',
            'completed' => 'required|boolean',
            'absorbed_amount' => 'nullable|numeric|min:0',
        ]);
        // Ensure item belongs to this POA's RAB
        $itemId = (int) $validated['rab_item_id'];
        $belongs = $poa->rab?->items?->firstWhere('id', $itemId);
        if (!$belongs) {
            abort(404);
        }
        $progress = $poa->item_progress ?: [];
        $progress['months'] = $progress['months'] ?? [];
        $progress['months'][(int)$validated['month']]['items'] = $progress['months'][(int)$validated['month']]['items'] ?? [];
        $progress['months'][(int)$validated['month']]['items'][$itemId] = [
            'completed' => (bool) $validated['completed'],
            'absorbed_amount' => (float) ($validated['absorbed_amount'] ?? 0),
        ];
        $poa->item_progress = $progress;
        $poa->save();

        // Credit optional item to employee saldo (penanggung jawab) if applicable
        try {
            $this->upsertOptionalSaldoEntry($poa, (int)$validated['month'], $belongs, (float)($validated['absorbed_amount'] ?? 0));
        } catch (\Throwable $e) {
            // swallow errors, do not block UI
        }

        return back()->with('success', 'Status item berhasil diperbarui.');
    }

    private function resolvePenanggungJawabEmployeeId(Poa $poa, int $month): ?int
    {
        // Prefer LPJ participants in the same month/year (dynamic)
        try {
            $lpjs = \App\Models\Lpj::where('kegiatan', $poa->kegiatan)
                ->with('participants')
                ->orderByDesc('created_at')
                ->get();
            foreach ($lpjs as $l) {
                $my = \App\Helpers\DateHelper::getMonthYearFromActivity($l->tanggal_kegiatan)
                    ?? \App\Helpers\DateHelper::getMonthYearFromDocument($l->tanggal_surat);
                $m = null; $y = null;
                if ($my) { $m = (int) ($my['month'] ?? 0); $y = (int) ($my['year'] ?? 0); }
                else { $m = (int) $l->created_at->month; $y = (int) $l->created_at->year; }
                if ($y !== (int) $poa->year || $m !== (int) $month) continue;
                $pj = $l->participants->first(function ($p) { return $this->roleIsPenanggung($p->role ?? ''); });
                if ($pj?->employee_id) return (int) $pj->employee_id;
            }
        } catch (\Throwable $e) {
            // ignore and fallback to POA participants
        }

        // Fallback to POA participants effective for the month
        $schedule = $poa->schedule ?: [];
        $months = $schedule['months'] ?? [];
        $meta = $months[$month] ?? [];
        $monthPids = collect($meta['participant_ids'] ?? [])->map(fn($id) => (int)$id)->filter()->unique()->values()->all();
        $participants = $poa->participants()->get();
        $effective = !empty($monthPids)
            ? $participants->whereIn('employee_id', $monthPids)
            : $participants;
        $pj = $effective->first(function ($p) { return $this->roleIsPenanggung($p->role ?? ''); });
        if ($pj) {
            $eid = (int) ($pj->employee_id ?: 0);
            if ($eid > 0) return $eid;
            $beid = (int) ($pj->borrowed_employee_id ?: 0);
            if ($beid > 0) return $beid;
        }
        return null;
    }

    private function roleIsPenanggung(string $role): bool
    {
        $r = strtoupper(trim($role));
        if ($r === '') return false;
        // Normalize spaces and punctuation for token detection
        $tokens = preg_split('/[^A-Z0-9]+/', $r, -1, PREG_SPLIT_NO_EMPTY);
        $tokenSet = collect($tokens)->map(fn($t) => trim($t))->filter()->values();
        // Accept explicit tokens
        if ($tokenSet->contains('PJ')) return true;
        if ($tokenSet->contains('KETUA')) return true;
        // Accept substrings for "PENANGGUNG" or "PENANGGUNGJAWAB"
        if (str_contains($r, 'PENANGGUNG')) return true;
        if (str_contains($r, 'PENANGGUNGJAWAB')) return true;
        return false;
    }

    private function isOptionalItem($rabItem): bool
    {
        $type = strtolower((string) ($rabItem->type ?? ''));
        $labelLower = mb_strtolower((string) ($rabItem->label ?? ''), 'UTF-8');
        $optionalTypes = ['snack', 'konsumsi', 'penggandaan', 'bahan_makanan', 'lainnya', 'transport_peserta'];
        if (in_array($type, $optionalTypes, true)) return true;
        foreach ($optionalTypes as $t) {
            if (str_contains($labelLower, str_replace('_', ' ', $t))) return true;
        }
        return false;
    }

    private function upsertOptionalSaldoEntry(Poa $poa, int $month, $rabItem, float $absorbed): void
    {
        if (!$rabItem || !$this->isOptionalItem($rabItem)) return;
        $employeeId = $this->resolvePenanggungJawabEmployeeId($poa, $month);
        if (!$employeeId) return;

        // Prefer absorbed; fallback to estimated amount for the month when zero
        $amount = max(0.0, (float) $absorbed);
        if ($amount <= 0) {
            $amount = $this->estimateOptionalItemAmountForMonth($poa, $month, $rabItem) ?? 0.0;
        }
        $category = strtolower((string) ($rabItem->type ?? 'opsional')) ?: 'opsional';
        $label = (string) ($rabItem->label ?? 'Item Opsional');

        // If amount is zero, remove existing entry (if any)
        $Entry = \App\Models\EmployeeSaldoEntry::query()
            ->where('employee_id', $employeeId)
            ->where('poa_id', $poa->id)
            ->where('rab_item_id', $rabItem->id)
            ->where('month', $month)
            ->where('year', (int) $poa->year)
            ->first();

        if ($amount <= 0) {
            if ($Entry) $Entry->delete();
            return;
        }

        if ($Entry) {
            $Entry->update([
                'amount' => $amount,
                'category' => $category,
                'label' => $label,
                'description' => 'Kredit item opsional dari POA #' . $poa->id . ' bulan ' . $this->getMonthName($month),
                'created_by' => auth()->id(),
            ]);
        } else {
            \App\Models\EmployeeSaldoEntry::create([
                'employee_id' => $employeeId,
                'poa_id' => $poa->id,
                'rab_item_id' => $rabItem->id,
                'year' => (int) $poa->year,
                'month' => $month,
                'category' => $category,
                'label' => $label,
                'amount' => $amount,
                'description' => 'Kredit item opsional dari POA #' . $poa->id . ' bulan ' . $this->getMonthName($month),
                'created_by' => auth()->id(),
            ]);
        }
    }

    private function estimateOptionalItemAmountForMonth(Poa $poa, int $m, $rabItem): float
    {
        $unit = (float) ($rabItem->unit_price ?? 0);
        $factors = collect($rabItem->factors ?? []);
        $hasKaliKegiatan = false; $multOther = 1.0; $multAll = 1.0;
        foreach ($factors as $f) {
            $label = strtolower((string) ($f['label'] ?? $f['key'] ?? ''));
            $val = (float) ($f['value'] ?? 0);
            $multAll *= max(0.0, $val);
            if (str_contains($label, 'kali') && str_contains($label, 'kegiatan')) {
                $hasKaliKegiatan = true;
            } else {
                $multOther *= max(0.0, $val);
            }
        }
        $countsByMonth = [];
        $sumCounts = 0;
        for ($mm=1; $mm<=12; $mm++) {
            $cnt = (int) ($poa->schedule['months'][$mm]['count'] ?? 0);
            $countsByMonth[$mm] = $cnt; $sumCounts += $cnt;
        }
        $c = (int) ($countsByMonth[$m] ?? 0);
        $yearlyTotal = $unit * $multAll;
        // Determine completion status for this item/month from stored progress
        $pi = $poa->item_progress['months'][$m]['items'][$rabItem->id] ?? null;
        $completed = !empty($pi['completed']);
        if ($hasKaliKegiatan) {
            // If no schedule count, but marked completed, assume count = 1
            $effCount = $c > 0 ? $c : ($completed ? 1 : 0);
            return $unit * $multOther * max(0, $effCount);
        }
        if ($sumCounts > 0) {
            return $yearlyTotal * ($c / $sumCounts);
        }
        // No schedule counts: distribute equally across completed months for this item
        $completedMonthsCount = 0;
        for ($mm=1; $mm<=12; $mm++) {
            $ppi = $poa->item_progress['months'][$mm]['items'][$rabItem->id] ?? null;
            if (!empty($ppi['completed'])) $completedMonthsCount++;
        }
        if ($completed && $completedMonthsCount > 0) {
            return $yearlyTotal / $completedMonthsCount;
        }
        return 0.0;
    }

    private function computeRabParticipantLimit(?Rab $rab): int
    {
        if (!$rab) {
            return 0;
        }

        $limit = 0;
        foreach ($rab->items ?? [] as $item) {
            if (!is_array($item->factors)) {
                continue;
            }
            foreach ($item->factors as $factor) {
                $label = strtolower((string) ($factor['label'] ?? $factor['key'] ?? ''));
                if (!str_contains($label, 'orang')) {
                    continue;
                }
                $value = (int) round((float) ($factor['value'] ?? 0));
                if ($value > $limit) {
                    $limit = $value;
                }
            }
        }

        return $limit;
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $months[(int)$monthNumber] ?? '';
    }

    // Format date list (YYYY-MM-DD) to "14 dan 18 Bulan Tahun"
    private function formatIndoDateList(array $dates): string
    {
        $dates = array_values(array_filter($dates));
        sort($dates);
        if (empty($dates)) return '';
        $carbons = array_map(fn($d) => \Carbon\Carbon::createFromFormat('Y-m-d', $d), $dates);
        $month = $this->getMonthName($carbons[0]->month);
        $year = $carbons[0]->year;
        $days = array_map(fn($c) => $c->day, $carbons);
        if (count($days) === 1) {
            return $days[0] . ' ' . $month . ' ' . $year;
        }
        $head = implode(', ', array_slice($days, 0, -1));
        $last = end($days);
        return ($head ? ($head . ' dan ') : '') . $last . ' ' . $month . ' ' . $year;
    }

    // Format date list (YYYY-MM-DD) to range "14 s/d 18 Bulan Tahun"
    private function formatIndoDateRange(array $dates): string
    {
        $dates = array_values(array_filter($dates));
        sort($dates);
        if (empty($dates)) return '';
        $start = \Carbon\Carbon::createFromFormat('Y-m-d', $dates[0]);
        $end = \Carbon\Carbon::createFromFormat('Y-m-d', end($dates));
        $month = $this->getMonthName($start->month);
        $year = $start->year;
        return $start->day . ' s/d ' . $end->day . ' ' . $month . ' ' . $year;
    }

    // Upsert month meta: nomor surat SPPT/SPPD, tanggal kegiatan, peserta per bulan
    public function upsertMonthMeta(Request $request, Poa $poa)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }
        $poa->loadMissing('rab.items');
        $participantLimit = $this->computeRabParticipantLimit($poa->rab);
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'no_surat_sppt' => 'nullable|string|max:255',
            'no_surat_sppd' => 'nullable|string|max:255',
            'tanggal_kegiatan_sppt' => 'nullable|string|max:255',
            'tanggal_kegiatan_sppd' => 'nullable|string|max:255',
            'tanggal_surat_sppt' => 'nullable|string|max:255',
            'tanggal_surat_sppd' => 'nullable|string|max:255',
            'darat_village_ids' => 'nullable|string',
            'seberang_village_ids' => 'nullable|string',
            'participant_ids' => 'nullable|string', // comma separated IDs
            'holidays' => 'nullable|string', // comma-separated list of days or YYYY-MM-DD (global)
        ]);
        $m = (int) $validated['month'];

        $schedule = $poa->schedule ?: ['months' => []];
        $schedule['months'] = $schedule['months'] ?? [];
        $month = &$schedule['months'][$m];
        if (!is_array($month)) $month = [];
        $month['no_surat_sppt'] = $validated['no_surat_sppt'] ?? ($month['no_surat_sppt'] ?? null);
        $month['no_surat_sppd'] = $validated['no_surat_sppd'] ?? ($month['no_surat_sppd'] ?? null);
        $month['tanggal_kegiatan_sppt'] = $validated['tanggal_kegiatan_sppt'] ?? ($month['tanggal_kegiatan_sppt'] ?? null);
        $month['tanggal_kegiatan_sppd'] = $validated['tanggal_kegiatan_sppd'] ?? ($month['tanggal_kegiatan_sppd'] ?? null);
        $month['tanggal_surat_sppt'] = $validated['tanggal_surat_sppt'] ?? ($month['tanggal_surat_sppt'] ?? null);
        $month['tanggal_surat_sppd'] = $validated['tanggal_surat_sppd'] ?? ($month['tanggal_surat_sppd'] ?? null);

        // Save selected villages per type if provided
        foreach (['darat', 'seberang'] as $type) {
            $key = $type . '_village_ids';
            if (!empty($validated[$key])) {
                $ids = collect(explode(',', $validated[$key]))
                    ->map(fn($x) => (int) trim($x))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $month[$key] = $ids;
            }
        }

        if (!empty($validated['participant_ids'])) {
            $ids = collect(explode(',', $validated['participant_ids']))
                ->map(fn($x) => (int) trim($x))
                ->filter();
            $valid = Employee::whereIn('id', $ids)->pluck('id')->values()->all();
            if ($participantLimit > 0 && count($valid) > $participantLimit) {
                return back()->with('error', 'Peserta yang dipilih melebihi kuota RAB (' . $participantLimit . ' orang).');
            }
            $month['participant_ids'] = $valid;
        }

        // Parse and store GLOBAL holidays (tanggal merah) for this month & year
        if (array_key_exists('holidays', $validated)) {
            $holRaw = (string) ($validated['holidays'] ?? '');
            $year = (int) $poa->year;
            $ymdList = collect(preg_split('/\s*,\s*/', $holRaw, -1, PREG_SPLIT_NO_EMPTY))
                ->map(function ($token) use ($year, $m) {
                    $token = trim($token);
                    if ($token === '') return null;
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $token)) {
                        return $token;
                    }
                    if (preg_match('/^\d{1,2}$/', $token)) {
                        $day = (int) $token;
                        return sprintf('%04d-%02d-%02d', $year, (int) $m, $day);
                    }
                    return null;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            // Replace global holidays for the month with selected list
            $start = \Carbon\Carbon::create($year, $m, 1)->startOfDay()->toDateString();
            $end = \Carbon\Carbon::create($year, $m, 1)->endOfMonth()->toDateString();
            GlobalHoliday::whereBetween('date', [$start, $end])->delete();
            foreach ($ymdList as $ymd) {
                GlobalHoliday::firstOrCreate(['date' => $ymd]);
            }
        }

        // Validate tanggal kegiatan count vs RAB targets
        try {
            $targets = $this->computeRabVillageTargets($poa);
            $maxSppt = (int) ($targets['darat'] ?? 0);
            $maxSppd = (int) ($targets['seberang'] ?? 0);
            if ($maxSppt > 0 && !empty($month['tanggal_kegiatan_sppt'])) {
                $cnt = \App\Helpers\DateHelper::countActivityDays((string) $month['tanggal_kegiatan_sppt']);
                if ($cnt > $maxSppt) {
                    return back()->with('error', 'Tanggal kegiatan SPPT melebihi batas RAB (' . $maxSppt . ' tanggal).');
                }
            }
            if ($maxSppd > 0 && !empty($month['tanggal_kegiatan_sppd'])) {
                $cnt = \App\Helpers\DateHelper::countActivityDays((string) $month['tanggal_kegiatan_sppd']);
                if ($cnt > $maxSppd) {
                    return back()->with('error', 'Tanggal kegiatan SPPD melebihi batas RAB (' . $maxSppd . ' tanggal).');
                }
            }
        } catch (\Throwable $e) {
            // ignore parsing/compute errors here
        }

        // Validate tanggal surat not on holidays (if holidays provided or already stored)
        try {
            $year = (int) $poa->year;
            $start = \Carbon\Carbon::create($year, $m, 1)->startOfDay()->toDateString();
            $end = \Carbon\Carbon::create($year, $m, 1)->endOfMonth()->toDateString();
            $holidays = GlobalHoliday::whereBetween('date', [$start, $end])->pluck('date')->values()->all();
            if (!empty($month['tanggal_surat_sppt'])) {
                $c = \App\Helpers\DateHelper::parseDocumentDate($month['tanggal_surat_sppt']);
                if ($c) {
                    $ymd = $c->format('Y-m-d');
                    if (in_array($ymd, $holidays, true)) {
                        return back()->with('error', 'Tanggal surat SPPT jatuh pada tanggal merah. Mohon pilih tanggal lain.');
                    }
                }
            }
            if (!empty($month['tanggal_surat_sppd'])) {
                $c = \App\Helpers\DateHelper::parseDocumentDate($month['tanggal_surat_sppd']);
                if ($c) {
                    $ymd = $c->format('Y-m-d');
                    if (in_array($ymd, $holidays, true)) {
                        return back()->with('error', 'Tanggal surat SPPD jatuh pada tanggal merah. Mohon pilih tanggal lain.');
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore parse errors and continue
        }

        // Store selected villages and per-village dates (optional)
        $poa->schedule = $schedule;
        $poa->save();

        return back()->with('success', 'Pengaturan bulan berhasil disimpan.');
    }

    // Claim: create SPPT and SPPD quickly from POA month
    public function claim(Request $request, Poa $poa)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'no_surat_sppt' => 'nullable|string|max:255',
            'tanggal_surat_sppt' => 'nullable|string|max:255',
            'jumlah_desa_darat' => 'nullable|integer|min:0',
            'desa_tujuan_darat' => 'nullable|string',
            'no_surat_sppd' => 'nullable|string|max:255',
            'tanggal_surat_sppd' => 'nullable|string|max:255',
            'jumlah_desa_seberang' => 'nullable|integer|min:0',
            'desa_tujuan_seberang' => 'nullable|string',
        ]);

        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        $userIsAdmin = $user->isSuperAdmin();
        $currentEmployeeId = $user->employee_id;
        if (!$userIsAdmin && !$currentEmployeeId) {
            return back()->with('error', 'Akun anda belum terhubung dengan data pegawai. Hubungi admin.');
        }

        $m = (int) $validated['month'];
        $schedule = $poa->schedule ?: ['months' => []];
        $month = $schedule['months'][$m] ?? [];

        // Prevent claim when locked by admin (non-admin users only)
        if (!empty($month['locked']) && !$userIsAdmin) {
            return back()->with('error', 'Bulan ini dikunci oleh admin. Anda tidak dapat melakukan klaim saat ini.');
        }

        if (!empty($month['claimed_at']) || !empty($month['sppt_lpj_id']) || !empty($month['sppd_lpj_id'])) {
            return back()->with('error', 'Bulan ini sudah diklaim sebelumnya.');
        }

        $participantIds = collect($month['participant_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->when(count($month['participant_ids'] ?? []) === 0, function ($collection) use ($poa) {
                return $poa->participants()->pluck('employee_id')->map(fn($id) => (int) $id);
            })
            ->unique()
            ->values()
            ->all();

        if (empty($participantIds)) {
            return back()->with('error', 'Tidak ada peserta yang ditugaskan pada POA ini.');
        }

        if (!$userIsAdmin && !in_array($currentEmployeeId, $participantIds, true)) {
            return back()->with('error', 'Anda tidak termasuk peserta pada bulan ini.');
        }

        $poa->loadMissing('rab.items');
        $participantLimit = $this->computeRabParticipantLimit($poa->rab);
        if ($participantLimit > 0 && count($participantIds) > $participantLimit) {
            return back()->with('error', 'Jumlah peserta melebihi kuota RAB (' . $participantLimit . ' orang). Periksa pengaturan peserta POA.');
        }

        $jumlahDarat = (int) ($validated['jumlah_desa_darat'] ?? 0);
        $jumlahSeberang = (int) ($validated['jumlah_desa_seberang'] ?? 0);

        $targets = $this->computeRabVillageTargets($poa);
        // Allow claiming up to the target (<=). Disallow exceeding or claiming a type not in RAB.
        $targetDarat = (int) ($targets['darat'] ?? 0);
        $targetSeberang = (int) ($targets['seberang'] ?? 0);
        if ($jumlahDarat > 0 && $targetDarat === 0) {
            return back()->with('error', 'RAB tidak mengatur transport darat untuk kegiatan ini.');
        }
        if ($jumlahSeberang > 0 && $targetSeberang === 0) {
            return back()->with('error', 'RAB tidak mengatur transport seberang/laut untuk kegiatan ini.');
        }
        if ($targetDarat > 0 && $jumlahDarat > $targetDarat) {
            return back()->with('error', 'Jumlah desa darat (' . $jumlahDarat . ') melebihi target RAB (' . $targetDarat . ').');
        }
        if ($targetSeberang > 0 && $jumlahSeberang > $targetSeberang) {
            return back()->with('error', 'Jumlah desa seberang (' . $jumlahSeberang . ') melebihi target RAB (' . $targetSeberang . ').');
        }

        $defaultTanggal = '1 ' . $this->getMonthName($m) . ' ' . (int) $poa->year;
        $manualTanggalSppt = trim((string) ($month['tanggal_kegiatan_sppt'] ?? ''));
        $manualTanggalSppd = trim((string) ($month['tanggal_kegiatan_sppd'] ?? ''));

        $noSuratSppt = trim((string) ($month['no_surat_sppt'] ?? $validated['no_surat_sppt'] ?? ''));
        $noSuratSppd = trim((string) ($month['no_surat_sppd'] ?? $validated['no_surat_sppd'] ?? ''));

        if ($jumlahDarat > 0 && $noSuratSppt === '') {
            return back()->with('error', 'Nomor surat SPPT wajib diisi.');
        }
        if ($jumlahSeberang > 0 && $noSuratSppd === '') {
            return back()->with('error', 'Nomor surat SPPD wajib diisi.');
        }

        // Tanggal surat diambil dari pengaturan admin (month meta). User tidak mengubah saat klaim.
        $tanggalSuratSppt = $month['tanggal_surat_sppt'] ?? ($validated['tanggal_surat_sppt'] ?? null);
        $tanggalSuratSppd = $month['tanggal_surat_sppd'] ?? ($validated['tanggal_surat_sppd'] ?? null);

        // Block claim if tanggal surat is on holiday (tanggal merah) for this month
        try {
            $year = (int) $poa->year;
            $start = \Carbon\Carbon::create($year, $m, 1)->startOfDay()->toDateString();
            $end = \Carbon\Carbon::create($year, $m, 1)->endOfMonth()->toDateString();
            $holidays = GlobalHoliday::whereBetween('date', [$start, $end])->pluck('date')->values()->all();
            if (!empty($tanggalSuratSppt)) {
                $c = \App\Helpers\DateHelper::parseDocumentDate($tanggalSuratSppt);
                if ($c) {
                    if (in_array($c->format('Y-m-d'), $holidays, true)) {
                        return back()->with('error', 'Tanggal surat SPPT jatuh pada tanggal merah. Tidak boleh membuat surat keluar pada tanggal tersebut.');
                    }
                }
            }
            if (!empty($tanggalSuratSppd)) {
                $c = \App\Helpers\DateHelper::parseDocumentDate($tanggalSuratSppd);
                if ($c) {
                    if (in_array($c->format('Y-m-d'), $holidays, true)) {
                        return back()->with('error', 'Tanggal surat SPPD jatuh pada tanggal merah. Tidak boleh membuat surat keluar pada tanggal tersebut.');
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore parse errors and proceed
        }

        if ($jumlahDarat > 0 && empty($tanggalSuratSppt)) {
            return back()->with('error', 'Tanggal surat SPPT wajib diisi.');
        }
        if ($jumlahSeberang > 0 && empty($tanggalSuratSppd)) {
            return back()->with('error', 'Tanggal surat SPPD wajib diisi.');
        }

        $desaDaratStr = $validated['desa_tujuan_darat'] ?? null;
        $desaSeberangStr = $validated['desa_tujuan_seberang'] ?? null;

        $spptTanggal = $manualTanggalSppt !== '' ? $manualTanggalSppt : $defaultTanggal;
        $sppdTanggal = $manualTanggalSppd !== '' ? $manualTanggalSppd : $defaultTanggal;

        $created = [
            'sppt' => null,
            'sppd' => null,
        ];

        try {
            DB::transaction(function () use ($poa, $participantIds, $jumlahDarat, $jumlahSeberang, $desaDaratStr, $desaSeberangStr, $spptTanggal, $sppdTanggal, &$schedule, $m, $noSuratSppt, $noSuratSppd, $tanggalSuratSppt, $tanggalSuratSppd, $user, &$created) {
                $month = $schedule['months'][$m] ?? [];

                if ($jumlahDarat > 0) {
                    $sppt = Lpj::create([
                        'type' => 'SPPT',
                        'kegiatan' => $poa->kegiatan,
                        'no_surat' => $noSuratSppt,
                        'tanggal_surat' => $tanggalSuratSppt,
                        'tanggal_kegiatan' => $spptTanggal,
                        'transport_mode' => 'DARAT',
                        'jumlah_desa_darat' => $jumlahDarat,
                        'desa_tujuan_darat' => $desaDaratStr,
                        'jumlah_desa_seberang' => 0,
                        'desa_tujuan_seberang' => null,
                        'created_by' => $user->id,
                    ]);
                    $spptDays = \App\Helpers\DateHelper::countActivityDays($spptTanggal) ?: $jumlahDarat;
                    foreach ($participantIds as $empId) {
                        $sppt->participants()->create([
                            'employee_id' => $empId,
                            'role' => 'ANGGOTA',
                            'lama_tugas_hari' => $spptDays,
                            'transport_amount' => 70000 * $jumlahDarat,
                            'per_diem_rate' => 0,
                            'per_diem_days' => 0,
                            'per_diem_amount' => 0,
                            'total_amount' => 70000 * $jumlahDarat,
                        ]);
                    }
                    $created['sppt'] = $sppt->id;
                    $month['sppt_lpj_id'] = $sppt->id;
                }

                if ($jumlahSeberang > 0) {
                    $sppd = Lpj::create([
                        'type' => 'SPPD',
                        'kegiatan' => $poa->kegiatan,
                        'no_surat' => $noSuratSppd,
                        'tanggal_surat' => $tanggalSuratSppd,
                        'tanggal_kegiatan' => $sppdTanggal,
                        'transport_mode' => 'LAUT',
                        'jumlah_desa_darat' => 0,
                        'desa_tujuan_darat' => null,
                        'jumlah_desa_seberang' => $jumlahSeberang,
                        'desa_tujuan_seberang' => $desaSeberangStr,
                        'created_by' => $user->id,
                    ]);
                    $sppdDays = \App\Helpers\DateHelper::countActivityDays($sppdTanggal) ?: $jumlahSeberang;
                    foreach ($participantIds as $empId) {
                        $sppd->participants()->create([
                            'employee_id' => $empId,
                            'role' => 'ANGGOTA',
                            'lama_tugas_hari' => $sppdDays,
                            'transport_amount' => 70000 * $jumlahSeberang,
                            'per_diem_rate' => 150000,
                            'per_diem_days' => $jumlahSeberang,
                            'per_diem_amount' => 150000 * $jumlahSeberang,
                            'total_amount' => (70000 + 150000) * $jumlahSeberang,
                        ]);
                    }
                    $created['sppd'] = $sppd->id;
                    $month['sppd_lpj_id'] = $sppd->id;
                }

                $month['participant_ids'] = $participantIds;
                $month['claimed_at'] = now()->toDateTimeString();
                $month['claimed_by'] = $user->id;
                if ($noSuratSppt !== '') {
                    $month['no_surat_sppt'] = $noSuratSppt;
                }
                if ($noSuratSppd !== '') {
                    $month['no_surat_sppd'] = $noSuratSppd;
                }
                if ($tanggalSuratSppt) {
                    $month['tanggal_surat_sppt'] = $tanggalSuratSppt;
                }
                if ($tanggalSuratSppd) {
                    $month['tanggal_surat_sppd'] = $tanggalSuratSppd;
                }

                $schedule['months'][$m] = $month;
                $poa->schedule = $schedule;
                $poa->save();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat LPJ: ' . $e->getMessage());
        }

        $messages = ['LPJ berhasil dibuat.'];
        if ($created['sppt']) {
            $messages[] = 'Dokumen SPPT siap diunduh.';
        }
        if ($created['sppd']) {
            $messages[] = 'Dokumen SPPD siap diunduh.';
        }

        // After creating LPJ(s), auto-create Tiba Berangkat similar to LPJ flow
        $redirect = redirect()->route('poa.show', $poa)->with('success', implode(' ', $messages));
        try {
            $tbService = new TibaBerangkatService();
            $tb = null;
            $pairIds = null;
            if ($created['sppt'] && $created['sppd']) {
                $sppt = Lpj::find($created['sppt']);
                $sppd = Lpj::find($created['sppd']);
                if ($sppt && $sppd) {
                    $tb = $tbService->createFromLpjs($sppt, $sppd);
                    $pairIds = [$sppt->id, $sppd->id];
                }
            } else {
                $lpjId = $created['sppt'] ?: $created['sppd'];
                if ($lpjId) {
                    $lpj = Lpj::find($lpjId);
                    if ($lpj) {
                        $tb = $tbService->createFromSingleLpj($lpj);
                    }
                }
            }
            if ($tb) {
                $redirect->with('tiba_berangkat_review_id', $tb->id)
                        ->with('tiba_berangkat_id', $tb->id);
                if ($pairIds) {
                    $redirect->with('download_pair_ids', $pairIds);
                }
            } else {
                // If no TB created, still suggest creating from one of LPJs (prefer SPPD)
                $sourceId = $created['sppd'] ?: $created['sppt'];
                if ($sourceId) {
                    $redirect->with('suggest_tb_from', $sourceId);
                }
            }
        } catch (\Throwable $e) {
            // Ignore TB auto-create failures; keep LPJ success
        }

        return $redirect;
    }

    private function computeRabVillageTargets(Poa $poa): array
    {
        $targets = ['darat' => null, 'seberang' => null];
        $rab = $poa->rab;
        if (!$rab) return $targets;

        $explicitDarat = null;      // from item.type === transport_darat
        $explicitSeberang = null;   // from item.type === transport_laut|transport_seberang
        $patternDarat = null;       // from label heuristics
        $patternSeberang = null;    // from label heuristics
        $fallbackSeberang = null;   // from uang_harian when no transport laut present

        foreach (($rab->items ?? []) as $it) {
            $label = strtolower((string) $it->label);
            $type = strtolower((string) ($it->type ?? ''));
            $desa = null;
            if (is_array($it->factors)) {
                foreach ($it->factors as $f) {
                    $fl = strtolower((string) ($f['label'] ?? $f['key'] ?? ''));
                    if (str_contains($fl, 'desa')) {
                        $desa = (int) round((float) ($f['value'] ?? 0));
                        break;
                    }
                }
            }
            if ($desa === null || $desa <= 0) continue;

            // Prefer explicit types
            if ($type === 'transport_darat') {
                $explicitDarat = max($explicitDarat ?? 0, $desa);
            }
            if ($type === 'transport_laut' || $type === 'transport_seberang') {
                $explicitSeberang = max($explicitSeberang ?? 0, $desa);
            }

            // Heuristics based on labels (fallback)
            if ($patternDarat === null && (str_contains($label, 'transport') && str_contains($label, 'darat'))) {
                $patternDarat = max($patternDarat ?? 0, $desa);
            }
            if ($patternSeberang === null && (str_contains($label, 'laut') || str_contains($label, 'seberang') || str_contains($label, 'speedboat') || str_contains($label, 'kapal') || str_contains($label, 'pompong'))) {
                $patternSeberang = max($patternSeberang ?? 0, $desa);
            }

            // Uang harian as last-resort proxy for seberang
            if ($type === 'uang_harian') {
                $fallbackSeberang = max($fallbackSeberang ?? 0, $desa);
            }
        }

        $targets['darat'] = $explicitDarat ?? $patternDarat;
        $targets['seberang'] = $explicitSeberang ?? $patternSeberang ?? $fallbackSeberang;
        return $targets;
    }
}
