<?php

namespace App\Enums;

enum CriteriaType: string
{
    case Benefit = 'benefit';
    case Cost = 'cost';

    /**
     * Get the human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Benefit => 'Benefit',
            self::Cost => 'Cost',
        };
    }
}
