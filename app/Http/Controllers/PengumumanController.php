<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::orderBy('created_at', 'desc')->paginate(10);
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function create()
    {
        return view('pengumuman.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'is_active' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'prioritas' => 'required|in:low,medium,high',
            'warna_tema' => 'required|string|max:7'
        ]);

        $validated['is_active'] = $request->has('is_active');

        Pengumuman::create($validated);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('pengumuman.show', compact('pengumuman'));
    }

    public function edit(Pengumuman $pengumuman)
    {
        return view('pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'is_active' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'prioritas' => 'required|in:low,medium,high',
            'warna_tema' => 'required|string|max:7'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $pengumuman->update($validated);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function toggle(Pengumuman $pengumuman)
    {
        $pengumuman->update(['is_active' => !$pengumuman->is_active]);
        
        $status = $pengumuman->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('pengumuman.index')
            ->with('success', "Pengumuman berhasil {$status}.");
    }

    public function getActive()
    {
        $pengumuman = Pengumuman::active()
            ->orderBy('prioritas', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
            
        return response()->json($pengumuman);
    }
}