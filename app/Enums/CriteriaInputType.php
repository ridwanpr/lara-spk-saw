<?php

namespace App\Enums;

enum CriteriaInputType: string
{
    case Categorical = 'categorical';
    case Range = 'range';

    /**
     * Get the human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Categorical => 'Kategorikal',
            self::Range => 'Rentang Nilai',
        };
    }
}
