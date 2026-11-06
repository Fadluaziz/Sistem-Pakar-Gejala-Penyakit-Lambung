# CLAUDE.md

Panduan kerja untuk Claude Code di repositori ini.

## Tentang Proyek

Sistem pakar diagnosa penyakit lambung berbasis **forward chaining**, dibangun sebagai
aplikasi **Laravel full-stack dengan Blade UI**. Tidak ada API layer (tidak ada
`Controllers/Api`, API Resource, atau `routes/api.php`) — jangan tambahkan.

- **Stack:** PHP 8.2+, Laravel 12, MySQL, Blade, Tailwind CSS 4 + Vite.
- **Peran pengguna:** Pasien, Editor, Dokter, Admin.
- **Bahasa:** seluruh teks UI, pesan validasi, flash message, dan komentar ditulis dalam
  Bahasa Indonesia. Ikuti ini saat menambah kode.

## Perintah Penting

```bash
php artisan serve
php artisan test
php artisan migrate
npm run dev
npm run build
```

Setup awal: `composer run setup`. Database: MySQL, nama `silambung_db` (lihat `.env`).

## Arsitektur: Feature-Based

Kode baru **wajib** masuk ke `app/Features/{FeatureName}/`. Dilarang menaruh kode baru di
`app/Http/Controllers` atau `app/Models` (keduanya hanya menyimpan sisa scaffold Laravel).

```
app/Features/
├── Shared/
│   ├── Controllers/      # BaseController + helper lintas fitur
│   ├── Models/           # Model dipakai ≥2 fitur (mis. User, Role)
│   ├── Services/         # Service lintas fitur (mis. AuthService)
│   └── DTO/              # HANYA untuk parsing data integrasi pihak ketiga (AI/Ollama/OCR)
├── Authentication/
├── ForgotPassword/
├── User/
├── EmailVerification/
└── DiseaseDetection/     # fitur inti sistem pakar
```

Setiap fitur berisi `Controllers/Web/`, `Services/`, `Requests/`, `Models/` (bila ada),
dan `routes_web.php`.

Blade views terpusat di `resources/views/features/{FeatureName}/`, dengan
`resources/views/layouts/` dan `resources/views/components/` untuk layout & komponen
reusable.

### Routing

`routes/web.php` **hanya** berisi `require` file rute tiap fitur:

```php
require base_path('app/Features/Authentication/routes_web.php');
require base_path('app/Features/DiseaseDetection/routes_web.php');
```

Semua route didefinisikan di `app/Features/{Feature}/routes_web.php` dengan nama
terstruktur, mis. `detection.consultation`, `detection.diagnose`, `detection.result`.

## Alur Komponen per Halaman

| Komponen | Lokasi | Tanggung jawab |
|---|---|---|
| Web Controller | `{Feature}/Controllers/Web/` | Terima request, panggil Service, render view / redirect. Wajib extends `BaseController`. |
| FormRequest | `{Feature}/Requests/` | Validasi input POST/PUT/PATCH + `authorize()`. |
| Service | `{Feature}/Services/` | Pusat logika bisnis & transaksi DB. |
| Blade View | `resources/views/features/{Feature}/` | Presentasi saja, tanpa query/bisnis. |

FormRequest tidak diperlukan untuk route GET statis. Jika ada olah data, tetap pakai Service.

## Aturan Kode

**Service** — pusat logika bisnis. Controller dilarang berisi kalkulasi bisnis atau query
rumit. Operasi yang menyentuh >1 tabel wajib dibungkus `DB::transaction(...)`. Service
independen dari HTTP: parameternya primitif (`array`, `int`, `string`) atau model/DTO
eksternal — jangan kirim `Request`.

**Model** — hanya `$table`, `$fillable`, `$guarded`, `$casts`, relasi, dan query scope.
Dilarang menaruh logika inferensi/alur transaksi. Accessor/Mutator sebatas formatting.

**Controller** — tipis: terima request → panggil Service → `view(...)` atau
`redirect()->route(...)->with('success', '...')`. Jangan panggil `DB::table(...)` atau
query model langsung dari controller.

**DTO** — hanya bila ada data dari layanan pihak ketiga. Untuk form internal, cukup
`$request->validated()`.

**Clean code** — namespace selalu `App\Features\{Feature}\...`; type hint & return type
wajib pada semua method Controller & Service; jangan tinggalkan kode mati atau unused
import.

## Konvensi yang Sudah Berjalan di Repo Ini

Ikuti pola yang ada, bukan hanya aturan di atas:

- **Constructor BaseController** — `BaseController::__construct(AuthService $authService)`.
  Subclass memanggilnya lewat `parent::__construct(app(AuthService::class))`, lalu
  mengakses `$this->authService`.
- **Return Service** — method Service mengembalikan `array` dengan bentuk
  `['status' => bool, 'message' => string, 'redirect' => ?string]`, dan Controller yang
  menerjemahkannya jadi `back()->withErrors(...)` atau `redirect()->with('success', ...)`.
