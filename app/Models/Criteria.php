<?php

namespace App\Models;

use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criterias';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'weight',
        'input_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CriteriaType::class,
            'input_type' => CriteriaInputType::class,
            'weight' => 'decimal:2',
        ];
    }

    /**
     * Get the sub criteria options for this criteria.
     *
     * @return HasMany<SubCriteria, $this>
     */
    public function subCriterias(): HasMany
    {
        return $this->hasMany(SubCriteria::class, 'criteria_id');
    }

    /**
     * Get the evaluations recorded for this criteria.
     *
     * @return HasMany<TicketEvaluation, $this>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(TicketEvaluation::class, 'criteria_id');
    }
}
