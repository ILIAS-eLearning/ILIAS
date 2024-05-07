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

use ILIAS\AdvancedMetaData\Data\PersistenceTrackingData;
use ILIAS\AdvancedMetaData\Data\Exception;

interface Option extends PersistenceTrackingData
{
    public function optionID(): ?int;

    public function getPosition(): int;

    public function setPosition(int $position): void;

    /**
     * @return OptionTranslation[]
     */
    public function getTranslations(): \Generator;

    public function hasTranslationInLanguage(string $language): bool;

    public function getTranslationInLanguage(string $language): ?OptionTranslation;

    /**
     * Returns the new translation such that it can be configured.
     * @throws Exception if translation in this language already exists.
     */
    public function addTranslation(string $language): OptionTranslation;

    public function removeTranslation(string $language): void;
}
