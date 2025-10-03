<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::orderBy('nama')->get();
        return view('admin.users.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:user,admin,super_admin'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        if ($request->filled('employee_id')) {
            $alreadyLinked = User::where('employee_id', $request->employee_id)->exists();
            if ($alreadyLinked) {
                return back()->withInput()->withErrors(['employee_id' => 'Pegawai sudah terhubung ke akun lain.']);
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'approved_at' => $request->role === 'super_admin' ? now() : null,
            'email_verified_at' => now(),
            'employee_id' => $request->employee_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $employees = Employee::orderBy('nama')->get();
        return view('admin.users.edit', compact('user', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:user,admin,super_admin'],
            'password' => ['nullable', 'string', 'min:8'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        if ($request->filled('employee_id')) {
            $alreadyLinked = User::where('employee_id', $request->employee_id)
                ->where('id', '!=', $user->id)
                ->exists();
            if ($alreadyLinked) {
                return back()->withInput()->withErrors(['employee_id' => 'Pegawai sudah terhubung ke akun lain.']);
            }
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'employee_id' => $request->employee_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Approve a user
     */
    public function approve(User $user)
    {
        $user->update(['approved_at' => now()]);
        return redirect()->route('users.index')->with('success', 'User approved successfully.');
    }
}
