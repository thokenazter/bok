# PROMPT UNTUK ACLI ROVO DEV — FITUR ROLE & APPROVAL USER (LARAVEL + JETSTREAM)

## Konteks
Aplikasi Laravel berbasis Jetstream/Fortify sudah berjalan.  
Kita ingin menambahkan fitur role sederhana:

- **super_admin**
  - Bisa melihat, mengedit, dan menghapus user.
  - Bisa approve user baru (yang registrasi).
  - Bisa melihat menu "User Management" & "Approval User".
- **user**
  - Default setelah registrasi.
  - Tidak bisa login ke dashboard sebelum `approved_at` diisi oleh super_admin.
  - Saat login sebelum diapprove → diarahkan ke halaman “Menunggu persetujuan”.

## Tugas untuk AI
Implementasikan fitur ini dalam project Laravel saya dengan langkah-langkah berikut:

---

### 1. Migration
Tambahkan kolom `role` (string, default `user`) dan `approved_at` (timestamp nullable) pada tabel `users`.

```php
php artisan make:migration add_role_and_approved_to_users_table --table=users
```

Isi migrasi:
```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('user')->after('email');
        $table->timestamp('approved_at')->nullable()->after('role');
    });
}
```

---

### 2. Update Model User
Tambahkan helper method:

```php
public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
public function isApproved(): bool { return !is_null($this->approved_at); }
```

---

### 3. Update Registrasi
Di `app/Actions/Fortify/CreateNewUser.php`, pastikan user baru tersimpan sebagai:
```php
'role' => 'user',
'approved_at' => null,
```

---

### 4. Middleware
Buat 2 middleware:

#### `EnsureUserIsApproved`
- Jika user login tapi `approved_at == null` → logout, redirect ke route `approval.pending`.

#### `EnsureUserIsSuperAdmin`
- Jika user bukan super_admin → abort(403).

Daftarkan di `Kernel.php`:
```php
'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
'super_admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
```

---

### 5. Routes
Tambahkan group untuk super_admin:
```php
Route::middleware(['auth','approved','super_admin'])->group(function () {
    Route::resource('/users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/approve', [UserController::class,'approve'])->name('users.approve');
});
```

Tambahkan route untuk halaman pending approval:
```php
Route::get('/approval/pending', fn() => view('auth.approval-pending'))->name('approval.pending');
```

---

### 6. Controller
Buat `Admin/UserController` dengan fungsi:
- `index` → list semua user (dengan tombol Approve jika belum approved).
- `approve(User $user)` → set `approved_at = now()`.
- `edit/update/destroy` → CRUD user hanya untuk super_admin.

---

### 7. Views
- `resources/views/admin/users/index.blade.php`:
  - Tabel semua user
  - Tombol approve untuk user baru
  - Tombol edit/hapus untuk super_admin
- `resources/views/auth/approval-pending.blade.php`:
  - Pesan: "Akun Anda menunggu persetujuan super_admin."

---

### 8. Navigasi
Update `navigation-menu.blade.php`:
```blade
@if(auth()->user()->isSuperAdmin())
    <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
        {{ __('User Management') }}
    </x-nav-link>
@endif
```

---

### 9. Seeder super_admin
Buat seeder `SuperAdminSeeder` untuk membuat akun super_admin default:
```php
User::firstOrCreate(
    ['email' => 'superadmin@puskesmas.test'],
    [
        'name' => 'Super Admin',
        'password' => Hash::make('password123'),
        'role' => 'super_admin',
        'approved_at' => now(),
        'email_verified_at' => now(),
    ]
);
```

---

### 10. Testing
- Registrasi user baru → diarahkan ke halaman pending.
- super_admin approve user → user bisa login dashboard.
- super_admin bisa melihat, edit, hapus user.

---

## Acceptance Criteria
- User default role = `user`, belum bisa login dashboard.
- super_admin bisa approve user.
- super_admin bisa CRUD user.
- User yang belum approved diarahkan ke halaman pending.
- Navigasi User Management hanya muncul untuk super_admin.

---
