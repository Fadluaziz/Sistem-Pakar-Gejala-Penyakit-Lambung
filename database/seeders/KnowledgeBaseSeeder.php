<?php

namespace Database\Seeders;

use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Rule;
use App\Features\DiseaseDetection\Models\Symptom;
use Illuminate\Database\Seeder;

/**
 * Basis pengetahuan sistem pakar penyakit lambung.
 *
 * Bobot berskala 1–5 (5 = paling khas untuk penyakit tersebut). Hanya gejala yang
 * MEMBEDAKAN keempat penyakit yang diberi bobot; gejala yang tidak diskriminatif
 * sengaja dibiarkan tanpa bobot agar persentase keyakinan tetap bermakna.
 *
 * Kode gejala di sini harus sama dengan kode yang dirujuk
 * App\Features\DiseaseDetection\Services\ConsultationService::steps();
 * ada test yang memastikan keduanya tidak melenceng.
 */
class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Daftar gejala: kode => [nama, kategori].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const SYMPTOMS = [
        'G01' => ['Nyeri atau perih di ulu hati', 'Umum'],
        'G02' => ['Perut terasa kembung atau begah', 'Umum'],
        'G03' => ['Cepat merasa kenyang saat makan', 'Umum'],
        'G04' => ['Rasa penuh atau tidak nyaman di perut bagian atas', 'Umum'],
        'G05' => ['Mual', 'Umum'],
        'G06' => ['Muntah', 'Umum'],
        'G07' => ['Hilang atau berkurangnya nafsu makan', 'Umum'],
        'G08' => ['Sering bersendawa', 'Umum'],
        'G09' => ['Nyeri ulu hati muncul saat perut kosong', 'Pola Nyeri'],
        'G10' => ['Nyeri ulu hati muncul 1-3 jam setelah makan', 'Pola Nyeri'],
        'G11' => ['Nyeri ulu hati berkurang setelah makan', 'Pola Nyeri'],
        'G12' => ['Nyeri ulu hati berkurang setelah minum obat antasida', 'Pola Nyeri'],
        'G13' => ['Nyeri ulu hati memburuk setelah makan', 'Pola Nyeri'],
        'G14' => ['Nyeri ulu hati muncul atau memberat pada malam hari', 'Pola Nyeri'],
        'G15' => ['Nyeri ulu hati terasa panas atau terbakar', 'Pola Nyeri'],
        'G16' => ['Rasa panas atau terbakar di dada', 'Umum'],
        'G17' => ['Rasa asam atau pahit naik ke kerongkongan', 'Refluks'],
        'G18' => ['Mulut terasa asam atau pahit', 'Refluks'],
        'G19' => ['Gejala memburuk saat berbaring atau membungkuk', 'Refluks'],
        'G20' => ['Sulit atau nyeri saat menelan', 'Refluks'],
        'G21' => ['Batuk kering atau suara serak tanpa sebab jelas', 'Refluks'],
        'G22' => ['BAB berwarna hitam seperti aspal', 'Tanda Bahaya'],
        'G23' => ['Muntah darah atau cairan seperti kopi', 'Tanda Bahaya'],
        'G24' => ['Penurunan berat badan tanpa sebab jelas', 'Tanda Bahaya'],
        'G25' => ['Nyeri perut hebat yang menjalar ke punggung', 'Tanda Bahaya'],
    ];

    /**
     * Daftar penyakit: kode => [nama, ringkasan, deskripsi, pengobatan, pencegahan].
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: string, 4: string}>
     */
    private const DISEASES = [
        'P01' => [
            'Gastritis',
            'Peradangan pada lapisan dinding lambung, bisa mendadak (akut) atau menahun '
            .'(kronis).',
            'Gastritis adalah peradangan pada lapisan mukosa dinding lambung. Pada bentuk '
            .'akut, keluhan muncul mendadak setelah makan makanan yang mengiritasi, meminum '
            .'alkohol, atau memakai obat antiradang golongan NSAID. Pada bentuk kronis, '
            .'keluhan datang berulang dalam waktu lama dan sering dipicu pola makan yang '
            .'tidak teratur serta infeksi Helicobacter pylori. Gejala utamanya nyeri atau '
            .'perih di ulu hati, disertai mual, muntah, dan berkurangnya nafsu makan.',
            'Pengobatan bertujuan mengurangi peradangan dan meredakan keluhan, umumnya '
            .'memakai obat penekan asam lambung serta obat pelindung mukosa. Bila ditemukan '
            .'infeksi Helicobacter pylori, dokter akan memberikan terapi antibiotik '
            .'kombinasi. Jangan memakai obat pereda nyeri golongan NSAID tanpa anjuran '
            .'dokter karena dapat memperberat peradangan.',
            'Hindari makanan pedas, asam, dan berlemak; batasi kopi dan alkohol; berhenti '
            .'merokok; makan dalam porsi kecil tetapi lebih sering dan teratur; kelola stres '
            .'dengan cukup istirahat; konsultasikan ke dokter bila keluhan berulang lebih '
            .'dari dua minggu.',
        ],
        'P02' => [
            'Dispepsia (Maag)',
            'Kumpulan keluhan tidak nyaman di perut bagian atas yang sehari-hari disebut '
            .'"maag", sering tanpa kelainan organik yang jelas.',
            'Dispepsia adalah kumpulan keluhan pada perut bagian atas yang terasa tidak '
            .'nyaman, sering disebut "maag" oleh masyarakat. Bentuk fungsional paling umum '
            .'dan tidak menunjukkan kelainan organik pada pemeriksaan, sedangkan bentuk '
            .'organik berkaitan dengan gangguan lain seperti tukak atau radang lambung. '
            .'Keluhan khasnya rasa penuh setelah makan, cepat kenyang, kembung, sering '
            .'bersendawa, dan nyeri ulu hati yang tidak khas.',
            'Penanganan bertumpu pada perbaikan pola makan dan gaya hidup. Dokter dapat '
            .'menambahkan obat penekan asam lambung, obat pemicu gerak saluran cerna, atau '
            .'obat penenang untuk meredakan keluhan. Bila keluhan menetap atau disertai '
            .'penurunan berat badan, diperlukan pemeriksaan lanjutan untuk menyingkirkan '
            .'penyebab organik.',
            'Makan teratur dalam porsi kecil dan jangan melewatkan waktu makan; kurangi '
            .'makanan berlemak, pedas, dan asam; batasi kafein dan rokok; kelola stres dan '
            .'tidur yang cukup; jangan langsung berbaring setelah makan.',
        ],
        'P03' => [
            'Tukak Lambung (Ulkus Peptikum)',
            'Luka terbuka pada lapisan mukosa lambung atau usus dua belas jari; nyeri ulu '
            .'hati khas muncul saat perut kosong dan mereda setelah makan.',
            'Tukak lambung atau ulkus peptikum adalah luka terbuka pada lapisan mukosa '
            .'lambung maupun usus dua belas jari. Penyebab utamanya infeksi Helicobacter '
            .'pylori dan pemakaian obat antiradang golongan NSAID dalam jangka panjang. '
            .'Ciri khasnya nyeri ulu hati yang muncul saat perut kosong atau pada malam '
            .'hari, dan justru mereda setelah makan atau meminum obat antasida. Bila luka '
            .'mendalam dan menembus pembuluh darah, dapat terjadi perdarahan yang ditandai '
            .'BAB hitam atau muntah darah.',
            'Pengobatan memerlukan obat penekan asam lambung dalam jangka waktu tertentu, '
            .'dan bila terbukti ada infeksi Helicobacter pylori dilanjutkan terapi '
            .'eradikasi berupa kombinasi antibiotik. Obat antiradang golongan NSAID harus '
            .'dihentikan atau diganti. Pendarahan saluran cerna adalah kondisi gawat darurat '
            .'yang memerlukan penanganan rumah sakit segera.',
            'Hindari obat antiradang golongan NSAID dan alkohol; berhenti merokok; makan '
            .'secara teratur; waspadai tanda perdarahan berupa BAB hitam atau muntah darah '
            .'dan segera ke rumah sakit bila terjadi; lakukan kontrol rutin sesuai anjuran '
            .'dokter.',
        ],
        'P04' => [
            'GERD (Refluks Gastroesofageal)',
            'Naiknya asam lambung ke kerongkongan yang menimbulkan rasa terbakar di dada '
            .'dan rasa asam di mulut, khas memberat saat berbaring.',
            'GERD adalah kondisi naiknya isi asam lambung ke kerongkongan sehingga '
            .'menimbulkan rasa terbakar di dada (heartburn) dan rasa asam atau pahit di '
            .'mulut. Keluhan khas muncul setelah makan porsi besar, lalu memberat saat '
            .'berbaring atau membungkuk. Rasa nyeri dapat menjalar menyerupai nyeri jantung, '
            .'dan refluks jangka panjang dapat memicu iritasi kerongkongan, sulit menelan, '
            .'serta batuk atau suara serak yang tidak jelas sebabnya.',
            'Pengobatan utama memakai obat penekan asam lambung, ditambah antasida untuk '
            .'meredakan keluhan secara cepat. Dokter dapat menambahkan obat penguat otot '
            .'katup kerongkongan bagian bawah. Bila keluhan berlanjut, diperlukan '
            .'pemeriksaan endoskopi untuk menilai tingkat kerusakan kerongkongan.',
            'Makan dalam porsi kecil dan jangan berbaring minimal 3 jam setelah makan; '
            .'hindari makanan pemicu seperti pedas, asam, berlemak, cokelat, dan kafein; '
            .'tinggikan kepala tempat tidur sekitar 15 cm; turunkan berat badan bila '
            .'berlebih; hindari pakaian ketat di perut; berhenti merokok.',
        ],
    ];

    /**
     * Bobot premis aturan: kode penyakit => [kode gejala => bobot].
     *
     * Total bobot per penyakit: P01 = 23, P02 = 21, P03 = 26, P04 = 27.
     *
     * @var array<string, array<string, int>>
     */
    private const RULES = [
        'P01' => [
            'G01' => 5,
            'G02' => 3,
            'G04' => 3,
            'G05' => 4,
            'G06' => 3,
            'G07' => 3,
            'G13' => 2,
        ],
        'P02' => [
            'G01' => 3,
            'G02' => 5,
            'G03' => 5,
            'G04' => 4,
            'G08' => 4,
        ],
        'P03' => [
            'G01' => 4,
            'G09' => 5,
            'G10' => 4,
            'G11' => 5,
            'G12' => 4,
            'G14' => 4,
        ],
        'P04' => [
            'G15' => 3,
            'G16' => 5,
            'G17' => 5,
            'G18' => 4,
            'G19' => 4,
            'G20' => 3,
            'G21' => 3,
        ],
    ];

    public function run(): void
    {
        foreach (self::SYMPTOMS as $code => [$name, $category]) {
            Symptom::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'category' => $category],
            );
        }

        foreach (self::DISEASES as $code => [$name, $summary, $description, $treatment, $prevention]) {
            Disease::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'summary' => $summary,
                    'description' => $description,
                    'treatment' => $treatment,
                    'prevention' => $prevention,
                ],
            );
        }

        $diseaseIds = Disease::pluck('id', 'code');
        $symptomIds = Symptom::pluck('id', 'code');

        foreach (self::RULES as $diseaseCode => $premises) {
            foreach ($premises as $symptomCode => $weight) {
                Rule::updateOrCreate(
                    [
                        'disease_id' => $diseaseIds[$diseaseCode],
                        'symptom_id' => $symptomIds[$symptomCode],
                    ],
                    ['weight' => $weight],
                );
            }
        }
    }
}
