# 🚀 Prompt AI Acli RovoDev - Fitur Tiba Berangkat

## 📋 Requirement Overview

Saya ingin membuat fitur **Tiba Berangkat** dengan sistem yang lebih sederhana dan terstruktur. Berikut adalah spesifikasi lengkapnya:

---

## 🗄️ Database Structure

### 1. Tabel `pejabat_ttd`
```sql
CREATE TABLE pejabat_ttd (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    desa VARCHAR(255) NOT NULL,
    jabatan VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Tabel `tiba_berangkat`
```sql
CREATE TABLE tiba_berangkat (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    no_surat VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3. Tabel `tiba_berangkat_detail`
```sql
CREATE TABLE tiba_berangkat_detail (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tiba_berangkat_id BIGINT,
    pejabat_ttd_id BIGINT,
    tanggal_kunjungan DATE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tiba_berangkat_id) REFERENCES tiba_berangkat(id) ON DELETE CASCADE,
    FOREIGN KEY (pejabat_ttd_id) REFERENCES pejabat_ttd(id)
);
```

---

## 🎯 Fitur yang Diinginkan

### 1. **CRUD Pejabat TTD**
- ✅ Create, Read, Update, Delete pejabat penandatangan
- ✅ Form fields: Nama, Desa, Jabatan
- ✅ Datatables untuk listing dengan search, sort, pagination

### 2. **CRUD Tiba Berangkat**
- ✅ Form input nomor surat
- ✅ Multi-select desa kunjungan dengan tanggal masing-masing yang akan di input manual
- ✅ Auto-populate data pejabat dari tabel `pejabat_ttd` berdasarkan desa
- ✅ Validasi minimal 1 desa harus dipilih
- ✅ Listing dengan datatables

### 3. **Generate Document (.docx)**
- ✅ Template dinamis berdasarkan jumlah desa:
  - 1 desa → `templates/1desa.docx`
  - 2 desa → `templates/2desa.docx`
  - 3 desa → `templates/3desa.docx`
  - dst...
- ✅ Replace placeholder di template dengan data real
- ✅ Download otomatis setelah generate

---

## 🏗️ Structure Laravel yang Diinginkan

```
app/
├── Models/
│   ├── PejabatTtd.php
│   ├── TibaBerangkat.php
│   └── TibaBerangkatDetail.php
├── Http/Controllers/
│   ├── PejabatTtdController.php
│   └── TibaBerangkatController.php
├── Http/Requests/
│   ├── PejabatTtdRequest.php
│   └── TibaBerangkatRequest.php

resources/views/
├── pejabat-ttd/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── tiba-berangkats/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    └── show.blade.php

storage/app/templates/
├── 1desa.docx
├── 2desa.docx
├── 3desa.docx
└── ...
```

---

## 📝 Spesifikasi Form Tiba Berangkat

### Input Fields:
1. **Nomor Surat** - Text input (required, unique)
2. **Desa Kunjungan** - Dynamic section yang bisa ditambah/kurang:
   - Select Desa (dari tabel pejabat_ttd)
   - Auto-populate: Nama Pejabat, Jabatan
   - Input: Tanggal Kunjungan
   - Button: Hapus Desa

### JavaScript Functionality:
- ✅ Tombol "Tambah Desa" yang berfungsi sempurna
- ✅ Auto-populate pejabat saat pilih desa
- ✅ Validasi tidak boleh pilih desa yang sama 2x
- ✅ Minimum 1 desa harus dipilih

---

## 🔧 Tech Stack & Dependencies

```json
{
  "laravel": "^10.0",
  "php": "^8.1",
  "packages": {
    "phpoffice/phpword": "^1.0",
    "yajra/laravel-datatables-oracle": "^10.0"
  },
  "frontend": {
    "tailwindcss": "^3.0",
    "alpine.js": "^3.0",
    "fontawesome": "^6.0"
  }
}
```

---

## 📄 Template Document Placeholders

### Placeholder yang harus di-replace:
```
{{no_surat}}
{{tanggal_surat}}
{{desa_1}}
{{pejabat_1}}
{{jabatan_1}}
{{tanggal_kunjungan_1}}
{{desa_2}}
{{pejabat_2}}
{{jabatan_2}}
{{tanggal_kunjungan_2}}
... dan seterusnya sesuai jumlah desa
```

---

## 🎨 UI/UX Requirements

### Design Style:
- ✅ Modern, clean interface dengan Tailwind CSS
- ✅ Responsive design (mobile-friendly)
- ✅ Consistent color scheme (blue primary, green success, red danger)
- ✅ Smooth animations dan transitions
- ✅ Loading states untuk async operations
- ✅ Toast notifications untuk feedback

### Form UX:
- ✅ Progressive disclosure (start with 1 desa, add more as needed)
- ✅ Real-time validation feedback
- ✅ Auto-save draft (optional)
- ✅ Confirmation modals untuk actions

---

## 🔐 Security & Validation

### Validation Rules:
```php
// TibaBerangkatRequest
'no_surat' => 'required|string|max:255|unique:tiba_berangkat',
'desa' => 'required|array|min:1',
'desa.*.pejabat_ttd_id' => 'required|exists:pejabat_ttd,id',
'desa.*.tanggal_kunjungan' => 'required|date',

// PejabatTtdRequest
'nama' => 'required|string|max:255',
'desa' => 'required|string|max:255',
'jabatan' => 'required|string|max:255',
```

---

## 🚀 Deployment Instructions

### Migration Commands:
```bash
php artisan make:migration create_pejabat_ttd_table
php artisan make:migration create_tiba_berangkat_table
php artisan make:migration create_tiba_berangkat_detail_table
php artisan migrate
```

### Seeder (Optional):
```bash
php artisan make:seeder PejabatTtdSeeder
php artisan db:seed --class=PejabatTtdSeeder
```

---

## 🎯 Success Criteria

### Functional:
- ✅ Semua CRUD operations berjalan tanpa error
- ✅ JavaScript form interactions berfungsi sempurna
- ✅ Document generation berhasil dengan template yang benar
- ✅ Validasi form comprehensive
- ✅ Responsive di semua device

### Technical:
- ✅ Code mengikuti Laravel best practices
- ✅ Database relationships properly defined
- ✅ Error handling yang robust
- ✅ Performance optimized (N+1 query prevention)
- ✅ Clean, maintainable code structure

---

## 💡 Additional Notes

1. **Template Management**: Buat sistem untuk upload template .docx via admin panel (future enhancement)
2. **Audit Trail**: Log semua generate document dengan timestamp dan user
3. **Backup Strategy**: Auto-backup generated documents ke cloud storage
4. **API Ready**: Struktur code siap untuk API endpoints di masa depan

---

## 🎬 Execution Priority

1. **Phase 1**: Database migration + Models + Basic CRUD
2. **Phase 2**: Form interactions + JavaScript functionality
3. **Phase 3**: Document generation + Template system
4. **Phase 4**: UI/UX polish + Testing + Deployment

---

**Generate semua code yang diperlukan sesuai spesifikasi di atas. Prioritaskan functionality yang solid dan user experience yang smooth. Jangan lupa include error handling dan validation yang comprehensive.**