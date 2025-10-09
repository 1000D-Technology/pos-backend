<?php

namespace App\Enums;

enum PaymentType: string
{
    case REGULAR = 'regular';
    case ADVANCE = 'advance';
    case BONUS = 'bonus';
    case OVERTIME = 'overtime';
    case COMMISSION = 'commission';
    case ALLOWANCE = 'allowance';
    case ADJUSTMENT = 'adjustment';

    /**
     * Get all payment type values
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get payment type label
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular Payment',
            self::ADVANCE => 'Advance Payment',
            self::BONUS => 'Bonus Payment',
            self::OVERTIME => 'Overtime Payment',
            self::COMMISSION => 'Commission Payment',
            self::ALLOWANCE => 'Allowance Payment',
            self::ADJUSTMENT => 'Adjustment',
        };
    }
}
