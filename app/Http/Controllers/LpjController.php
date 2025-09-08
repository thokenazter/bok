<?php

namespace App\Http\Controllers;

use App\Models\Lpj;
use App\Models\Employee;
use App\Models\Village;
use App\Models\Activity;
// PerDiemRate tidak diperlukan karena uang harian fixed Rp 150.000 per desa
use App\Http\Requests\StoreLpjRequest;
use App\Services\LpjDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LpjController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lpj::with(['participants.employee']);
        
        // Live search by kegiatan, no_surat, atau tanggal
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kegiatan', 'like', "%{$search}%")
                  ->orWhere('no_surat', 'like', "%{$search}%")
                  ->orWhere('tanggal_kegiatan', 'like', "%{$search}%")
                  ->orWhere('tanggal_surat', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by month/year
        if ($request->filled('month') && $request->filled('year')) {
            // Since tanggal_kegiatan is stored as text, we'll filter by contains
            $monthName = $this->getMonthName($request->month);
            $query->where('tanggal_kegiatan', 'like', "%{$monthName} {$request->year}%");
        }

        // Filter by date range
        if ($request->filled('date_from') || $request->filled('date_to')) {
            // For date range, we'll use created_at as fallback since tanggal_kegiatan is text
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination or show all
        if ($request->get('show_all') === 'true') {
            $lpjs = $query->get();
            $showAll = true;
        } else {
            $perPage = $request->get('per_page', 10);
            $lpjs = $query->paginate($perPage);
            $showAll = false;
        }

        // Calculate statistics
        $stats = $this->calculateStats($query);
        
        // If it's an AJAX request, return only the table content
        if ($request->ajax()) {
            return view('lpjs.partials.table', compact('lpjs', 'showAll'))->render();
        }
        
        return view('lpjs.index', compact('lpjs', 'stats', 'showAll'));
    }

    /**
     * Calculate statistics for current filtered data
     */
    private function calculateStats($query)
    {
        // Clone query to avoid affecting pagination
        $statsQuery = clone $query;
        $allLpjs = $statsQuery->with('participants')->get();

        return [
            'total_lpjs' => $allLpjs->count(),
            'total_transport' => $allLpjs->sum(function($lpj) { 
                return $lpj->participants->sum('transport_amount'); 
            }),
            'total_per_diem' => $allLpjs->sum(function($lpj) { 
                return $lpj->participants->sum('per_diem_amount'); 
            }),
            'total_amount' => $allLpjs->sum(function($lpj) { 
                return $lpj->participants->sum('total_amount'); 
            }),
            'total_participants' => $allLpjs->sum(function($lpj) { 
                return $lpj->participants->count(); 
            }),
            'by_type' => $allLpjs->groupBy('type')->map->count(),
            'avg_per_lpj' => $allLpjs->count() > 0 ? $allLpjs->sum(function($lpj) { 
                return $lpj->participants->sum('total_amount'); 
            }) / $allLpjs->count() : 0,
        ];
    }

    /**
     * Get month name in Indonesian
     */
    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$monthNumber] ?? '';
    }

    /**
     * Bulk delete LPJs
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'lpj_ids' => 'required|array',
            'lpj_ids.*' => 'exists:lpjs,id'
        ]);

        $deletedCount = 0;
        $errors = [];

        foreach ($request->lpj_ids as $lpjId) {
            try {
                $lpj = Lpj::findOrFail($lpjId);
                $lpj->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "LPJ ID {$lpjId}: " . $e->getMessage();
            }
        }

        if ($deletedCount > 0) {
            $message = "Berhasil menghapus {$deletedCount} LPJ.";
            if (!empty($errors)) {
                $message .= " Beberapa LPJ gagal dihapus: " . implode(', ', $errors);
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Gagal menghapus LPJ: ' . implode(', ', $errors));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        
        return view('lpjs.create', compact('employees'));
    }

    /**
     * Search employees for autocomplete
     */
    public function searchEmployees(Request $request)
    {
        $search = $request->get('q', '');
        
        $employees = Employee::where('nama', 'LIKE', "%{$search}%")
            ->orWhere('pangkat_golongan', 'LIKE', "%{$search}%")
            ->orWhere('nip', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'nama', 'pangkat_golongan', 'nip']);
            
        return response()->json([
            'results' => $employees->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'text' => $employee->nama . ' (' . $employee->pangkat_golongan . ')',
                    'nama' => $employee->nama,
                    'pangkat_golongan' => $employee->pangkat_golongan,
                    'nip' => $employee->nip
                ];
            })
        ]);
    }

    /**
     * Search activities for autocomplete
     */
    public function searchActivities(Request $request)
    {
        $search = trim($request->get('q', ''));
        
        if (empty($search)) {
            return response()->json(['results' => []]);
        }
        
        // Search existing activities
        $activities = Activity::where('nama', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'nama']);
        
        $results = $activities->map(function($activity) {
            return [
                'id' => $activity->nama,
                'text' => $activity->nama,
                'nama' => $activity->nama,
                'existing' => true
            ];
        });
        
        // If no exact match found and search is not empty, suggest to create new
        $exactMatch = $activities->where('nama', $search)->first();
        if (!$exactMatch && strlen($search) >= 3) {
            $results->prepend([
                'id' => $search,
                'text' => $search . ' (Buat Baru)',
                'nama' => $search,
                'existing' => false,
                'new_activity' => true
            ]);
        }
        
        return response()->json([
            'results' => $results->values()
        ]);
    }

    /**
     * Create new activity if it doesn't exist
     */
    public function createActivityIfNotExists($activityName)
    {
        $activityName = trim($activityName);
        
        if (empty($activityName)) {
            return null;
        }
        
        // Check if activity already exists (case insensitive)
        $existingActivity = Activity::whereRaw('LOWER(nama) = ?', [strtolower($activityName)])->first();
        
        if (!$existingActivity) {
            // Create new activity
            $newActivity = Activity::create([
                'nama' => $activityName
            ]);
            
            return $newActivity;
        }
        
        return $existingActivity;
    }

    /**
     * Create new activity via AJAX
     */
    public function createActivity(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|min:3'
        ]);

        $activityName = trim($request->nama);
        
        // Check if activity already exists (case insensitive)
        $existingActivity = Activity::whereRaw('LOWER(nama) = ?', [strtolower($activityName)])->first();
        
        if ($existingActivity) {
            return response()->json([
                'success' => true,
                'activity' => [
                    'id' => $existingActivity->nama,
                    'text' => $existingActivity->nama,
                    'nama' => $existingActivity->nama,
                    'existing' => true
                ],
                'message' => 'Kegiatan sudah ada sebelumnya.'
            ]);
        }

        // Create new activity
        $newActivity = Activity::create([
            'nama' => $activityName
        ]);

        return response()->json([
            'success' => true,
            'activity' => [
                'id' => $newActivity->nama,
                'text' => $newActivity->nama,
                'nama' => $newActivity->nama,
                'existing' => false,
                'new_activity' => true
            ],
            'message' => 'Kegiatan baru berhasil ditambahkan!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLpjRequest $request)
    {
        $lpj = null;
        
        DB::transaction(function () use ($request, &$lpj) {
            // Auto-create activity if it doesn't exist
            $this->createActivityIfNotExists($request->kegiatan);
            
            $lpj = Lpj::create([
                'type' => $request->type,
                'kegiatan' => $request->kegiatan,
                'no_surat' => $request->no_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'transport_mode' => $request->transport_mode,
                'jumlah_desa_darat' => $request->jumlah_desa_darat,
                'desa_tujuan_darat' => $request->desa_tujuan_darat,
                'jumlah_desa_seberang' => $request->jumlah_desa_seberang,
                'desa_tujuan_seberang' => $request->desa_tujuan_seberang,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->participants as $participantData) {
                $lpj->participants()->create([
                    'employee_id' => $participantData['employee_id'],
                    'role' => $participantData['role'],
                    'lama_tugas_hari' => $participantData['lama_tugas_hari'],
                    'transport_amount' => $participantData['transport_amount'],
                    'per_diem_rate' => $participantData['per_diem_rate'],
                    'per_diem_days' => $participantData['per_diem_days'],
                    'per_diem_amount' => $participantData['per_diem_amount'],
                    'total_amount' => $participantData['transport_amount'] + $participantData['per_diem_amount'],
                ]);
            }
        });

        // Auto-generate Word document
        try {
            // Load participants for document generation
            $lpj->load(['participants.employee']);
            
            $documentService = new LpjDocumentService();
            $documentService->generateDocument($lpj);
            $message = 'LPJ berhasil dibuat dan dokumen Word telah digenerate!';
        } catch (\Exception $e) {
            $message = 'LPJ berhasil dibuat, namun gagal membuat dokumen Word: ' . $e->getMessage();
        }

        return redirect()->route('lpjs.index')
            ->with('success', $message)
            ->with('show_download', $lpj->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lpj $lpj)
    {
        $lpj->load(['participants.employee', 'createdBy']);
        return view('lpjs.show', compact('lpj'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lpj $lpj)
    {
        $employees = Employee::all();
        
        $lpj->load('participants.employee');
        
        return view('lpjs.edit', compact('lpj', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreLpjRequest $request, Lpj $lpj)
    {
        DB::transaction(function () use ($request, $lpj) {
            // Auto-create activity if it doesn't exist
            $this->createActivityIfNotExists($request->kegiatan);
            
            $lpj->update([
                'type' => $request->type,
                'kegiatan' => $request->kegiatan,
                'no_surat' => $request->no_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'transport_mode' => $request->transport_mode,
                'jumlah_desa_darat' => $request->jumlah_desa_darat,
                'desa_tujuan_darat' => $request->desa_tujuan_darat,
                'jumlah_desa_seberang' => $request->jumlah_desa_seberang,
                'desa_tujuan_seberang' => $request->desa_tujuan_seberang,
            ]);

            // Delete existing participants
            $lpj->participants()->delete();

            // Create new participants
            foreach ($request->participants as $participantData) {
                $lpj->participants()->create([
                    'employee_id' => $participantData['employee_id'],
                    'role' => $participantData['role'],
                    'lama_tugas_hari' => $participantData['lama_tugas_hari'],
                    'transport_amount' => $participantData['transport_amount'],
                    'per_diem_rate' => $participantData['per_diem_rate'],
                    'per_diem_days' => $participantData['per_diem_days'],
                    'per_diem_amount' => $participantData['per_diem_amount'],
                    'total_amount' => $participantData['transport_amount'] + $participantData['per_diem_amount'],
                ]);
            }
        });

        return redirect()->route('lpjs.index')
            ->with('success', 'LPJ berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lpj $lpj)
    {
        $lpj->delete();

        return redirect()->route('lpjs.index')
            ->with('success', 'LPJ berhasil dihapus.');
    }
}
