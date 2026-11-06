<?php

namespace App\Features\DiseaseDetection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    protected $fillable = [
        'consultation_session_id',
        'disease_id',
        'confidence',
        'breakdown',
        'total_weight',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'breakdown' => 'array',
            'total_weight' => 'integer',
            'rank' => 'integer',
        ];
    }

    /**
     * Bobot yang terkumpul dari gejala pasien untuk penyakit ini.
     */
    public function matchedWeight(): int
    {
        return (int) array_sum(array_column($this->breakdown ?? [], 'weight'));
    }

    public function consultationSession(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class);
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }
}
