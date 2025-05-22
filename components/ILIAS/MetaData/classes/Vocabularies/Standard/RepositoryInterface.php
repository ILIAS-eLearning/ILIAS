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

namespace ILIAS\MetaData\Vocabularies\Standard;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValueInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

interface RepositoryInterface
{
    public function deactivateVocabulary(SlotIdentifier $slot): void;

    public function activateVocabulary(SlotIdentifier $slot): void;

    public function isVocabularyActive(SlotIdentifier $slot): bool;

    public function getVocabulary(SlotIdentifier $slot): VocabularyInterface;

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabularies(SlotIdentifier ...$slots): \Generator;

    /**
     * @return VocabularyInterface[]
     */
    public function getActiveVocabularies(SlotIdentifier ...$slots): \Generator;

    /**
     * Values not from (active) standard vocabularies will not be returned at all.
     * @return LabelledValueInterface[]
     */
    public function getLabelsForValues(
        PresentationUtilities $presentation_utilities,
        SlotIdentifier $slot,
        bool $only_active,
        string ...$values
    ): \Generator;
}
