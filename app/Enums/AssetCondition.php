<?php

namespace App\Enums;

enum AssetCondition: string
{
    case Good = 'good';
    case Damaged = 'damaged';
    case UnderRepair = 'under_repair';

    /**
     * Get the human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Good => 'Baik',
            self::Damaged => 'Rusak',
            self::UnderRepair => 'Dalam Perbaikan',
        };
    }
}
