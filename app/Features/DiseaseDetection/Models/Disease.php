<?php

namespace App\Features\DiseaseDetection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disease extends Model
{
    protected $fillable = [
        'code',
        'name',
        'summary',
        'description',
        'treatment',
        'prevention',
    ];

    /**
     * Premis aturan yang mengarah ke penyakit ini.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /**
     * Gejala yang menjadi premis penyakit ini, beserta bobotnya.
     */
    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'rules')->withPivot('weight');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }
}
