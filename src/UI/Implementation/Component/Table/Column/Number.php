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
    protected int $decimals = 0;
    protected string $unit = '';
    protected mixed $unit_pos = self::UNIT_POSITION_AFT;

    protected $delim_decimal = ',';
    protected $delim_thousands = '';

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

    public function withUnit(string $unit, $unit_position = self::UNIT_POSITION_AFT): self
    {
        if (! in_array($unit_position, [
            self::UNIT_POSITION_FORE,
            self::UNIT_POSITION_AFT
        ])) {
            throw new \InvalidArgumentException('Use $unit_position = \'UNIT_POSITION_FORE\'/, \'UNIT_POSITION_AFT\'');
        }

        $clone = clone $this;
        $clone->unit = $unit;
        $clone->unit_position = $unit_position;
        return $clone;
    }

    public function format($value): string
    {
        $value = (string)number_format(
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
            $value = $value . ' ' . $this->unit;
        }
        return $value;
    }
}
