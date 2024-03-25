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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\TypeSpecificData;

interface SelectSpecificData extends TypeSpecificData
{
    public function hasOptions(): bool;

    /**
     * Returns options in the order of their position.
     * @return Option[]
     */
    public function getOptions(): \Generator;

    public function getOption(int $option_id): ?Option;

    public function removeOption(int $option_id): void;

    /**
     * Returns the new option such that it can be configured.
     */
    public function addOption(): Option;
}
