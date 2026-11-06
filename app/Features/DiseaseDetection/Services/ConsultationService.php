<?php

namespace App\Features\DiseaseDetection\Services;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Diagnosis;
use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Symptom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Alur konsultasi: definisi langkah wizard, percabangan antar langkah, dan
 * penyimpanan hasil konsultasi.
 *
 * Kondisi percabangan memakai KODE gejala (G01, G16, ...), bukan id, karena kode
 * inilah yang stabil dan dipakai bersama KnowledgeBaseSeeder. Semua id di-resolve
 * lewat database.
 *
 * Service ini tidak menyentuh session HTTP; draft jawaban dikelola controller.
 */
class ConsultationService
{
    /**
     * Kalimat pertanyaan Ya/Tidak per kode gejala. Kode harus sama dengan
     * KnowledgeBaseSeeder. Bila sebuah kode tidak terdaftar di sini, nama gejala
     * dipakai apa adanya sebagai pertanyaan.
     *
     * @var array<string, string>
     */
    private const QUESTIONS = [
        'G01' => 'Apakah Anda merasakan nyeri atau perih di ulu hati?',
        'G02' => 'Apakah perut Anda terasa kembung atau begah?',
        'G03' => 'Apakah Anda cepat merasa kenyang saat makan?',
        'G04' => 'Apakah perut bagian atas terasa penuh atau tidak nyaman?',
        'G05' => 'Apakah Anda merasa mual?',
        'G06' => 'Apakah Anda pernah muntah?',
        'G07' => 'Apakah nafsu makan Anda berkurang akhir-akhir ini?',
        'G08' => 'Apakah Anda sering bersendawa?',
        'G09' => 'Apakah nyeri ulu hati muncul saat perut kosong atau sebelum makan?',
        'G10' => 'Apakah nyeri ulu hati muncul 1-3 jam setelah makan?',
        'G11' => 'Apakah nyeri ulu hati berkurang setelah makan?',
        'G12' => 'Apakah nyeri ulu hati berkurang setelah minum obat antasida?',
        'G13' => 'Apakah nyeri ulu hati justru memburuk setelah makan?',
        'G14' => 'Apakah nyeri ulu hati muncul atau memberat pada malam hari?',
        'G15' => 'Apakah nyeri ulu hati terasa panas atau terbakar?',
        'G16' => 'Apakah Anda merasakan rasa panas atau terbakar di dada?',
        'G17' => 'Apakah rasa asam atau pahit naik ke kerongkongan?',
        'G18' => 'Apakah mulut Anda terasa asam atau pahit?',
        'G19' => 'Apakah keluhan memburuk saat berbaring atau membungkuk?',
        'G20' => 'Apakah Anda sulit atau nyeri saat menelan?',
        'G21' => 'Apakah Anda batuk kering atau suara serak tanpa sebab jelas?',
        'G22' => 'Apakah BAB Anda berwarna hitam seperti aspal?',
        'G23' => 'Apakah Anda pernah muntah darah atau cairan seperti kopi?',
        'G24' => 'Apakah berat badan Anda turun tanpa sebab jelas?',
        'G25' => 'Apakah nyeri perut terasa hebat dan menjalar ke punggung?',
    ];

    public function __construct(
        private readonly InferenceService $inference,
    ) {}

        /**
     * Pemetaan kategori gejala ke bagian konsultasi. Pertanyaan tiap bagian
     * diturunkan dari kolom `symptoms.category`, bukan kode hardcoded — sehingga
     * gejala yang ditambah/diubah editor langsung tercermin di konsultasi.
     *
     * @var array<int, array{slug: string, title: string, description: string, category: string}>
     */
    private const SECTIONS = [
        [
            'slug' => 'utama',
            'title' => 'Gejala Umum',
            'description' => 'Jawab Ya atau Tidak untuk setiap keluhan berikut.',
            'category' => Symptom::CATEGORY_UMUM,
        ],
        [
            'slug' => 'nyeri',
            'title' => 'Pola Nyeri Ulu Hati',
            'description' => 'Jawab bagaimana pola nyeri ulu hati yang Anda rasakan.',
            'category' => Symptom::CATEGORY_NYERI,
        ],
        [
            'slug' => 'refluks',
            'title' => 'Gejala Refluks dan Tenggorokan',
            'description' => 'Jawab gejala yang berkaitan dengan naiknya asam lambung.',
            'category' => Symptom::CATEGORY_REFLUKS,
        ],
        [
            'slug' => 'bahaya',
            'title' => 'Tanda Bahaya',
            'description' => 'Bagian terakhir. Jawab bila Anda pernah mengalami tanda berikut.',
            'category' => Symptom::CATEGORY_BAHAYA,
        ],
    ];

    /**
     * Definisi bagian konsultasi secara berurutan.
     *
     * Setiap bagian memuat pertanyaan Ya/Tidak; tiap pertanyaan mewakili satu
     * gejala dengan kategori yang sesuai. Semua bagian SELALU ditampilkan supaya
     * seluruh pertanyaan terlewati dan dasar diagnosa lebih lengkap.
     *
     * @return array<int, array{
     *     slug: string,
     *     title: string,
     *     description: string,
     *     codes: array<int, string>,
     *     condition: null
     * }>
     */
    public function steps(): array
    {
        return array_map(fn (array $section): array => [
            'slug' => $section['slug'],
            'title' => $section['title'],
            'description' => $section['description'],
            'codes' => Symptom::where('category', $section['category'])
                ->orderBy('code')
                ->pluck('code')
                ->all(),
            'condition' => null,
        ], self::SECTIONS);
    }

