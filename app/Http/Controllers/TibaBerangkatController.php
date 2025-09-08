<?php

namespace App\Http\Controllers;

use App\Http\Requests\TibaBerangkatRequest;
use App\Models\TibaBerangkat;
use App\Models\PejabatTtd;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use App\Helpers\DateHelper;

class TibaBerangkatController extends Controller
{
    public function index()
    {
        $tibaBerangkats = TibaBerangkat::with('details.pejabatTtd')->latest()->paginate(10);
        return view('tiba-berangkats.index', compact('tibaBerangkats'));
    }

    public function create()
    {
        $pejabatTtds = PejabatTtd::orderBy('desa')->orderBy('nama')->get();
        return view('tiba-berangkats.create', compact('pejabatTtds'));
    }

    public function store(TibaBerangkatRequest $request)
    {
        $tibaBerangkat = TibaBerangkat::create([
            'no_surat' => $request->no_surat
        ]);

        foreach ($request->desa as $desaData) {
            $tibaBerangkat->details()->create([
                'pejabat_ttd_id' => $desaData['pejabat_ttd_id'],
                'tanggal_kunjungan' => $desaData['tanggal_kunjungan'],
            ]);
        }

        return redirect()->route('tiba-berangkats.index')
            ->with('success', 'Tiba Berangkat berhasil ditambahkan.')
            ->with('show_download', $tibaBerangkat->id);
    }

    public function show(TibaBerangkat $tibaBerangkat)
    {
        $tibaBerangkat->load('details.pejabatTtd');
        return view('tiba-berangkats.show', compact('tibaBerangkat'));
    }

    public function edit(TibaBerangkat $tibaBerangkat)
    {
        $tibaBerangkat->load('details.pejabatTtd');
        $pejabatTtds = PejabatTtd::orderBy('desa')->orderBy('nama')->get();
        return view('tiba-berangkats.edit', compact('tibaBerangkat', 'pejabatTtds'));
    }

    public function update(TibaBerangkatRequest $request, TibaBerangkat $tibaBerangkat)
    {
        $tibaBerangkat->update([
            'no_surat' => $request->no_surat
        ]);

        // Delete existing details
        $tibaBerangkat->details()->delete();

        // Create new details
        foreach ($request->desa as $desaData) {
            $tibaBerangkat->details()->create([
                'pejabat_ttd_id' => $desaData['pejabat_ttd_id'],
                'tanggal_kunjungan' => $desaData['tanggal_kunjungan'],
            ]);
        }

        return redirect()->route('tiba-berangkats.index')
            ->with('success', 'Tiba Berangkat berhasil diperbarui.')
            ->with('show_download', $tibaBerangkat->id);
    }

    public function destroy(TibaBerangkat $tibaBerangkat)
    {
        $tibaBerangkat->delete();

        return redirect()->route('tiba-berangkats.index')
            ->with('success', 'Tiba Berangkat berhasil dihapus.');
    }

    public function download(TibaBerangkat $tibaBerangkat)
    {
        $tibaBerangkat->load('details.pejabatTtd');
        
        $jumlahDesa = $tibaBerangkat->details->count();
        $templatePath = storage_path("app/templates/{$jumlahDesa}desa.docx");
        
        if (!file_exists($templatePath)) {
            return back()->with('error', "Template untuk {$jumlahDesa} desa tidak ditemukan.");
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        
        // Replace basic placeholders
        $templateProcessor->setValue('no_surat', $tibaBerangkat->no_surat);
        $templateProcessor->setValue('tanggal_surat', DateHelper::formatIndonesian(now()));
        
        // Replace desa-specific placeholders sesuai format template Anda
        foreach ($tibaBerangkat->details as $index => $detail) {
            $num = $index + 1;
            
            // Format placeholder sesuai template Anda
            $templateProcessor->setValue("desa_{$num}", $detail->pejabatTtd->desa);
            $templateProcessor->setValue("kepala_desa_{$num}", $detail->pejabatTtd->nama);
            $templateProcessor->setValue("tanggal_{$num}", DateHelper::formatIndonesian($detail->tanggal_kunjungan));
            
            // Untuk kompatibilitas dengan format lama
            $templateProcessor->setValue("pejabat_{$num}", $detail->pejabatTtd->nama);
            $templateProcessor->setValue("jabatan_{$num}", $detail->pejabatTtd->jabatan);
            $templateProcessor->setValue("tanggal_kunjungan_{$num}", DateHelper::formatIndonesian($detail->tanggal_kunjungan));
        }
        
        // Tambahan placeholder untuk tanggal selesai (hari berikutnya dari kunjungan terakhir)
        if ($tibaBerangkat->details->isNotEmpty()) {
            $lastDetail = $tibaBerangkat->details->last();
            // Ambil tanggal kunjungan terakhir dan tambah 1 hari
            $tanggalSelesai = $lastDetail->tanggal_kunjungan->addDay();
            $templateProcessor->setValue('tanggal_selesai', DateHelper::formatIndonesian($tanggalSelesai));
        }

        $fileName = "Tiba_Berangkat_{$tibaBerangkat->no_surat}.docx";
        $tempPath = storage_path("app/temp/{$fileName}");
        
        // Ensure temp directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function getPejabatByDesa(Request $request)
    {
        $desa = $request->get('desa');
        $pejabat = PejabatTtd::where('desa', $desa)->first();
        
        return response()->json($pejabat);
    }
}