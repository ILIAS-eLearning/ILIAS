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

namespace ILIAS\MetaData\Elements\Data;

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

interface DataInterface
{
    /**
     * LOM data type
     */
    public function type(): Type;

    /**
     * Value of the data, in a format according to its type.
     */
    public function value(): string;

    /**
     * Vocabulary slot the data belongs to (important for
     * making vocab values/strings presentable).
     */
    public function vocabularySlot(): SlotIdentifier;
}