    /**
     * Definisi satu langkah, atau null bila slug tidak dikenal.
     *
     * @return array<string, mixed>|null
     */
    public function step(string $slug): ?array
    {
        return collect($this->steps())->firstWhere('slug', $slug);
    }

    /**
     * Slug langkah yang berlaku untuk draft jawaban saat ini, berurutan.
     *
     * @param  array<int, int|string>  $selectedIds
     * @return array<int, string>
     */
    public function applicableSteps(array $selectedIds): array
    {
        $selectedCodes = $this->codesFor($selectedIds);

        return collect($this->steps())
            ->filter(function (array $step) use ($selectedCodes): bool {
                if ($step['condition'] === null) {
                    return true;
                }

                return array_intersect($step['condition']['any_of'], $selectedCodes) !== [];
            })
            ->pluck('slug')
            ->all();
    }

    /**
     * Gejala milik sebuah langkah, dibungkus sebagai pertanyaan Ya/Tidak.
     *
     * Teks pertanyaan diambil dari nama gejala; bila ada padanan kalimat tanya yang
     * lebih natural, dipakai itu. Kode & id tetap dibawa untuk mengisi form.
     *
     * @return Collection<int, array{id: int, code: string, question: string}>
     */
    public function questionsForStep(string $slug): Collection
    {
        return $this->symptomsForStep($slug)->map(fn (Symptom $symptom): array => [
            'id' => (int) $symptom->id,
            'code' => $symptom->code,
            'question' => self::QUESTIONS[$symptom->code] ?? $symptom->name,
        ])->values();
    }

    /**
     * Gejala milik sebuah langkah, terurut sesuai urutan di definisi langkah.
     *
     * @return Collection<int, Symptom>
     */
    public function symptomsForStep(string $slug): Collection
    {
        $codes = $this->step($slug)['codes'] ?? [];

        if ($codes === []) {
            return collect();
        }

        return Symptom::whereIn('code', $codes)
            ->get()
            ->sortBy(fn (Symptom $symptom): int|false => array_search($symptom->code, $codes, true))
            ->values();
    }

    /**
     * @return array<int, int>
     */
    public function symptomIdsForStep(string $slug): array
    {
        $codes = $this->step($slug)['codes'] ?? [];

        if ($codes === []) {
            return [];
        }

        return Symptom::whereIn('code', $codes)->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    /**
     * Gabungkan jawaban satu langkah ke draft.
     *
     * Jawaban lama untuk langkah yang sama dibuang lebih dulu, sehingga menekan
     * "Kembali" lalu menyimpan ulang tidak menggandakan atau menyisakan pilihan
     * yang sudah tidak dicentang.
     *
     * @param  array<int, int|string>  $selectedIds  pilihan pada langkah ini
     * @param  array<int, int|string>  $draft  akumulasi jawaban sebelumnya
     * @return array<int, int>
     */
    public function saveStep(string $slug, array $selectedIds, array $draft): array
    {
        $stepIds = $this->symptomIdsForStep($slug);

        $kept = array_filter(
            array_map('intval', $draft),
            fn (int $id): bool => ! in_array($id, $stepIds, true),
        );

        $chosen = array_map('intval', $selectedIds);

        return array_values(array_unique([...$kept, ...$chosen]));
    }

    /**
     * Daftar penyakit untuk halaman edukasi, lengkap dengan gejala khasnya.
     *
     * @return Collection<int, Disease>
     */
    public function diseasePreview(): Collection
    {
        return Disease::with('symptoms')->orderBy('code')->get();
    }

    /**
     * Jalankan inferensi atas draft lalu simpan sesi konsultasi beserta hasilnya.
     */
    public function finish(array $selectedIds, int $userId): ConsultationSession
    {
        $result = $this->inference->execute($selectedIds);

        return DB::transaction(function () use ($selectedIds, $userId, $result): ConsultationSession {
            $session = ConsultationSession::create([
                'user_id' => $userId,
                'status' => $result['status'],
                'has_red_flag' => $result['has_red_flag'],
            ]);

            $session->symptoms()->sync($selectedIds);

            foreach ($result['candidates'] as $index => $candidate) {
                Diagnosis::create([
                    'consultation_session_id' => $session->id,
                    'disease_id' => $candidate['disease_id'],
                    'confidence' => $candidate['confidence'],
                    'breakdown' => $candidate['matched'],
                    'total_weight' => $candidate['total_weight'],
                    'rank' => $index + 1,
                ]);
            }

            return $session;
        });
    }

    /**
     * Kode gejala dari sekumpulan id.
     *
     * @param  array<int, int|string>  $ids
     * @return array<int, string>
     */
    private function codesFor(array $ids): array
    {
        $ids = array_filter(array_map('intval', $ids));

        if ($ids === []) {
            return [];
        }

        return Symptom::whereIn('id', $ids)->pluck('code')->all();
    }
}
