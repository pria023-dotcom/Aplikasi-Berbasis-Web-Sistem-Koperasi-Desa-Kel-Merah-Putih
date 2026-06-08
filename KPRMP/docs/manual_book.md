# MANUAL BOOK & PANDUAN OPERASIONAL
## SISTEM INFORMASI MANAJEMEN KOPERASI DESA/KELURAHAN MERAH PUTIH (KPRMP)

---

### **INFORMASI DOKUMEN**
* **Judul Dokumen:** Manual Book & Panduan Operasional Website KPRMP
* **Versi Aplikasi:** 1.0 (Stabil)
* **Tanggal Rilis:** 26 Mei 2026
* **Bahasa:** Indonesia
* **Penulis:** Tim Pengembang Sistem KPRMP
* **Peruntukan:** Pengurus Koperasi, Kepala Pengawas, Staf Ritel, Staf Gudang, Anggota Koperasi, dan System Developer.

---

## **DAFTAR ISI**
1. [KATA PENGANTAR](#kata-pengantar)
2. [BAB I: PENGENALAN SISTEM KPRMP](#bab-i-pengenalan-sistem-kprmp)
   - 1.1 Latar Belakang
   - 1.2 Tujuan dan Manfaat Aplikasi
3. [BAB II: ARSITEKTUR & STRUKTUR DIREKTORI SISTEM](#bab-ii-arsitektur--struktur-direktori-sistem)
   - 2.1 Struktur Folder Aplikasi KPRMP
   - 2.2 Penjelasan Fungsi Subfolder
4. [BAB III: AKUN PENGGUNA DEFAULT (KREDENSIAL)](#bab-iii-akun-pengguna-default-kredensial)
   - 3.1 Daftar Akun Bawaan Sistem
5. [BAB IV: MATRIKS HAK AKSES & PERAN (ROLES & PERMISSIONS)](#bab-iv-matriks-hak-akses--peran-roles--permissions)
   - 4.1 Deskripsi Peran (Role)
   - 4.2 Matriks Akses Tab Menu & Fitur CRUD
6. [BAB V: PANDUAN OPERASIONAL MODUL (STEP-BY-STEP)](#bab-v-panduan-operasional-modul-step-by-step)
   - 5.1 Modul Login & Autentikasi Keamanan
   - 5.2 Modul Dashboard & Visualisasi Analisis Keuangan
   - 5.3 Modul Keanggotaan Koperasi
   - 5.4 Modul Unit Usaha Ritel (Fitur Audit & Filter Cabang)
   - 5.5 Modul Logistik Pengiriman Barang
   - 5.6 Modul Gudang & Hasil Pertanian Desa
   - 5.7 Modul Permodalan Koperasi
   - 5.8 Modul Pendanaan SAL (Sisa Alokasi Lahan)
   - 5.9 Modul Jaminan Proyek Dana Desa
   - 5.10 Modul Transaksi Simpan Pinjam
   - 5.11 Modul Kalkulasi Bagi Hasil SHU Otomatis
   - 5.12 Modul Sistem Informasi Desa (Info Desa)
7. [BAB VI: PENYELESAIAN MASALAH (TROUBLESHOOTING)](#bab-vi-penyelesaian-masalah-troubleshooting)
   - 6.1 Masalah Login "Username/Password Salah" (Pembaruan Hash Bcrypt)
   - 6.2 Masalah "Aksi Tidak Dikenal" saat Penambahan Data

---

## **KATA PENGANTAR**

Puji syukur kita panjatkan kepada Tuhan Yang Maha Esa atas terselesaikannya pengembangan **Sistem Informasi Manajemen Koperasi Desa/Kelurahan Merah Putih (KPRMP)** beserta buku panduan operasional ini. 

Website KPRMP dirancang khusus untuk memenuhi kebutuhan administrasi dan akuntansi modern koperasi di tingkat desa. Dengan memadukan prinsip transparansi, efisiensi keuangan, dan kemudahan penggunaan, sistem ini diharapkan dapat mempermudah pengurus koperasi, unit usaha ritel, pengawas, hingga anggota dalam mengelola dan memantau aset desa.

Manual Book ini disusun secara sistematis agar pengguna dari berbagai latar belakang peran dapat dengan mudah memahami alur kerja aplikasi, fungsi-fungsi tombol, manajemen data, hingga penyelesaian masalah teknis secara mandiri. Kami menyarankan pengguna membaca bab hak akses dan panduan modul secara seksama sebelum mulai mengoperasikan aplikasi untuk menghindari kesalahan input data.

---

## **BAB I: PENGENALAN SISTEM KPRMP**

### **1.1 Latar Belakang**
Koperasi Desa/Kelurahan Merah Putih (KPRMP) merupakan roda penggerak ekonomi desa yang mencakup berbagai unit bisnis: toko ritel cabang, pengelolaan logistik pengiriman, pergudangan komoditas tani, permodalan, serta pelayanan simpan pinjam bagi warga desa. Pengelolaan yang bersifat konvensional atau pencatatan manual sangat rentan terhadap kesalahan perhitungan, keterlambatan pelaporan, dan kurangnya transparansi data bagi anggota koperasi.

Oleh karena itu, Website KPRMP hadir sebagai solusi digital terintegrasi untuk menyatukan seluruh unit usaha koperasi desa tersebut ke dalam satu sistem berbasis web yang dinamis, responsif, dan mudah diakses.

### **1.2 Tujuan dan Manfaat Aplikasi**
1. **Transparansi Keuangan:** Memberikan laporan visual arus kas dan perhitungan Sisa Hasil Usaha (SHU) secara transparan yang dapat diakses langsung oleh seluruh anggota koperasi.
2. **Efisiensi Operasional:** Mengotomatisasi proses pembagian SHU, kalkulasi modal, audit stok barang ritel, hingga pelacakan logistik pengiriman secara *real-time*.
3. **Keamanan Data:** Membatasi hak akses setiap pengguna melalui sistem *Role‑Based Access Control* (RBAC) yang ketat guna melindungi data sensitif koperasi dari akses ilegal.
4. **Peningkatan Keputusan Bisnis:** Menyajikan grafik analisis keuangan (pendapatan vs beban) tahun berjalan untuk membantu pengurus dan pengawas menentukan arah kebijakan koperasi selanjutnya.

---

## **BAB II: ARSITEKTUR & STRUKTUR DIREKTORI SISTEM**

Untuk memudahkan pemeliharaan kode program (*maintenance*) oleh System Developer maupun Administrator, aplikasi KPRMP disusun menggunakan struktur folder yang sangat rapi dan dikelompokkan sesuai dengan fungsinya masing-masing.

### **2.1 Struktur Folder Aplikasi KPRMP**
Berikut adalah peta direktori proyek KPRMP di dalam server XAMPP (`htdocs/KPRMP/`):

```text
KPRMP/
│
├── api/
│   └── handler.php             <-- Central Request Handler (Backend API)
│
├── assets/
│   ├── css/
│   │   └── style.css           <-- File Desain Tampilan (Premium CSS & Theme)
│   └── js/
│       ├── charts.js           <-- Logika Grafik Analisis Finansial (Chart.js)
│       └── main.js             <-- Logika Interaksi UI, Modal, dan AJAX Request
│
├── config/
│   └── database.php            <-- Konfigurasi Koneksi Database MySQL (PDO)
│
├── database/
│   └── kprmp.sql               <-- File SQL untuk Inisialisasi & Seeding Database
│
├── docs/
│   └── manual_book.md          <-- File Manual Book Panduan Resmi ini (Markdown)
│
├── includes/
│   ├── footer.php              <-- Template Kaki Halaman (Hak Cipta)
│   ├── header.php              <-- Template Kepala Halaman (Informasi User)
│   └── sidebar.php             <-- Template Panel Navigasi Samping (Menu Utama)
│
├── index.php                   <-- Halaman Utama Aplikasi (Dashboard Terpadu)
├── login.php                   <-- Halaman Masuk Akun Pengguna
├── logout.php                  <-- Skrip Keluar dari Sesi Aplikasi
└── register.php                <-- Halaman Registrasi Akun Anggota Baru
```

### **2.2 Penjelasan Fungsi Subfolder**
* **`api/`**: Berisi gerbang backend terpusat (`handler.php`) yang memproses seluruh operasi penambahan data, pembaruan, persetujuan pinjaman, kalkulasi SHU, hingga penghapusan rekaman data menggunakan arsitektur RESTful JSON.
* **`assets/`**: Berisi file statis untuk mempercantik dan menghidupkan website. `css/style.css` mendefinisikan palet warna, tipografi modern, efek glassmorphism, dan animasi mikro. `js/main.js` mengontrol tampilan modal, animasi navigasi tab, pengiriman form menggunakan AJAX, serta konfirmasi hapus data.
* **`config/`**: Menyimpan kredensial database MySQL. Semua skrip PHP yang membutuhkan interaksi dengan database memanggil berkas koneksi PDO terpusat dari folder ini.
* **`database/`**: Menyimpan skrip SQL (`kprmp.sql`) untuk memudahkan proses instalasi ulang database, pembuatan tabel secara otomatis, dan pengisian data uji coba awal (*seeding*).
* **`docs/`**: Subfolder khusus yang didedikasikan untuk menyimpan dokumentasi proyek, manual book operasional, dan panduan teknis agar terpisah secara rapi dari kode fungsional aplikasi.
* **`includes/`**: Menyimpan potongan template antarmuka (`header`, `sidebar`, `footer`) yang dipanggil secara dinamis di setiap modul untuk menjaga konsistensi tampilan layout.

---

## **BAB III: AKUN PENGGUNA DEFAULT (KREDENSIAL)**

Untuk mempermudah pengujian dan operasional awal, sistem telah dilengkapi dengan beberapa akun bawaan yang memiliki wewenang peran berbeda-beda. 

### **3.1 Daftar Akun Bawaan Sistem**
Berikut adalah daftar kredensial login bawaan yang telah diamankan menggunakan enkripsi **Bcrypt**:

| No | Username | Nama Pengguna | Peran (Role) | Kata Sandi (Password) | Deskripsi Hak Akses Utama |
|:--:|:---|:---|:---|:---|:---|
| 1 | **admin** | Administrator Utama | Administrator | `admin123` | Akses penuh seluruh sistem & konfigurasi |
| 2 | **pengawas** | Kepala Pengawas | Kepala Pengawas | `pengawas123` | Akses penuh monitoring untuk keperluan audit |
| 3 | **budi** | Budi Santoso | Anggota Koperasi | `budi123` | Akses personal simpanan, pinjaman & SHU |
| 4 | **ritel** | Staf Toko Ritel | Satuan Unit Ritel | `ritel123` | Akses modul Ritel & Logistik |
| 5 | **gudang** | Staf Gudang | Satuan Unit Gudang | `gudang123` | Akses modul Gudang & Logistik |
| 6 | **developer**| System Developer | System Developer | `developer123` | Akses penuh sistem untuk pemeliharaan teknis |

> **[IMPORTANT]**
> Untuk alasan keamanan tingkat tinggi, Administrator sangat disarankan untuk mengimbau seluruh staf dan anggota segera mengubah kata sandi bawaan mereka setelah berhasil melakukan login pertama kali di aplikasi.

---

## **BAB IV: MATRIKS HAK AKSES & PERAN (ROLES & PERMISSIONS)**

Sistem KPRMP menerapkan otorisasi ketat berbasis peran (*Role‑Based Access Control*). Hal ini memastikan bahwa data finansial, inventaris ritel, logistik, dan data gudang hanya dapat diubah oleh personel yang memiliki wewenang sah.

### **4.1 Deskripsi Peran (Role)**
1. **System Developer:** Bertanggung jawab penuh atas kelancaran teknis sistem, perbaikan bug, integrasi database, dan memiliki akses menyeluruh ke semua modul.
2. **Administrator:** Pengurus harian koperasi yang memiliki wewenang tertinggi untuk menambah, mengubah, dan menghapus seluruh data (anggota, keuangan, proyek desa, permodalan, dll.).
3. **Kepala Pengawas:** Dewan pengawas yang bertugas memantau kinerja koperasi. Memiliki hak akses penuh untuk melihat semua data untuk transparansi, serta dapat menambah/mengedit data jika diperlukan dalam proses audit.
4. **Satuan Unit Ritel:** Staf operasional toko ritel koperasi yang bertugas mengelola inventaris produk, mengaudit stok barang, dan mendaftarkan pengiriman logistik ke konsumen.
5. **Satuan Unit Gudang:** Staf operasional gudang komoditas pertanian yang bertugas menginput hasil panen petani, menentukan grade kualitas tani, serta memantau logistik pengiriman pasokan.
6. **Anggota Koperasi:** Warga desa terdaftar yang hanya memiliki akses personal. Anggota dapat melihat tab dashboard, detail simpanan/pinjaman pribadinya, laporan transparansi bagi hasil SHU, dan membaca informasi terbaru dari desa.

### **4.2 Matriks Akses Tab Menu & Fitur CRUD**
Berikut adalah tabel detail yang menunjukkan tab menu apa saja yang muncul di sidebar dan tindakan apa saja yang diizinkan untuk setiap peran:

| Modul / Tab Menu | Developer | Admin | Pengawas | Staf Ritel | Staf Gudang | Anggota |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Dashboard** | View + Edit | View + Edit | View + Edit | View | View | View (Personal) |
| **Keanggotaan** | CRUD | CRUD | CRUD | No Access | No Access | No Access |
| **Usaha Ritel** | CRUD | CRUD | CRUD | CRUD | No Access | No Access |
| **Logistik** | CRUD | CRUD | CRUD | CRUD | CRUD | No Access |
| **Gudang & Tani**| CRUD | CRUD | CRUD | No Access | CRUD | No Access |
| **Permodalan** | CRUD | CRUD | CRUD | No Access | No Access | No Access |
| **Pendanaan SAL**| CRUD | CRUD | CRUD | No Access | No Access | No Access |
| **Jaminan Desa** | CRUD | CRUD | CRUD | No Access | No Access | No Access |
| **Simpan Pinjam**| CRUD | CRUD | CRUD | No Access | No Access | View (Personal) |
| **Bagi Hasil SHU**| CRUD + Config| CRUD + Config| CRUD + Config| No Access | No Access | View (Personal) |
| **Info Desa** | CRUD | CRUD | CRUD | CRUD | CRUD | View |

*Keterangan Singkat:*
* **CRUD:** Dapat melakukan *Create* (Tambah), *Read* (Melihat), *Update* (Mengubah), dan *Delete* (Menghapus) data.
* **View (Personal):** Hanya dapat melihat data yang terkait langsung dengan akun dirinya sendiri (misalnya, Anggota Budi hanya melihat riwayat tabungan dan pinjaman atas nama Budi).
* **Config:** Memiliki tombol khusus untuk mengubah persentase pembagian alokasi dana SHU.
* **No Access:** Tab menu tidak muncul di sidebar dan akses URL langsung akan ditolak secara otomatis oleh backend API (`handler.php`).

---

## **BAB V: PANDUAN OPERASIONAL MODUL (STEP-BY-STEP)**

### **5.1 Modul Login & Autentikasi Keamanan**
* **Tujuan:** Memverifikasi identitas pengguna dan memberikan tingkat akses menu yang sesuai dengan peran mereka.
* **Langkah-langkah Penggunaan:**
  1. Buka browser Anda (Google Chrome/Microsoft Edge) dan akses URL aplikasi: `http://localhost/KPRMP/login.php`
  2. Anda akan disajikan halaman masuk modern bermotif Merah Putih dengan efek *Glassmorphic Card*.
  3. Masukkan **Username Akun** Anda pada kolom pertama (sensitif terhadap huruf besar/kecil).
  4. Masukkan **Kata Sandi (Password)** Anda pada kolom kedua secara teliti.
  5. Tekan tombol **"Masuk Sistem"**.
  6. Jika login berhasil, sistem akan mendeteksi peran Anda, membuat sesi terenkripsi, dan mengarahkan Anda secara instan ke halaman Dashboard Utama.
  7. Jika gagal, akan muncul kotak peringatan berwarna merah bertuliskan *"Username atau password salah."*.

---

### **5.2 Modul Dashboard & Visualisasi Analisis Keuangan**
* **Tujuan:** Memberikan gambaran ringkas kinerja koperasi secara visual melalui indikator angka dan grafik interaktif.
* **Fitur Utama:**
  * **Widget Ringkasan:** Menampilkan total anggota aktif, jumlah produk ritel, total simpanan anggota, dan pinjaman aktif berjalan.
  * **Grafik Finansial (Interactive Chart):** Grafik dinamis yang menampilkan visualisasi pendapatan operasional berbanding beban operasional koperasi di tahun 2026.
  * **Panel Status Usaha:** Menampilkan total stok pertanian di gudang (satuan Ton), modal saat ini, penyaluran dana SAL, penjaminan dana desa, serta perolehan Sisa Hasil Usaha (SHU) bersih koperasi.
* **Cara Membaca Grafik:**
  * Batang grafik menunjukkan perbandingan nominal kas. Arahkan kursor (*hover*) pada batang grafik untuk menampilkan tooltip berisi angka desimal secara presisi.

---

### **5.3 Modul Keanggotaan Koperasi**
* **Tujuan:** Mengelola basis data seluruh warga desa yang menjadi anggota koperasi Merah Putih.
* **Langkah Menambah Anggota Baru:**
  1. Klik tab menu **"Keanggotaan"** di sidebar.
  2. Tekan tombol **"+ Tambah Anggota"** di sudut kanan atas tabel.
  3. Form input modal akan muncul. Isi data dengan lengkap:
     * *Nama Lengkap:* Isi nama sesuai dengan KTP.
     * *NIK (Nomor Induk Kependudukan):* Masukkan 16 digit NIK.
     * *No Telepon:* Masukkan nomor HP aktif (contoh: `081234567890`).
     * *Alamat:* Tulis alamat dusun/RT/RW tempat tinggal anggota.
     * *Tanggal Bergabung:* Pilih tanggal pendaftaran (default: hari ini).
     * *Status Keanggotaan:* Pilih *Aktif* atau *Nonaktif*.
  4. Klik tombol **"Simpan Data"**. Sistem akan otomatis men-generate **Kode Anggota Unik** baru dengan format urut `KPR-XXXX` (contoh: `KPR-0007`).
* **Langkah Mengubah atau Menghapus Data Anggota:**
  * Klik ikon pensil berwarna kuning (✏️) untuk melakukan pengeditan data.
  * Klik ikon tempat sampah berwarna merah (🗑️) untuk menghapus data anggota. Konfirmasi penghapusan data akan muncul terlebih dahulu sebelum skrip dijalankan.

---

### **5.4 Modul Unit Usaha Ritel (Fitur Audit & Filter Cabang)**
* **Tujuan:** Mengontrol stok, harga beli/jual, pemasok, dan inventarisasi produk di seluruh toko cabang milik koperasi.
* **Langkah Operasional & Fitur Unggulan:**
  1. **Filter Cabang Toko:**
     * Koperasi memiliki 3 lokasi toko usaha ritel: Toko Pusat, Cabang Dusun I, dan Cabang Dusun II.
     * Pengguna dapat memilih opsi pada menu dropdown **"Lokasi/Cabang"** untuk menyaring produk di toko tertentu secara instan.
  2. **Tambah Produk Baru:**
     * Tekan tombol **"+ Tambah Barang"**.
     * Isi Kode Barang, Nama Barang, Kategori (contoh: Sembako, Pupuk, dll.), Jumlah Stok Awal, Harga Beli, Harga Jual, Pemasok (*Supplier*), dan lokasi Cabang produk tersebut disimpan. Klik **"Simpan Data"**.
  3. **Fitur Audit & Pembaruan Stok Cepat (Quick Stock Audit):**
     * Di samping tombol edit, terdapat tombol pintasan melingkar biru (🔄).
     * Fitur ini didedikasikan untuk staf ritel guna memperbarui jumlah stok fisik barang secara cepat setelah melakukan *stock opname* harian tanpa perlu membuka form edit lengkap. Cukup masukkan jumlah stok terbaru dan tekan simpan.

---

### **5.5 Modul Logistik Pengiriman Barang**
* **Tujuan:** Melacak pengiriman komoditas pertanian atau pasokan ritel dari pengirim hingga sampai ke tangan penerima.
* **Langkah Operasional:**
  1. Klik tab menu **"Logistik"**.
  2. Untuk menambah data, tekan **"+ Tambah Pengiriman"**.
  3. Isi muatan barang, nama pengirim, nama penerima, tanggal kirim, dan pilih status pengiriman (*Diproses*, *Dalam Perjalanan*, *Diterima*, *Pending*).
  4. Kolom **"No Resi/Pelacakan"** dapat dikosongkan; jika kosong, sistem backend akan otomatis membuatkan kode resi pelacakan unik berformat `TRKYYYYMMDDXX` secara otomatis (contoh: `TRK2026052614`).
  5. Perbarui status logistik menjadi **"Diterima"** setelah kurir mengonfirmasi barang telah sampai di tujuan.

---

### **5.6 Modul Gudang & Hasil Pertanian Desa**
* **Tujuan:** Mencatat hasil panen petani lokal yang ditampung oleh koperasi desa serta memantau kualitasnya.
* **Langkah Operasional:**
  1. Klik tab menu **"Gudang & Tani"**.
  2. Tekan tombol **"+ Catat Hasil Pertanian"**.
  3. Masukkan data komoditas (misalnya: Padi, Jagung, Kopi, Karet).
  4. Masukkan jumlah berat dalam satuan **Ton** (contoh: `3.5` untuk 3,5 Ton).
  5. Pilih Kualitas/Grade panen:
     * *Grade A:* Kualitas premium (harga jual tertinggi).
     * *Grade B:* Kualitas medium.
     * *Grade C:* Kualitas rendah/curah.
  6. Pilih Tanggal Masuk gudang dan sebutkan Lokasi Penyimpanan (contoh: Gudang Barat, Silo 3). Klik **"Simpan Data"**.

---

### **5.7 Modul Permodalan Koperasi**
* **Tujuan:** Mencatat dan melacak seluruh arus masuk modal koperasi yang bersumber dari berbagai pihak.
* **Langkah Operasional:**
  1. Klik menu **"Permodalan"**.
  2. Tekan **"+ Tambah Modal"**.
  3. Tuliskan **Sumber Modal** (misalnya: Hibah Provinsi, Investasi Anggota, Dana Sisa Hasil Usaha, APBDesa).
  4. Masukkan nominal uang rupiah pada kolom **Jumlah Modal** (tanpa tanda titik/koma, contoh: `50000000` untuk Rp 50.000.000).
  5. Masukkan tanggal penerimaan modal dan berikan catatan penjelas pada kolom **Keterangan**. Klik simpan.

---

### **5.8 Modul Pendanaan SAL (Sisa Alokasi Lahan)**
* **Tujuan:** Mengelola penyaluran dana sosial dan pembangunan desa yang bersumber dari bagi hasil sewa atau sisa alokasi lahan desa.
* **Langkah Operasional:**
  1. Klik menu **"Pendanaan SAL"**.
  2. Klik **"+ Tambah Penyaluran"**.
  3. Isi nama program pembangunan desa (contoh: Perbaikan Irigasi Dusun II, Pembangunan Gapura Desa).
  4. Masukkan nominal dana SAL yang disalurkan, tanggal penyaluran, serta status program (*Direncanakan*, *Disalurkan*, *Selesai*).

---

### **5.9 Modul Jaminan Proyek Dana Desa**
* **Tujuan:** Mencatat jaminan keuangan yang diberikan oleh koperasi sebagai penjamin kelancaran pelaksanaan proyek-proyek fisik pembangunan desa.
* **Langkah Operasional:**
  1. Klik menu **"Jaminan Desa"**.
  2. Tekan tombol **"+ Tambah Jaminan Proyek"**.
  3. Masukkan nama proyek pembangunan yang dijamin (contoh: Pengaspalan Jalan Lingkar Desa).
  4. Masukkan jumlah nominal jaminan yang disepakati, tanggal penerbitan surat jaminan, dan pilih status jaminan (*Aktif* atau *Selesai*).

---

### **5.10 Modul Transaksi Simpan Pinjam**
* **Tujuan:** Melayani penabungan dan peminjaman dana bagi anggota koperasi secara terkontrol.
* **Alur Pengajuan dan Persetujuan Kredit (Pinjaman):**
  1. **Pencatatan Transaksi Baru:**
     * Pengurus masuk ke menu **"Simpan Pinjam"** lalu menekan tombol **"+ Transaksi Baru"**.
     * Pilih nama anggota dari pilihan yang tersedia.
     * Pilih jenis transaksi: *Simpanan Pokok*, *Simpanan Wajib*, *Simpanan Sukarela*, atau *Pinjaman*.
     * Masukkan nominal dana simpanan/pinjaman.
     * Jika memilih transaksi **Pinjaman**, tentukan bunga per tahun (persen) dan jangka waktu pelunasan (tenor dalam satuan bulan).
     * Klik **"Simpan Data"**.
  2. **Sistem Pengaman Kredit (Persetujuan Otomatis):**
     * Demi menjaga kesehatan keuangan koperasi, setiap transaksi jenis **Pinjaman** baru yang diinput akan otomatis berstatus **"Menunggu"** dan **tidak akan terhitung** ke dalam kas sebelum mendapat persetujuan.
     * Akun Administrator, Kepala Pengawas, atau Developer harus meninjau pengajuan ini dan menekan tombol hijau bertuliskan **"Setujui"** pada tabel aksi untuk mencairkan kredit tersebut dan mengubah statusnya menjadi **"Disetujui"** atau **"Aktif"**.

---

### **5.11 Modul Kalkulasi Bagi Hasil SHU Otomatis**
* **Tujuan:** Menghitung sisa hasil usaha bersih koperasi secara transparan dan membaginya secara adil ke masing-masing pos alokasi serta individu anggota.
* **Langkah bagi Administrator/Staf Pengurus (Konfigurasi):**
  1. Klik menu **"Bagi Hasil SHU"**.
  2. Anda akan disajikan formulir input pos alokasi persen.
  3. Masukkan **Total Pendapatan** dan **Total Beban** operasional koperasi pada tahun buku berjalan.
  4. Atur persentase alokasi untuk pos berikut:
     * *Dana Cadangan Koperasi (%):* Cadangan kas koperasi.
     * *Jasa Modal Anggota (%):* Bagian SHU yang dibagikan secara adil berdasarkan proporsi simpanan anggota.
     * *Jasa Transaksi Usaha Anggota (%):* Bagian SHU yang dibagikan berdasarkan kontribusi pinjaman anggota.
     * *Dana Pendidikan (%):* Alokasi pelatihan pengurus & anggota.
     * *Dana Pengurus & Pengawas (%):* Alokasi insentif kinerja dewan pengurus.
  5. **[PERINGATAN]** Total penjumlahan persentase dari kelima pos di atas **HARUS TEPAT 100%**. Jika kurang atau lebih (misal: 101%), sistem backend akan menolak penyimpanan dan menampilkan error.
  6. Tekan tombol **"Simpan & Hitung"**. Sistem akan secara instan mengkalkulasi nominal rupiah di setiap pos berdasarkan SHU Bersih (Pendapatan dikurangi Beban).

* **Tampilan bagi Anggota Koperasi (Transparansi Saldo):**
  * Anggota Koperasi yang masuk ke menu ini tidak akan melihat form edit persentase.
  * Sebagai gantinya, mereka akan disajikan ringkasan transparan mengenai rumus perhitungan dan **kotak informasi nominal SHU pribadi** yang akan mereka terima. SHU anggota dihitung secara adil menggunakan algoritma:
    $$\text{SHU Jasa Modal Anda} = \left(\frac{\text{Simpanan Pribadi}}{\text{Total Simpanan Koperasi}}\right) \times \text{Alokasi Pool Jasa Modal}$$
    $$\text{SHU Jasa Transaksi Anda} = \left(\frac{\text{Pinjaman Pribadi}}{\text{Total Pinjaman Koperasi}}\right) \times \text{Alokasi Pool Jasa Transaksi}$$
    $$\text{Total SHU Diterima Anggota} = \text{SHU Jasa Modal Anda} + \text{SHU Jasa Transaksi Anda}$$

---

### **5.12 Modul Sistem Informasi Desa (Info Desa)**
* **Tujuan:** Wadah publikasi pengumuman penting, berita pertanian, informasi rapat tahunan anggota, maupun sosialisasi program desa kepada masyarakat.
* **Langkah Operasional:**
  1. Buka menu **"Info Desa"**.
  2. Tekan tombol **"+ Tambah Informasi"** (untuk Staf Ritel, Staf Gudang, Pengawas, Developer, dan Admin).
  3. Isi Judul Pengumuman, tulis isi informasi secara lengkap pada area teks, pilih Kategori (Berita, Pengumuman, Agenda, Pertanian), dan pilih Tanggal Publikasi. Klik simpan.
  4. Anggota koperasi dapat membaca postingan ini secara urut berdasarkan tanggal penerbitan terbaru langsung dari dashboard personal mereka.

---

## **BAB VI: PENYELESAIAN MASALAH (TROUBLESHOOTING)**

Dalam operasional harian website KPRMP, terdapat dua kendala utama yang sebelumnya sering ditemui oleh pengguna. Berikut adalah penyebab dan langkah penyelesaian permanen yang kini telah diterapkan di sistem:

### **6.1 Masalah Login "Username/Password Salah" (Pembaruan Hash Bcrypt)**
* **Gejala:** Pengguna default (seperti `pengawas`, `gudang`, `ritel`, dll.) tidak bisa login ke dalam aplikasi, meskipun telah memasukkan password default yang tertulis di dokumentasi dengan benar.
* **Penyebab:** Pada database inisial sebelumnya, seluruh record pengguna bawaan memiliki string enkripsi Bcrypt yang persis sama, di mana string enkripsi tersebut adalah milik password `admin123`. Hal ini menyebabkan semua pengguna hanya bisa login jika menggunakan password `admin123`, dan gagal jika menggunakan password aslinya (seperti `pengawas123`).
* **Solusi yang Telah Diterapkan:**
  1. File inisialisasi database `database/kprmp.sql` telah diperbarui. Setiap pengguna bawaan kini memiliki hash Bcrypt unik yang valid dan sesuai dengan kata sandinya masing-masing (misal: user `pengawas` memiliki hash khusus untuk password `pengawas123`).
  2. Jika database aktif di XAMPP Anda belum terupdate, System Developer telah membuatkan skrip migrasi PHP otomatis sekali pakai untuk mengupdate password pengguna langsung ke database aktif MySQL Anda. Masalah ini kini telah teratasi 100% dan seluruh pengguna dapat login dengan lancar.

---

### **6.2 Masalah "Aksi Tidak Dikenal" saat Penambahan Data**
* **Gejala:** Saat pengguna menekan tombol **"Simpan Data"** pada form tambah anggota baru atau barang ritel baru, sistem memunculkan kotak pesan alert merah bertuliskan *"Aksi tidak dikenal"* dan data gagal tersimpan ke database.
* **Penyebab:** Tombol tambah data di frontend JavaScript (`assets/js/main.js`) secara dinamis merangkai aksi dalam bahasa Indonesia berformat `add_${module}` (contoh: `add_keanggotaan`, `add_ritel`, `add_logistik`). Namun, di sisi server backend (`api/handler.php`), skrip penanganan kondisi `switch-case` mengekspektasikan aksi bahasa Inggris (contoh: `add_member`, `add_goods`, `add_logistics`).
* **Solusi yang Telah Diterapkan:**
  1. **Perbaikan Frontend:** Mengubah fungsi `openAddModal(module)` di file JavaScript agar memetakan parameter module bahasa Indonesia ke aksi bahasa Inggris sebelum mengirimkan data via AJAX.
  2. **Perbaikan Backend (Double Protection):** Menambahkan array pemetaan fallback `actionMap` di baris awal `api/handler.php` yang otomatis mengonversi aksi bahasa Indonesia menjadi aksi bahasa Inggris sebelum masuk ke blok `switch-case`. Perbaikan ganda ini menjamin pengiriman form tambah data dari modul mana pun akan selalu diproses sukses oleh server tanpa memunculkan error *"Aksi tidak dikenal"* lagi.

---

### **REKOMENDASI PENGAMANAN TAMBAHAN**
1. **Pembersihan Cache Browser:** Jika setelah perbaikan sistem Anda masih mengalami error lama, lakukan pembersihan cache browser dengan menekan tombol **Ctrl + F5** secara bersamaan pada halaman aplikasi untuk memuat ulang berkas Javascript terbaru.
2. **Koneksi Database:** Pastikan konfigurasi database di `config/database.php` sesuai dengan port dan kredensial server MySQL di perangkat komputer/hosting Anda.

---
*(Akhir dari Dokumen Manual Book KPRMP Versi 1.0)*
