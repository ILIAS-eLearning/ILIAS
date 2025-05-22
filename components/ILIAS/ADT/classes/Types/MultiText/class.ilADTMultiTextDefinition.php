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

class ilADTMultiTextDefinition extends ilADTDefinition
{
    protected ?int $max_length = null;
    protected ?int $max_size = null;

    // properties

    public function getMaxLength(): ?int
    {
        return $this->max_length;
    }

    public function setMaxLength(int $a_value): void
    {
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_length = $a_value;
    }

    public function getMaxSize(): ?int
    {
        return $this->max_size;
    }

    public function setMaxSize(int $a_value): void
    {
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_size = $a_value;
    }

    // comparison

    public function isComparableTo(ilADT $a_adt): bool
    {
        // has to be text-based
        return ($a_adt instanceof ilADTMultiText);
    }
}
