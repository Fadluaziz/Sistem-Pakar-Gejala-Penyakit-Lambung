<?php

namespace App\Features\DiseaseDetection\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsultationSession extends Model
{
    public const STATUS_DIAGNOSED = 'diagnosed';
    public const STATUS_INCONCLUSIVE = 'inconclusive';
    public const STATUS_INSUFFICIENT = 'insufficient';

    public const VALIDATION_CORRECT = 'correct';
    public const VALIDATION_INCORRECT = 'incorrect';

    public const SOURCE_PATIENT = 'patient';
    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'user_id',
        'doctor_id',
        'status',
        'has_red_flag',
        'source',
        'validation',
        'doctor_note',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'has_red_flag' => 'boolean',
            'validated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function isValidated(): bool
    {
        return $this->validated_at !== null;
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'consultation_symptoms');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    /**
     * Label tanggal berbahasa Indonesia untuk daftar riwayat.
     */
    protected function createdAtLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->created_at?->locale('id')->isoFormat('D MMMM YYYY, HH:mm'),
        );
    }
}
