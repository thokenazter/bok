# 🚀 Panduan Deployment ke Hostinger

## Metode 1: Manual Deployment via SSH

### Langkah-langkah:

1. **Login ke Hostinger via SSH**
   ```bash
   ssh username@your-domain.com
   ```

2. **Masuk ke direktori aplikasi**
   ```bash
   cd public_html
   ```

3. **Jalankan script deployment**
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```

## Metode 2: Auto Deployment dengan GitHub Webhook

### Setup Webhook di GitHub:

1. **Buka repository GitHub** → Settings → Webhooks
2. **Add webhook** dengan URL: `https://your-domain.com/deploy.php`
3. **Content type**: `application/json`
4. **Events**: Push events
5. **Active**: ✅

### Buat file webhook handler:

```php
<?php
// File: public_html/deploy.php

// Verifikasi secret (opsional untuk keamanan)
$secret = 'your-webhook-secret';
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ($secret && !hash_equals('sha256=' . hash_hmac('sha256', file_get_contents('php://input'), $secret), $signature)) {
    http_response_code(403);
    exit('Forbidden');
}

// Log deployment
file_put_contents('deployment.log', date('Y-m-d H:i:s') . " - Deployment started\n", FILE_APPEND);

// Jalankan deployment script
$output = shell_exec('./deploy.sh 2>&1');

// Log hasil
file_put_contents('deployment.log', $output . "\n", FILE_APPEND);

echo "Deployment completed!";
?>
```

## Metode 3: Manual Upload via File Manager

### Jika tidak ada akses SSH:

1. **Build assets locally**
   ```bash
   npm run build
   composer install --no-dev --optimize-autoloader
   ```

2. **Upload files** via Hostinger File Manager:
   - Upload semua file kecuali `node_modules/` dan `.git/`
   - Upload folder `public/build/` hasil build Vite

3. **Update database** via phpMyAdmin atau SSH

## Konfigurasi Environment (.env)

### Pastikan file .env di Hostinger sudah benar:

```env
APP_NAME="LPJ BOK System"
APP_ENV=production
APP_KEY=your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Cache & Session untuk production
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Mail configuration (sesuaikan dengan Hostinger)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
```

## Checklist Deployment

- [ ] ✅ Code sudah di-push ke GitHub
- [ ] ✅ Environment variables sudah dikonfigurasi
- [ ] ✅ Database sudah dibuat di Hostinger
- [ ] ✅ SSL certificate sudah aktif
- [ ] ✅ File permissions sudah benar (755 untuk storage)
- [ ] ✅ Composer dependencies sudah terinstall
- [ ] ✅ Frontend assets sudah di-build
- [ ] ✅ Laravel cache sudah di-clear dan di-optimize

## Troubleshooting

### Error 500:
- Cek file `.env` sudah benar
- Pastikan `APP_KEY` sudah di-generate
- Cek permissions folder `storage/` dan `bootstrap/cache/`

### Assets tidak load:
- Pastikan `APP_URL` di `.env` sudah benar
- Jalankan `npm run build` dan upload folder `public/build/`

### Database error:
- Cek koneksi database di `.env`
- Jalankan `php artisan migrate` jika ada migration baru

## Otomatisasi dengan Cron Job

### Setup cron job untuk deployment otomatis:

```bash
# Jalankan setiap 5 menit untuk cek update
*/5 * * * * cd /home/username/public_html && git fetch && [ $(git rev-list HEAD...origin/main --count) != 0 ] && ./deploy.sh
```

---

**💡 Tip**: Untuk deployment yang lebih smooth, gunakan metode webhook agar aplikasi otomatis update setiap kali ada push ke GitHub!