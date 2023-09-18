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

namespace ILIAS\MetaData\Vocabularies;

use ILIAS\MetaData\Vocabularies\Conditions\ConditionInterface;

interface VocabularyInterface
{
    public function source(): string;

    /**
     * @return string[]
     */
    public function values(): \Generator;

    /**
     * Some vocabularies are only available if a different
     * MD element has a certain value.
     */
    public function isConditional(): bool;

    /**
     * Contains the path to the element this vocabulary
     * is conditional on, and the value the element needs
     * to have.
     */
    public function condition(): ?ConditionInterface;
}
