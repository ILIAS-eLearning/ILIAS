<?php declare(strict_types=1);

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


class ilADTIntegerDefinition extends ilADTDefinition
{
    protected ?int $min_value;
    protected ?int $max_value;
    protected string $suffix = '';

    // properties

    public function handleNumber(int $a_value) : ?int
    {
        if (!is_numeric($a_value)) {
            $a_value = null;
        }
        if ($a_value !== null) {
            // round?
            $a_value = (int) $a_value;
        }
        return $a_value;
    }

    public function getMin() : ?int
    {
        return $this->min_value;
    }

    public function setMin(int $a_value) : void
    {
        $this->min_value = $this->handleNumber($a_value);
    }

    public function getMax() : ?int
    {
        return $this->max_value;
    }

    public function setMax(int $a_value) : void
    {
        $this->max_value = $this->handleNumber($a_value);
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $a_value) : void
    {
        $this->suffix = $a_value === null ? '' : trim($a_value);
    }

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be number-based
        return ($a_adt instanceof ilADTInteger);
    }
}
