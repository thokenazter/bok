# Perbaikan Bug Nomor Peserta - LPJ Create & Edit

## Masalah yang Ditemukan
Bug pada nomor peserta di halaman LPJ create dan edit dimana nomor peserta tidak restart setelah menghapus peserta. 

### Contoh Masalah:
1. Tambah peserta → ada Peserta 1 dan Peserta 2
2. Hapus Peserta 2
3. Tambah peserta lagi → muncul Peserta 3 (seharusnya Peserta 2)

## Root Cause Analysis
Masalah terjadi karena penggunaan variabel global `participantIndex` yang terus bertambah tanpa pernah di-reset atau disesuaikan dengan jumlah peserta yang sebenarnya ada.

### Kode Bermasalah:
```javascript
let participantIndex = 0; // Global counter yang terus bertambah

function addParticipant() {
    // Menggunakan participantIndex untuk nomor peserta
    const participantHtml = `
        <div data-index="${participantIndex}">
            <h4>Peserta ${participantIndex + 1}</h4>
            <!-- ... -->
        </div>
    `;
    
    participantIndex++; // Selalu bertambah, tidak pernah turun
}
```

## Solusi yang Diimplementasikan

### 1. Menghapus Variabel Global `participantIndex`
```javascript
// BEFORE:
let participantIndex = 0;

// AFTER:
// Variabel dihapus, tidak lagi menggunakan global counter
```

### 2. Menggunakan Dynamic Index Calculation
```javascript
function addParticipant() {
    const container = document.getElementById('participantsContainer');
    const currentParticipants = container.querySelectorAll('.participant-row');
    const newIndex = currentParticipants.length; // Hitung jumlah peserta saat ini
    
    const participantHtml = `
        <div class="participant-row" data-index="${newIndex}">
            <h4>Peserta ${newIndex + 1}</h4>
            <!-- ... -->
        </div>
    `;
    // Tidak ada increment global variable
}
```

### 3. Keuntungan Solusi Baru:
- **Nomor peserta selalu akurat** - Berdasarkan jumlah peserta yang ada saat ini
- **Otomatis restart** - Setelah menghapus peserta, nomor akan menyesuaikan
- **Tidak ada state global** - Menghindari bug dari state yang tidak sinkron
- **Lebih predictable** - Nomor peserta selalu = jumlah peserta + 1

## Perubahan yang Dilakukan

### File: `resources/views/lpjs/create.blade.php`

#### Sebelum:
```javascript
let participantIndex = 0;

function addParticipant() {
    // Menggunakan participantIndex untuk semua referensi
    const participantHtml = `
        <div data-index="${participantIndex}">
            <h4>Peserta ${participantIndex + 1}</h4>
            <select name="participants[${participantIndex}][employee_id]">
            <!-- ... -->
        </div>
    `;
    participantIndex++; // Bug: terus bertambah
}
```

#### Sesudah:
```javascript
function addParticipant() {
    const container = document.getElementById('participantsContainer');
    const currentParticipants = container.querySelectorAll('.participant-row');
    const newIndex = currentParticipants.length; // Dynamic calculation
    
    const participantHtml = `
        <div data-index="${newIndex}">
            <h4>Peserta ${newIndex + 1}</h4>
            <select name="participants[${newIndex}][employee_id]">
            <!-- ... -->
        </div>
    `;
    // Tidak ada increment global variable
}
```

### File: `resources/views/lpjs/edit.blade.php`

#### Perubahan yang sama:
- Menghapus `let participantIndex = {{ isset($existingParticipants) ? count($existingParticipants) : 0 }};`
- Menggunakan dynamic index calculation
- Semua referensi `participantIndex` diganti dengan `newIndex`

## Testing Scenario

### Skenario 1: Tambah Peserta Normal
1. ✅ Tambah peserta → Peserta 1
2. ✅ Tambah peserta → Peserta 2  
3. ✅ Tambah peserta → Peserta 3

### Skenario 2: Hapus dan Tambah Peserta (Bug Fix)
1. ✅ Tambah peserta → Peserta 1, Peserta 2
2. ✅ Hapus Peserta 2
3. ✅ Tambah peserta → Peserta 2 (FIXED: sebelumnya Peserta 3)

### Skenario 3: Hapus Peserta Tengah
1. ✅ Tambah peserta → Peserta 1, Peserta 2, Peserta 3
2. ✅ Hapus Peserta 2
3. ✅ Renumbering otomatis → Peserta 1, Peserta 2 (yang dulu Peserta 3)
4. ✅ Tambah peserta → Peserta 3

### Skenario 4: Edit Page dengan Data Existing
1. ✅ Buka halaman edit dengan 2 peserta existing
2. ✅ Tambah peserta → Peserta 3 (bukan nomor acak)
3. ✅ Hapus peserta → nomor menyesuaikan

## Fungsi yang Terpengaruh

### 1. `addParticipant()`
- ✅ Menggunakan dynamic index calculation
- ✅ Nomor peserta selalu akurat
- ✅ Name attributes menggunakan index yang benar

### 2. `renumberParticipants()`
- ✅ Tetap berfungsi dengan baik
- ✅ Menomori ulang semua peserta setelah penghapusan
- ✅ Update name attributes dengan benar

### 3. `removeParticipant()`
- ✅ Tidak perlu perubahan
- ✅ Masih memanggil `renumberParticipants()` untuk update nomor

## Kompatibilitas dan Side Effects

### ✅ Tidak Ada Breaking Changes:
- Form submission tetap bekerja normal
- Name attributes tetap konsisten
- Event listeners tetap berfungsi
- Select2 initialization tetap bekerja

### ✅ Improved Behavior:
- Nomor peserta lebih predictable
- User experience lebih baik
- Tidak ada confusion dengan nomor yang loncat-loncat

### ✅ Code Quality:
- Menghilangkan global state yang bermasalah
- Kode lebih clean dan maintainable
- Logic lebih mudah dipahami

## Kesimpulan

Bug nomor peserta telah berhasil diperbaiki dengan:

1. **Menghapus dependency pada global counter** yang bermasalah
2. **Menggunakan dynamic calculation** berdasarkan DOM state saat ini  
3. **Memastikan nomor peserta selalu akurat** dan sesuai urutan
4. **Tidak ada breaking changes** pada fungsionalitas existing

Sekarang nomor peserta akan selalu restart dan sesuai dengan urutan yang sebenarnya, memberikan user experience yang lebih baik dan predictable.

### Before Fix:
- Peserta 1, Peserta 2 → Hapus Peserta 2 → Tambah → Peserta 3 ❌

### After Fix:  
- Peserta 1, Peserta 2 → Hapus Peserta 2 → Tambah → Peserta 2 ✅