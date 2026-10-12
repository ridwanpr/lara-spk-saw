<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairTicket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'asset_id',
        'reporter_id',
        'damage_description',
        'damage_photo',
        'status',
        'verifier_id',
        'verified_at',
        'estimated_cost',
        'actual_cost',
        'completion_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'verified_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the asset associated with this repair ticket.
     *
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who reported this repair ticket.
     *
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user (inspector/admin) who verified this ticket.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    /**
     * Get the criteria evaluations for this repair ticket.
     *
     * @return HasMany<TicketEvaluation, $this>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(TicketEvaluation::class, 'ticket_id');
    }
}
