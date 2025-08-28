# Implementasi Fitur Download Dokumen Word LPJ

## Fitur yang Ditambahkan

### 1. Tombol Download Setelah Create LPJ
- **Lokasi**: Halaman index LPJ (`resources/views/lpjs/index.blade.php`)
- **Fungsionalitas**: Setelah berhasil membuat LPJ, akan muncul tombol "Download Dokumen Word" di dalam success message
- **Implementasi**: 
  - Controller `LpjController@store()` mengirim session `show_download` dengan ID LPJ
  - View index mendeteksi session tersebut dan menampilkan tombol download

### 2. Format Nama File Sesuai Permintaan
- **Format Baru**: `{type} {no_surat} {kegiatan}.docx`
- **Contoh**: `SPPD 443 Inspeksi Kesehatan Lingkungan di Tempat Fasilitas Umum (TFU).docx`
- **Lokasi Perubahan**:
  - `LpjDocumentController@download()` - untuk nama file download
  - `LpjDocumentService@generateFilename()` - untuk nama file internal storage

## File yang Dimodifikasi

### 1. `app/Http/Controllers/LpjController.php`
```php
// Menambahkan session show_download setelah create
return redirect()->route('lpjs.index')
    ->with('success', $message)
    ->with('show_download', $lpj->id);
```

### 2. `app/Http/Controllers/LpjDocumentController.php`
```php
// Mengubah format nama file download
$downloadName = "{$lpj->type} {$lpj->no_surat} {$lpj->kegiatan}.docx";
$downloadName = preg_replace('/[^A-Za-z0-9\-_.\s]/', '_', $downloadName);
```

### 3. `app/Services/LpjDocumentService.php`
```php
// Mengubah format nama file internal
private function generateFilename(Lpj $lpj)
{
    $date = now()->format('Y-m-d');
    $cleanType = preg_replace('/[^A-Za-z0-9\-]/', '_', $lpj->type);
    $cleanNoSurat = preg_replace('/[^A-Za-z0-9\-]/', '_', $lpj->no_surat);
    $cleanKegiatan = preg_replace('/[^A-Za-z0-9\-]/', '_', $lpj->kegiatan);
    
    return "{$cleanType}_{$cleanNoSurat}_{$cleanKegiatan}_{$date}.docx";
}
```

### 4. `resources/views/lpjs/index.blade.php`
```php
// Menambahkan tombol download di success message
@if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
        <span>{{ session('success') }}</span>
        @if (session('show_download'))
            @php
                $lpjForDownload = \App\Models\Lpj::find(session('show_download'));
            @endphp
            @if ($lpjForDownload)
                <a href="{{ route('lpj.download', $lpjForDownload) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center ml-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Dokumen Word
                </a>
            @endif
        @endif
    </div>
@endif
```

## Cara Kerja Fitur

### Flow Setelah Create LPJ:
1. User mengisi form create LPJ dan submit
2. `LpjController@store()` memproses data dan membuat LPJ
3. `LpjDocumentService` otomatis generate dokumen Word
4. Controller redirect ke index dengan session `success` dan `show_download`
5. Halaman index menampilkan success message dengan tombol download
6. User dapat langsung download dokumen dengan nama file sesuai format yang diminta

### Format Nama File:
- **Download**: `SPPD 443 Inspeksi Kesehatan Lingkungan di Tempat Fasilitas Umum (TFU).docx`
- **Storage Internal**: `SPPD_443_Inspeksi_Kesehatan_Lingkungan_di_Tempat_Fasilitas_Umum__TFU__2025-08-26.docx`

## Routes yang Tersedia
- `GET /lpj/{lpj}/download` - Download dokumen Word
- `GET /lpj/{lpj}/preview` - Preview dokumen (opsional)
- `POST /lpj/{lpj}/regenerate` - Generate ulang dokumen

## Testing
Untuk menguji fitur:
1. Buat LPJ baru melalui form create
2. Setelah berhasil, akan redirect ke halaman index
3. Lihat success message yang berisi tombol "Download Dokumen Word"
4. Klik tombol untuk download dengan nama file sesuai format yang diminta