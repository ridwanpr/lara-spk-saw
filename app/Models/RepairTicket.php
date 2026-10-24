<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Scope a query to only include tickets visible to the given user.
     *
     * Staff users can only see their own tickets, while admin and inspector can see all tickets.
     *
     * @param  Builder<RepairTicket>  $query
     * @return Builder<RepairTicket>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isStaff()) {
            return $query->where('reporter_id', $user->id);
        }

        return $query;
    }

    /**
     * Scope a query to only include tickets visible to the given user (alias for visibleTo).
     *
     * @param  Builder<RepairTicket>  $query
     * @return Builder<RepairTicket>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $this->scopeVisibleTo($query, $user);
    }

    /**
     * Generate a unique sequential ticket number for the current month.
     * Format: TKT-YYYYMM-0001
     */
    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT-'.now()->format('Ym').'-';

        $latestTicket = self::where('ticket_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByRaw('LENGTH(ticket_number) DESC')
            ->orderByDesc('ticket_number')
            ->first();

        $sequence = 1;

        if ($latestTicket) {
            $lastSequence = (int) substr($latestTicket->ticket_number, strlen($prefix));
            $sequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
