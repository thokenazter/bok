# LpjDocumentService.php - Deep Analysis & Enhancement Plan

## 📋 **Current Service Structure Analysis**

### **Core Functionality**
`LpjDocumentService` adalah service yang bertanggung jawab untuk generate dokumen Word (.docx) dari template berdasarkan data LPJ menggunakan PhpOffice\PhpWord library.

### **Key Methods Overview**

#### 1. **`generateDocument(Lpj $lpj)`** - Main Entry Point
- **Purpose**: Generate dokumen LPJ dari template
- **Process**: 
  - Load participants & employee data
  - Determine template based on type & participant count
  - Replace template variables
  - Save generated document
- **Return**: File path ke generated document

#### 2. **`getTemplatePath($type, $participantCount)`** - Template Selection
- **Logic**: Pilih template berdasarkan:
  - LPJ Type: `SPPT` atau `SPPD`
  - Participant Count: `1`, `2`, `3`, etc.
- **Template Naming**: `{TYPE}{COUNT}.docx` (e.g., `SPPT1.docx`, `SPPD2.docx`)
- **Fallback**: Generic template jika specific tidak ada

#### 3. **`replaceTemplateVariables()`** - Main Variable Replacement
- **Global Variables**: LPJ info (no_surat, tanggal, kegiatan, etc.)
- **Participant Variables**: Individual participant data
- **Financial Variables**: Transport, per diem, totals
- **Calculated Variables**: Terbilang (number to words)

#### 4. **`replaceIndividualParticipantData()`** - Individual Participant Processing
- **Current Employee Fields**:
  - `PESERTA{N}_NAMA` → `$participant->employee->nama`
  - `PESERTA{N}_NIP` → `$participant->employee->nip`
  - `PESERTA{N}_PANGKAT` → `$participant->employee->pangkat_golongan`
  - `PESERTA{N}_JABATAN` → `$participant->employee->jabatan`
  - `PESERTA{N}_ROLE` → `$participant->role`

#### 5. **`replaceParticipantsTable()`** - Table-based Participant Processing
- **Table Variables**: For templates with participant tables
- **Row Cloning**: Clone table rows for multiple participants

## 🎯 **Adding Tanggal Lahir - Implementation Plan**

### **Current Employee Model Analysis**
```php
// Employee Model - Already has tanggal_lahir field!
protected $fillable = [
    'nama',
    'nip',
    'tanggal_lahir',  // ✅ Already exists!
    'pangkat_golongan',
    'jabatan',
];

protected $casts = [
    'tanggal_lahir' => 'date',  // ✅ Already casted as date!
];
```

### **Implementation Strategy**

#### **Option 1: Add to Individual Participant Data (Recommended)**
**Location**: `replaceIndividualParticipantData()` method (lines ~150-190)

**Current Code**:
```php
// Basic participant info
$templateProcessor->setValue("PESERTA{$participantNumber}_NAMA", $participant->employee->nama);
$templateProcessor->setValue("PESERTA{$participantNumber}_NIP", $participant->employee->nip);
$templateProcessor->setValue("PESERTA{$participantNumber}_PANGKAT", $participant->employee->pangkat_golongan);
$templateProcessor->setValue("PESERTA{$participantNumber}_JABATAN", $participant->employee->jabatan);
```

**Enhanced Code**:
```php
// Basic participant info
$templateProcessor->setValue("PESERTA{$participantNumber}_NAMA", $participant->employee->nama);
$templateProcessor->setValue("PESERTA{$participantNumber}_NIP", $participant->employee->nip);
$templateProcessor->setValue("PESERTA{$participantNumber}_PANGKAT", $participant->employee->pangkat_golongan);
$templateProcessor->setValue("PESERTA{$participantNumber}_JABATAN", $participant->employee->jabatan);

// Add tanggal lahir with multiple formats
$tanggalLahir = $participant->employee->tanggal_lahir;
if ($tanggalLahir) {
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR", $tanggalLahir->format('d/m/Y'));
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_INDO", $tanggalLahir->format('d F Y'));
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_LONG", $tanggalLahir->format('d F Y'));
} else {
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR", '-');
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_INDO", '-');
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_LONG", '-');
}
```

#### **Option 2: Add to Table-based Participant Data**
**Location**: `replaceParticipantsTable()` method (lines ~240-260)

**Current Code**:
```php
$templateProcessor->setValue("PESERTA_NAMA#{$rowIndex}", $participant->employee->nama);
$templateProcessor->setValue("PESERTA_NIP#{$rowIndex}", $participant->employee->nip);
$templateProcessor->setValue("PESERTA_PANGKAT#{$rowIndex}", $participant->employee->pangkat_golongan);
$templateProcessor->setValue("PESERTA_JABATAN#{$rowIndex}", $participant->employee->jabatan);
```

