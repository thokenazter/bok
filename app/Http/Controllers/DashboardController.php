<?php

namespace App\Http\Controllers;

use App\Models\Lpj;
use App\Models\LpjParticipant;
use App\Models\Employee;
use App\Models\Village;
use App\Models\RateSetting;
use App\Helpers\DateHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->email === 'admin@admin.com');

        // Statistik Utama
        $totalEmployees = Employee::count();
        $totalVillages = Village::count();
        $totalLpjs = Lpj::when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))->count();
        $totalBudget = LpjParticipant::when(!$isAdmin, function ($q) use ($user) {
                $q->join('lpjs', 'lpj_participants.lpj_id', '=', 'lpjs.id')
                  ->where('lpjs.created_by', $user->id);
            })
            ->sum('total_amount');

        // Statistik Bulanan berdasarkan tanggal kegiatan
        $currentMonth = Carbon::now();
        $monthlyLpjs = $this->getLpjCountByActivityMonth($currentMonth->month, $currentMonth->year, $user, $isAdmin);
        $monthlyBudget = $this->getBudgetByActivityMonth($currentMonth->month, $currentMonth->year, $user, $isAdmin);

                // Data untuk Chart - 6 bulan dinamis berdasarkan kegiatan yang ada
        $chartData = $this->getChartDataDynamic($user, $isAdmin);

        // Statistik per Tipe LPJ
        $lpjByType = Lpj::when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => $item->count];
            });

        // Anggaran per Tipe LPJ
        $budgetByType = LpjParticipant::join('lpjs', 'lpj_participants.lpj_id', '=', 'lpjs.id')
            ->when(!$isAdmin, fn($q) => $q->where('lpjs.created_by', $user->id))
            ->select('lpjs.type', DB::raw('sum(lpj_participants.total_amount) as total'))
            ->groupBy('lpjs.type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => $item->total];
            });

        // Top 5 Kegiatan dengan Anggaran Terbesar
        $topActivities = LpjParticipant::join('lpjs', 'lpj_participants.lpj_id', '=', 'lpjs.id')
            ->when(!$isAdmin, fn($q) => $q->where('lpjs.created_by', $user->id))
            ->select('lpjs.kegiatan', DB::raw('sum(lpj_participants.total_amount) as total'))
            ->groupBy('lpjs.kegiatan')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Recent LPJs dengan informasi tambahan
        $recentLpjs = Lpj::with(['participants', 'createdBy'])
            ->when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($lpj) {
                $lpj->total_budget = $lpj->participants->sum('total_amount');
                $lpj->participant_count = $lpj->participants->count();
                return $lpj;
            });

        // Statistik Transport vs Per Diem
        $transportTotal = LpjParticipant::when(!$isAdmin, function ($q) use ($user) {
                $q->join('lpjs', 'lpj_participants.lpj_id', '=', 'lpjs.id')
                  ->where('lpjs.created_by', $user->id);
            })
            ->sum('transport_amount');
        $perDiemTotal = LpjParticipant::when(!$isAdmin, function ($q) use ($user) {
                $q->join('lpjs', 'lpj_participants.lpj_id', '=', 'lpjs.id')
                  ->where('lpjs.created_by', $user->id);
            })
            ->sum('per_diem_amount');

        // Rate Settings untuk info
        $currentTransportRate = RateSetting::getTransportRate();
        $currentPerDiemRate = RateSetting::getPerDiemRate();

        // Statistik Pegawai Aktif (yang pernah ikut LPJ)
        $activeEmployees = LpjParticipant::distinct('employee_id')->count('employee_id');

        return view('dashboard', compact(
            'totalEmployees',
            'totalVillages',
            'totalLpjs',
            'totalBudget',
            'monthlyLpjs',
            'monthlyBudget',
            'chartData',
            'lpjByType',
            'budgetByType',
            'topActivities',
            'recentLpjs',
            'transportTotal',
            'perDiemTotal',
            'currentTransportRate',
            'currentPerDiemRate',
            'activeEmployees'
        ));
    }

    /**
     * Get LPJ count by activity month
     */
    private function getLpjCountByActivityMonth($month, $year, $user, $isAdmin)
    {
        $lpjs = Lpj::when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))->get();
        $count = 0;

        foreach ($lpjs as $lpj) {
            $activityDate = DateHelper::getMonthYearFromActivity($lpj->tanggal_kegiatan);

            // Fallback ke tanggal surat jika parsing tanggal kegiatan gagal
            if (!$activityDate) {
                $activityDate = DateHelper::getMonthYearFromDocument($lpj->tanggal_surat);
            }

            if ($activityDate && $activityDate['month'] == $month && $activityDate['year'] == $year) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get budget sum by activity month
     */
    private function getBudgetByActivityMonth($month, $year, $user, $isAdmin)
    {
        $total = 0;
        $lpjs = Lpj::with('participants')->when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))->get();

        foreach ($lpjs as $lpj) {
            $activityDate = DateHelper::getMonthYearFromActivity($lpj->tanggal_kegiatan);

            // Fallback ke tanggal surat jika parsing tanggal kegiatan gagal
            if (!$activityDate) {
                $activityDate = DateHelper::getMonthYearFromDocument($lpj->tanggal_surat);
            }

            if ($activityDate && $activityDate['month'] == $month && $activityDate['year'] == $year) {
                $total += $lpj->participants->sum('total_amount');
            }
        }

        return $total;
    }

    /**
     * Get dynamic chart data that includes months with activities
     */
    private function getChartDataDynamic($user, $isAdmin)
    {
        // Kumpulkan semua bulan yang ada kegiatan
        $monthsWithActivity = [];
        $lpjs = Lpj::with('participants')
            ->when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))
            ->get();

        foreach ($lpjs as $lpj) {
            $activityDate = DateHelper::getMonthYearFromActivity($lpj->tanggal_kegiatan);
            
            // Fallback ke tanggal surat jika parsing tanggal kegiatan gagal
            if (!$activityDate) {
                $activityDate = DateHelper::getMonthYearFromDocument($lpj->tanggal_surat);
            }

            if ($activityDate) {
                $key = $activityDate['year'] . '-' . sprintf('%02d', $activityDate['month']);
                if (!isset($monthsWithActivity[$key])) {
                    $monthsWithActivity[$key] = [
                        'month' => $activityDate['month'],
                        'year' => $activityDate['year'],
                        'total' => 0
                    ];
                }
                $monthsWithActivity[$key]['total'] += $lpj->participants->sum('total_amount');
            }
        }

        // Sort by year-month
        ksort($monthsWithActivity);

        // Ambil 6 bulan terakhir dari data yang ada, atau minimal 6 bulan terakhir dari sekarang
        $currentDate = Carbon::now();
        $chartData = [];

        // Jika ada data kegiatan, gunakan range yang mencakup data tersebut
        if (!empty($monthsWithActivity)) {
            $allMonths = array_keys($monthsWithActivity);
            $latestMonth = end($allMonths);
            $earliestMonth = reset($allMonths);

            // Buat range 6 bulan yang mencakup data terbaru
            $endDate = Carbon::createFromFormat('Y-m', $latestMonth);
            $startDate = $endDate->copy()->subMonths(5);

            // Jika ada data di masa depan (seperti September), pastikan termasuk
            for ($i = 0; $i < 6; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $key = $date->format('Y-m');
                $monthName = $date->format('M Y');
                
                $total = isset($monthsWithActivity[$key]) ? $monthsWithActivity[$key]['total'] : 0;
                
                $chartData[] = [
                    'month' => $monthName,
                    'total' => $total
                ];
            }
        } else {
            // Fallback ke 6 bulan terakhir biasa jika tidak ada data
            for ($i = 5; $i >= 0; $i--) {
                $date = $currentDate->copy()->subMonths($i);
                $monthName = $date->format('M Y');
                
                $chartData[] = [
                    'month' => $monthName,
                    'total' => 0
                ];
            }
        }

        return $chartData;
    }
}
