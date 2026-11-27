# Panduan Instalasi Project Urban Office pada VPS Linux (LEMP Stack)

Panduan ini menjelaskan langkah-demi-langkah cara menginstal (deploy) website Pure PHP + HTML + MySQL 8 ini pada server VPS Ubuntu menggunakan Nginx dan PHP 8.3-FPM.

---

## 1. Persiapan VPS
Login ke VPS Anda menggunakan SSH:
```bash
ssh root@ip_address_vps
```
Lakukan pembaruan repositori dan paket sistem:
```bash
sudo apt update && sudo apt upgrade -y
```

---

## 2. Instalasi Nginx, MySQL, dan PHP 8.3
Pasang web server Nginx, database engine MySQL, git, dan PHP 8.3 beserta modul-modul yang dibutuhkan:

```bash
# 1. Install Nginx, MySQL Server, dan Git
sudo apt install nginx mysql-server git curl unzip -y

# 2. Tambahkan Repositori PHP PPA Ondrej
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# 3. Install PHP 8.3-FPM dan Modul Pendukung
sudo apt install php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip php8.3-opcache -y
```

---

## 3. Konfigurasi Database MySQL
1. Jalankan pengamanan instalasi database (Opsional):
   ```bash
   sudo mysql_secure_installation
   ```
2. Masuk ke console database MySQL:
   ```bash
   sudo mysql
   ```
3. Eksekusi query berikut untuk membuat database, user, dan hak akses:
   ```sql
   CREATE DATABASE urban_office DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'urban_user'@'localhost' IDENTIFIED BY 'PasswordKuatAnda123!';
   GRANT ALL PRIVILEGES ON urban_office.* TO 'urban_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

---

## 4. Deploy Source Code ke VPS
1. Buat direktori root untuk website Anda di VPS:
   ```bash
   sudo mkdir -p /var/www/urbanoffice/public_html
   ```
2. Upload seluruh file project Anda dari lokal ke dalam folder `/var/www/urbanoffice/public_html` di VPS menggunakan alat SFTP (seperti FileZilla/Cyberduck) atau clone dari repository Git Anda.
3. Setelah upload selesai, atur hak kepemilikan user web server Nginx (`www-data`) agar web server dapat memanipulasi upload gambar dan menulis file cache:
   ```bash
   sudo chown -R www-data:www-data /var/www/urbanoffice
   sudo chmod -R 755 /var/www/urbanoffice
   ```

---

## 5. Konfigurasi File Parameter Sistem
1. Buka file konfigurasi utama di VPS:
   ```bash
   nano /var/www/urbanoffice/public_html/inc/config.php
   ```
2. Sesuaikan konfigurasi berikut:
   * Ubah `DEV_MODE` menjadi `false` (menyembunyikan tampilan detail error teknis dari pengunjung).
   * Ubah `BASE_URL` ke domain produksi Anda (misal: `https://domainanda.com/`).
   * Ubah `DB_USER` ke `urban_user`.
   * Ubah `DB_PASS` ke password database yang Anda buat di Langkah 3 (`PasswordKuatAnda123!`).
   
   *Simpan file dengan menekan `CTRL + O`, lalu keluar dengan `CTRL + X`.*

---

## 6. Inisialisasi Database
Jalankan setup script database dari PHP CLI untuk membuat seluruh struktur table database dan mendaftarkan user administrator default:
```bash
php /var/www/urbanoffice/public_html/inc/init_db.php
```
* **Default Admin Username**: `admin`
* **Default Admin Password**: digenerate secara acak saat script dijalankan, dan ditampilkan sekali di output terminal (`Password: ...`). Password ini tidak disimpan di mana pun selain hash-nya di database, jadi catat segera dari output terminal.
*(Segera ganti password Anda melalui halaman admin `/admin` setelah login pertama kali).*

---

## 7. Konfigurasi Server Block Nginx
1. Buat file virtual host baru untuk website Anda:
   ```bash
   sudo nano /etc/nginx/sites-available/urbanoffice.conf
   ```
2. Salin dan tempelkan isi konfigurasi dari file `nginx.conf` bawaan project ini. Pastikan Anda menyesuaikan:
   * **`server_name`**: Ganti `urbanoffice.co.id` dengan domain Anda (misal: `domainanda.com www.domainanda.com`).
   * **`root`**: Pastikan mengarah ke direktori `/var/www/urbanoffice/public_html`.
   * **`fastcgi_pass`**: Pastikan soket mengarah ke socket PHP 8.3-FPM yang benar, contoh: `unix:/var/run/php/php8.3-fpm.sock`.
3. Aktifkan konfigurasi baru tersebut dan matikan default website bawaan Nginx:
   ```bash
   # Aktifkan konfigurasi baru
   sudo ln -s /etc/nginx/sites-available/urbanoffice.conf /etc/nginx/sites-enabled/
   
   # Hapus default page bawaan Nginx
   sudo rm /etc/nginx/sites-enabled/default
   
   # Tes konfigurasi apakah ada syntax error
   sudo nginx -t
   
   # Restart service Nginx dan PHP-FPM
   sudo systemctl restart nginx
   sudo systemctl restart php8.3-fpm
   ```

---

## 8. Pasang SSL (HTTPS) Gratis dengan Let's Encrypt
Amankan lalu lintas jaringan data website Anda dengan sertifikat SSL gratis menggunakan Certbot:
```bash
# 1. Install Certbot Nginx plugin
sudo apt install certbot python3-certbot-nginx -y

# 2. Ajukan permohonan sertifikat SSL otomatis
sudo certbot --nginx -d domainanda.com -d www.domainanda.com
```
*Ikuti petunjuk interaktif di layar, masukkan alamat email untuk pemberitahuan kedaluwarsa sertifikat, dan pilih opsi untuk **mengarahkan otomatis (redirect) seluruh traffic HTTP ke HTTPS**.*
