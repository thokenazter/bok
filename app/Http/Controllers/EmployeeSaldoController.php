<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LpjParticipant;
use Illuminate\Http\Request;

class EmployeeSaldoController extends Controller
{
    /**
     * Display employee salary/balance list
     */
    public function index(Request $request)
    {
        $query = Employee::with(['lpjParticipants.lpj']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('pangkat_golongan', 'like', "%{$search}%");
            });
        }
        
        // Sort by total saldo
        $employees = $query->get()->sortByDesc(function($employee) {
            return $employee->total_saldo;
        });
        
        // Calculate summary statistics
        $totalEmployees = $employees->count();
        $totalSaldo = $employees->sum('total_saldo');
        $avgSaldo = $totalEmployees > 0 ? $totalSaldo / $totalEmployees : 0;
        
        return view('employee-saldo.index', compact(
            'employees', 
            'totalEmployees', 
            'totalSaldo', 
            'avgSaldo'
        ));
    }
    
    /**
     * Show detailed salary breakdown for specific employee
     */
    public function show(Employee $employee)
    {
        $employee->load(['lpjParticipants.lpj']);
        
        // Group participations by LPJ for better display
        $participations = $employee->lpjParticipants()
            ->with(['lpj'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('employee-saldo.show', compact('employee', 'participations'));
    }
}
