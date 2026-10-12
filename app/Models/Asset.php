<?php

namespace App\Models;

use App\Enums\AssetCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'category_id',
        'room_id',
        'procurement_year',
        'condition',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'procurement_year' => 'integer',
            'condition' => AssetCondition::class,
        ];
    }

    /**
     * Get the category that this asset belongs to.
     *
     * @return BelongsTo<AssetCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get the room where this asset is located.
     *
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the repair tickets for this asset.
     *
     * @return HasMany<RepairTicket, $this>
     */
    public function repairTickets(): HasMany
    {
        return $this->hasMany(RepairTicket::class, 'asset_id');
    }
}