- **Redirect per peran** — pola `match ($role) { 'admin' => '/admin/dashboard', ... }`
  muncul di `BaseController::redirectTo()` dan `AuthService::redirectByRole()`. Jaga
  keduanya tetap sinkron bila menambah peran.
- **Anotasi return type belum seragam** — sebagian method (mis.
  `LoginController::showLogin()`) belum punya return type. Lengkapi saat menyentuh file
  tersebut. `BaseController` juga masih membawa import yang tidak terpakai
  (`AuthenticatesUsers`, `JsonResponse`).

## Fitur Inti: Sistem Pakar Forward Chaining

> **Status:** `DiseaseDetection` lengkap — alur konsultasi pasien, riwayat, peninjauan &
> diagnosa manual oleh dokter, serta panel editor untuk mengelola basis aturan
> (penyakit/gejala/bobot). Basis aturan awal tetap dari
> `database/seeders/KnowledgeBaseSeeder.php`; setelahnya diubah lewat panel editor.

Struktur data pakar:

- `diseases` — penyakit lambung (Gastritis, GERD, Tukak Lambung, Dispepsia, Kanker
  Lambung) beserta deskripsi dan solusi.
- `symptoms` — daftar gejala dengan kode (G01, G02, ...).
- `rules` — relasi IF-THEN penyakit ↔ gejala (basis aturan).
- `diagnoses` / `consultation_sessions` — riwayat konsultasi dan hasil diagnosa.

Seluruh penelusuran fakta (gejala terpilih) menuju konklusi (penyakit) **wajib** berada di
`InferenceService` di `app/Features/DiseaseDetection/Services/`. Inferensinya
**forward chaining berbobot**: tiap baris `rules` yang gejalanya muncul menambahkan
bobotnya ke penyakit terkait, lalu keyakinan = bobot terkumpul / total bobot penyakit.

```php
// Murni: hanya menerima id gejala, tidak menyentuh HTTP maupun menulis database.
public function execute(array $symptomIds): array
{
    // skor tiap penyakit → urutkan → deteksi tanda bahaya → tentukan status
}
```

Ambangnya berupa konstanta kelas yang mudah disetel: `MIN_SYMPTOMS` (minimal gejala
terpilih) dan `MIN_CONFIDENCE` (ambang keyakinan agar boleh disebut kesimpulan).

**Tanda bahaya tidak di-hardcode.** Gejala yang memicu peringatan darurat adalah gejala
berkategori `Symptom::CATEGORY_BAHAYA` ("Tanda Bahaya"). Begitu juga bagian-bagian wizard
konsultasi (`ConsultationService::steps()`) **diturunkan dari kolom `symptoms.category`**,
bukan daftar kode tetap — sehingga gejala yang ditambah editor lewat panel basis aturan
langsung muncul di konsultasi.

Penyimpanan sesi + hasilnya ditangani `ConsultationService::finish()` di dalam
`DB::transaction`. Perhitungan keyakinan **tidak** boleh ditulis ulang di controller.

## Panel Basis Aturan (role Editor)

Editor dan Admin mengelola basis aturan lewat route berawalan `editor.` (lihat
`app/Features/DiseaseDetection/routes_web.php`):

- `editor.rules.index/update` — matriks bobot gejala × penyakit.
- `editor.symptoms.index/store/update/destroy` — data gejala.
- `editor.diseases.index/store/update/destroy` — data penyakit.

Logika bisnisnya di `KnowledgeBaseService` (termasuk **penjaga penghapusan**): data yang
sudah dipakai riwayat konsultasi **tidak boleh dihapus**, karena foreign key di database
bersifat cascade — menghapus penyakit/gejala akan ikut menghapus riwayat diagnosa/gejala
pasien.

Hak akses per peran dikontrol via middleware `role` (alias terdaftar di
`bootstrap/app.php`, kelasnya di `app/Features/Shared/Middleware/`), dipakai di
`routes_web.php` sebagai `role:patient,doctor,admin` dan `role:editor,admin`:
Pasien mengisi gejala & melihat hasil/riwayatnya sendiri; Dokter memvalidasi diagnosa &
menambah diagnosa manual; Editor mengelola basis aturan; Admin akses penuh.

## Cheatsheet

| Kebutuhan | Tindakan |
|---|---|
| Halaman/fitur baru | Web Controller + Service + FormRequest (bila ada form) + Blade di `resources/views/features/...` |
| Menjalankan inferensi | Panggil `InferenceService::execute($symptomIds)`, atau `ConsultationService::finish()` bila hasilnya perlu disimpan |
| Mengubah basis aturan | Sunting `database/seeders/KnowledgeBaseSeeder.php`, lalu `php artisan db:seed --class=KnowledgeBaseSeeder` |
| Validasi form | FormRequest di `{Feature}/Requests/` |
| Route fitur baru | Daftarkan di `{Feature}/routes_web.php`, lalu `require` di `routes/web.php` |
| Model dipakai >1 fitur | `app/Features/Shared/Models/` |
| Model khusus penyakit/gejala | `app/Features/DiseaseDetection/Models/` |
