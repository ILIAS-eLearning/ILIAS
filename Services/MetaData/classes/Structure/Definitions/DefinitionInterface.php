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

namespace ILIAS\MetaData\Structure\Definitions;

use ILIAS\MetaData\Elements\Data\Type;

interface DefinitionInterface
{
    /**
     * Name of the metadata element. Note that even for
     * unique elements, this is not an unambiguous identifier
     * of the element.
     */
    public function name(): string;

    /**
     * Unique elements can only occur once at their position
     * in the metadata set. Note that elements with the same
     * name can still appear at other positions.
     */
    public function unique(): bool;

    /**
     * Type of data this element can carry.
     */
    public function dataType(): Type;
}
