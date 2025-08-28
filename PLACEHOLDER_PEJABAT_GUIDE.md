# 📋 Panduan Placeholder Pejabat - Template Tiba Berangkat

## 🎯 Placeholder yang Tersedia untuk Pejabat

Sistem sudah menyediakan beberapa placeholder untuk data pejabat yang bisa Anda gunakan dalam template:

### 1. **Format Baru (Recommended)**:
```
${kepala_desa_1}    - Nama pejabat desa ke-1
${kepala_desa_2}    - Nama pejabat desa ke-2
${kepala_desa_3}    - Nama pejabat desa ke-3
...dan seterusnya
```

### 2. **Format Lama (Backward Compatibility)**:
```
${pejabat_1}        - Nama pejabat desa ke-1
${pejabat_2}        - Nama pejabat desa ke-2
${pejabat_3}        - Nama pejabat desa ke-3
${jabatan_1}        - Jabatan pejabat desa ke-1
${jabatan_2}        - Jabatan pejabat desa ke-2
${jabatan_3}        - Jabatan pejabat desa ke-3
...dan seterusnya
```

## 📄 Contoh Penggunaan dalam Template

### Contoh 1: Format Sederhana
```
SURAT TIBA BERANGKAT
Nomor: ${no_surat}

Kunjungan ke Desa ${desa_1}
Pejabat yang ditemui: ${kepala_desa_1}
Jabatan: ${jabatan_1}
Tanggal: ${tanggal_1}

Kunjungan ke Desa ${desa_2}
Pejabat yang ditemui: ${kepala_desa_2}
Jabatan: ${jabatan_2}
Tanggal: ${tanggal_2}
```

### Contoh 2: Format Tabel
```
| Desa | Pejabat | Jabatan | Tanggal |
|------|---------|---------|---------|
| ${desa_1} | ${kepala_desa_1} | ${jabatan_1} | ${tanggal_1} |
| ${desa_2} | ${kepala_desa_2} | ${jabatan_2} | ${tanggal_2} |
```

### Contoh 3: Format Sesuai Template Anda (Extended)
```
Berangkat Dari	:	Desa ${desa_1}
Tiba di	:	Desa ${desa_1}	Ke	:	Desa ${desa_2}
Pada Tanggal	:	${tanggal_1}	Pada Tanggal	:	${tanggal_1}
Kepala Desa	:	${kepala_desa_1}
Jabatan		:	${jabatan_1}

${kepala_desa_1}	Kepala	:	Desa ${desa_1}
${jabatan_1}

${kepala_desa_1}
					
Berangkat Dari	:	Desa ${desa_2}
Tiba di	:	Desa ${desa_2}	Ke	:	Desa ${desa_1}
Pada Tanggal	:	${tanggal_2}	Pada Tanggal	:	${tanggal_selesai}
Kepala Desa	:	${kepala_desa_2}
Jabatan		:	${jabatan_2}

${kepala_desa_2}	Kepala	:	Desa ${desa_2}
${jabatan_2}

${kepala_desa_2}
```

## 🔧 Data yang Tersedia

Dari database `pejabat_ttd`, sistem mengambil:
- **Nama** → `${kepala_desa_X}` atau `${pejabat_X}`
- **Jabatan** → `${jabatan_X}`
- **Desa** → `${desa_X}`

## 📝 Tips Penulisan Template

1. **Gunakan format konsisten** - Pilih satu format dan gunakan di seluruh template
2. **Numbering berurutan** - Mulai dari 1, 2, 3, dst.
3. **Case sensitive** - Pastikan penulisan placeholder persis sama
4. **Test template** - Selalu test setelah membuat template baru

## 🎯 Rekomendasi Format

Untuk konsistensi dengan format yang sudah Anda gunakan, saya rekomendasikan:

```
${desa_1}, ${desa_2}, dst.           - Nama desa
${kepala_desa_1}, ${kepala_desa_2}   - Nama pejabat
${jabatan_1}, ${jabatan_2}           - Jabatan pejabat  
${tanggal_1}, ${tanggal_2}           - Tanggal kunjungan
${tanggal_selesai}                   - Tanggal selesai (otomatis)
```