**Enhanced Code**:
```php
$templateProcessor->setValue("PESERTA_NAMA#{$rowIndex}", $participant->employee->nama);
$templateProcessor->setValue("PESERTA_NIP#{$rowIndex}", $participant->employee->nip);
$templateProcessor->setValue("PESERTA_PANGKAT#{$rowIndex}", $participant->employee->pangkat_golongan);
$templateProcessor->setValue("PESERTA_JABATAN#{$rowIndex}", $participant->employee->jabatan);

// Add tanggal lahir
$tanggalLahir = $participant->employee->tanggal_lahir;
$templateProcessor->setValue("PESERTA_TANGGAL_LAHIR#{$rowIndex}", 
    $tanggalLahir ? $tanggalLahir->format('d/m/Y') : '-'
);
```

## 📝 **Template Variables Available After Enhancement**

### **Individual Participant Templates**
```
{PESERTA1_NAMA}                 → John Doe
{PESERTA1_NIP}                  → 123456789
{PESERTA1_PANGKAT}              → Golongan III/a
{PESERTA1_JABATAN}              → Staff Administrasi
{PESERTA1_TANGGAL_LAHIR}        → 15/08/1990
{PESERTA1_TANGGAL_LAHIR_INDO}   → 15 Agustus 1990
{PESERTA1_TANGGAL_LAHIR_LONG}   → 15 Agustus 1990
```

### **Table-based Templates**
```
{PESERTA_TANGGAL_LAHIR#1}       → 15/08/1990
{PESERTA_TANGGAL_LAHIR#2}       → 22/12/1985
```

## 🔧 **Implementation Code**

### **Method 1: Individual Participant Enhancement**
```php
// Add after line ~158 in replaceIndividualParticipantData()
// Add tanggal lahir with multiple formats
$tanggalLahir = $participant->employee->tanggal_lahir;
if ($tanggalLahir) {
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR", $tanggalLahir->format('d/m/Y'));
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_INDO", $tanggalLahir->format('d F Y'));
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_LONG", $tanggalLahir->format('d F Y'));
    
    // Alternative naming patterns for compatibility
    $templateProcessor->setValue("TANGGAL_LAHIR_PESERTA{$participantNumber}", $tanggalLahir->format('d/m/Y'));
    $templateProcessor->setValue("TGL_LAHIR_PESERTA{$participantNumber}", $tanggalLahir->format('d/m/Y'));
} else {
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR", '-');
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_INDO", '-');
    $templateProcessor->setValue("PESERTA{$participantNumber}_TANGGAL_LAHIR_LONG", '-');
    $templateProcessor->setValue("TANGGAL_LAHIR_PESERTA{$participantNumber}", '-');
    $templateProcessor->setValue("TGL_LAHIR_PESERTA{$participantNumber}", '-');
}
```

### **Method 2: Table Enhancement**
```php
// Add after line ~254 in replaceParticipantsTable()
// Add tanggal lahir for table
$tanggalLahir = $participant->employee->tanggal_lahir;
$templateProcessor->setValue("PESERTA_TANGGAL_LAHIR#{$rowIndex}", 
    $tanggalLahir ? $tanggalLahir->format('d/m/Y') : '-'
);
$templateProcessor->setValue("PESERTA_TGL_LAHIR#{$rowIndex}", 
    $tanggalLahir ? $tanggalLahir->format('d/m/Y') : '-'
);
```

## 📋 **Template Update Requirements**

### **For Individual Templates (SPPT1.docx, SPPD1.docx, etc.)**
Add these variables in your Word templates:
```
Nama: {PESERTA1_NAMA}
NIP: {PESERTA1_NIP}
Pangkat: {PESERTA1_PANGKAT}
Jabatan: {PESERTA1_JABATAN}
Tanggal Lahir: {PESERTA1_TANGGAL_LAHIR}
```

### **For Table Templates**
Add column in participant table:
```
| No | Nama | NIP | Pangkat | Jabatan | Tanggal Lahir |
|----|------|-----|---------|---------|---------------|
| {PESERTA_NO#1} | {PESERTA_NAMA#1} | {PESERTA_NIP#1} | {PESERTA_PANGKAT#1} | {PESERTA_JABATAN#1} | {PESERTA_TANGGAL_LAHIR#1} |
```

## 🎯 **Benefits of This Enhancement**

### **For Users**:
✅ **Complete Employee Info**: Tanggal lahir tersedia di laporan
✅ **Multiple Formats**: dd/mm/yyyy, dd Month yyyy
✅ **Flexible Templates**: Support individual & table formats
✅ **Null Handling**: Graceful handling jika tanggal lahir kosong

### **For System**:
✅ **Backward Compatible**: Tidak break existing templates
✅ **Consistent Naming**: Follow existing variable patterns
✅ **Multiple Patterns**: Support berbagai naming conventions
✅ **Error Handling**: Safe null checking

## 🚀 **Next Steps**

1. **Implement Code**: Add tanggal lahir variables to service
2. **Update Templates**: Add variables to Word templates
3. **Test Generation**: Generate documents with new fields
4. **Verify Output**: Check formatting and display
5. **Documentation**: Update template documentation

## 📁 **File Locations**

- **Service**: `app/Services/LpjDocumentService.php`
- **Templates**: `storage/app/templates/`
- **Generated**: `storage/app/public/lpj-documents/`
- **Employee Model**: `app/Models/Employee.php` (already has tanggal_lahir)

Ready to implement the enhancement?