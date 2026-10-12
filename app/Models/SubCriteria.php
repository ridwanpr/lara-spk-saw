<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCriteria extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sub_criterias';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'criteria_id',
        'description',
        'crisp_value',
        'min_value',
        'max_value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'crisp_value' => 'integer',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
        ];
    }

    /**
     * Get the criteria that owns this sub criteria.
     *
     * @return BelongsTo<Criteria, $this>
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }

    /**
     * Get evaluations referencing this sub criteria.
     *
     * @return HasMany<TicketEvaluation, $this>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(TicketEvaluation::class, 'sub_criteria_id');
    }
}
