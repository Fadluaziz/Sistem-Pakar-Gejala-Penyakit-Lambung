<?php

namespace App\Features\DiseaseDetection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu premis basis aturan: JIKA symptom_id muncul MAKA disease_id
 * mendapat tambahan bobot sebesar weight.
 */
class Rule extends Model
{
    protected $fillable = [
        'disease_id',
        'symptom_id',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function symptom(): BelongsTo
    {
        return $this->belongsTo(Symptom::class);
    }
}
