<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEvaluation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_evaluations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'criteria_id',
        'sub_criteria_id',
        'score',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    /**
     * Get the repair ticket being evaluated.
     *
     * @return BelongsTo<RepairTicket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(RepairTicket::class, 'ticket_id');
    }

    /**
     * Get the criteria associated with this evaluation.
     *
     * @return BelongsTo<Criteria, $this>
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }

    /**
     * Get the sub criteria selected in this evaluation.
     *
     * @return BelongsTo<SubCriteria, $this>
     */
    public function subCriteria(): BelongsTo
    {
        return $this->belongsTo(SubCriteria::class, 'sub_criteria_id');
    }
}
