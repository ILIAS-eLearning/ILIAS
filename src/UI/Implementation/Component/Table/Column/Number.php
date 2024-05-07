<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;

class Number extends Column implements C\Number
{
    protected string $unit_position = self::UNIT_POSITION_AFT;
    protected int $decimals = 0;
    protected string $unit = '';

    protected string $delim_decimal = ',';
    protected string $delim_thousands = '';

    private const POSSIBLE_UNIT_POSITIONS = [
        self::UNIT_POSITION_FORE,
        self::UNIT_POSITION_AFT
    ];

    public function withDecimals(int $number_of_decimals): self
    {
        $clone = clone $this;
        $clone->decimals = $number_of_decimals;
        return $clone;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function withUnit(string $unit, string $unit_position = self::UNIT_POSITION_AFT): self
    {
        $this->checkArgIsElement(
            'unit_position',
            $unit_position,
            self::POSSIBLE_UNIT_POSITIONS,
            'UNIT_POSITION_FORE | UNIT_POSITION_AFT'
        );

        $clone = clone $this;
        $clone->unit = $unit;
        $clone->unit_position = $unit_position;
        return $clone;
    }

    public function format($value): string
    {
        $value = number_format(
            $value,
            $this->decimals,
            $this->delim_decimal,
            $this->delim_thousands
        );

        if ($this->unit === '') {
            return $value;
        }

        if ($this->unit_position === self::UNIT_POSITION_FORE) {
            $value = $this->unit . ' ' . $value;
        }
        if ($this->unit_position === self::UNIT_POSITION_AFT) {
            return $value . ' ' . $this->unit;
        }
        return $value;
    }

    /**
     * @return string[]
     */
    public function getOrderingLabels(): array
    {
        return [
            $this->asc_label ?? $this->getTitle() . self::SEPERATOR . $this->lng->txt('order_option_numerical_ascending'),
            $this->desc_label ?? $this->getTitle() . self::SEPERATOR . $this->lng->txt('order_option_numerical_descending')
        ];
    }
}
