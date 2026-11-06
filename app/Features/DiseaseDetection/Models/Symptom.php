<?php

namespace App\Features\DiseaseDetection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Symptom extends Model
{
    /**
     * Kategori gejala — menentukan bagian konsultasi tempat gejala ditanyakan.
     * 'Tanda Bahaya' juga menjadi pemicu peringatan darurat.
     */
    public const CATEGORY_UMUM = 'Umum';
    public const CATEGORY_NYERI = 'Pola Nyeri';
    public const CATEGORY_REFLUKS = 'Refluks';
    public const CATEGORY_BAHAYA = 'Tanda Bahaya';

    /** @var array<int, string> */
    public const CATEGORIES = [
        self::CATEGORY_UMUM,
        self::CATEGORY_NYERI,
        self::CATEGORY_REFLUKS,
        self::CATEGORY_BAHAYA,
    ];

    protected $fillable = [
        'code',
        'name',
        'category',
    ];

    /**
     * Premis aturan yang memakai gejala ini.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /**
     * Penyakit yang menjadikan gejala ini sebagai premis.
     */
    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class, 'rules')->withPivot('weight');
    }

    /**
     * Sesi konsultasi yang menyertakan gejala ini.
     */
    public function consultationSessions(): BelongsToMany
    {
        return $this->belongsToMany(ConsultationSession::class, 'consultation_symptoms');
    }
}
