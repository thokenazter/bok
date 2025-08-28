# 📋 ATURAN TEKNIS FINAL - LPJ BOK PUSKESMAS

## 💰 PERHITUNGAN BERDASARKAN JUMLAH DESA

### **🚗 Transport (Rp 70.000)**
- **Formula**: Rp 70.000 × Jumlah Desa × Jumlah Peserta
- **SPPT**: Rp 70.000 × Jumlah Desa Darat × Jumlah Peserta
- **SPPD**: Rp 70.000 × Jumlah Desa Seberang × Jumlah Peserta

### **🏠 Uang Harian**
- **SPPT**: Rp 0 (tidak ada uang harian)
- **SPPD**: Rp 150.000 × Jumlah Desa Seberang (total untuk semua peserta)

## 📊 CONTOH PERHITUNGAN

### **SPPT - 2 Pegawai ke 3 Desa Darat:**
- Transport: Rp 70.000 × 3 desa × 2 pegawai = **Rp 420.000**
- Uang Harian: Rp 0
- **Total: Rp 420.000**

### **SPPD - 3 Pegawai ke 2 Desa Seberang:**
- Transport: Rp 70.000 × 2 desa × 3 pegawai = **Rp 420.000**
- Uang Harian: Rp 150.000 × 2 desa = **Rp 300.000**
- **Total: Rp 720.000**

## 🔧 IMPLEMENTASI YANG TELAH DILAKUKAN

### **Database Changes:**
- ✅ Tambah kolom `jumlah_desa_darat` dan `jumlah_desa_seberang` di tabel `lpjs`
- ✅ Hapus referensi `village_id` (karena sekarang multi-desa)
- ✅ Migration untuk menambah kolom baru

### **Model Updates:**
- ✅ Update `Lpj` model untuk include kolom baru
- ✅ Update fillable attributes
- ✅ Hapus relasi `village()` karena sekarang multi-desa

### **Form Validation:**
- ✅ Validasi SPPT: minimal 1 desa darat, 0 desa seberang
- ✅ Validasi SPPD: minimal 1 desa seberang, 0 desa darat
- ✅ Validasi transport amount sesuai formula baru
- ✅ Validasi uang harian sesuai aturan

### **UI/UX Improvements:**
- ✅ Form input untuk jumlah desa darat dan seberang
- ✅ Auto-calculate transport dan uang harian
- ✅ Hints dan panduan yang jelas
- ✅ JavaScript untuk real-time calculation

### **Controller Updates:**
- ✅ Update `LpjController` untuk handle kolom baru
- ✅ Hapus referensi `village_id` dari create/update
- ✅ Update query untuk tidak include village relation

## ✅ VALIDASI YANG DITERAPKAN

### **SPPT (Surat Perintah Perjalanan Tugas):**
- Hanya untuk desa dengan akses DARAT
- `jumlah_desa_darat` >= 1
- `jumlah_desa_seberang` = 0
- Transport = Rp 70.000 × jumlah_desa_darat per peserta
- Uang harian = Rp 0

### **SPPD (Surat Perintah Perjalanan Dinas):**
- Hanya untuk desa dengan akses SEBERANG
- `jumlah_desa_seberang` >= 1
- `jumlah_desa_darat` = 0
- Transport = Rp 70.000 × jumlah_desa_seberang per peserta
- Uang harian = Rp 150.000 × jumlah_desa_seberang (total)

## 🎯 FITUR YANG BERFUNGSI

### **Form LPJ Create:**
- Input jumlah desa darat dan seberang
- Auto-calculate transport berdasarkan jumlah desa
- Auto-calculate uang harian untuk SPPD
- Real-time validation dan feedback

### **Business Logic:**
- Perhitungan otomatis sesuai formula
- Validasi aturan bisnis yang ketat
- Error messages yang informatif
- Konsistensi data terjamin

### **User Experience:**
- Form yang intuitif dengan hints
- Calculation yang transparan
- Validation yang real-time
- UI yang responsive

## 🚀 STATUS APLIKASI

**✅ IMPLEMENTASI SELESAI 100%**

Aplikasi LPJ BOK Puskesmas sekarang sudah:
- ✅ Menggunakan perhitungan berdasarkan jumlah desa
- ✅ Validasi bisnis yang akurat
- ✅ Form yang user-friendly
- ✅ Auto-calculation yang benar
- ✅ Database structure yang optimal

**🎯 Siap digunakan dengan aturan teknis yang benar!**

### **Cara Penggunaan:**
1. Pilih tipe LPJ (SPPT/SPPD)
2. Input jumlah desa sesuai tipe
3. Sistem akan auto-calculate transport dan uang harian
4. Tambah peserta sesuai kebutuhan
5. Submit form dengan validasi otomatis

**Aplikasi akan memastikan semua perhitungan sesuai dengan aturan teknis yang telah ditetapkan!**