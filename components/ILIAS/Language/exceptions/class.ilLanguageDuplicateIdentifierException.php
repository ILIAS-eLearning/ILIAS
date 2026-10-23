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

/**
 * Thrown by ilObjLanguageExt::importLanguageFile() before anything is written, when entries would duplicate an
 * identifier of another module or in another spelling (rule 2 of docs/development/language.md).
 */
class ilLanguageDuplicateIdentifierException extends ilLanguageException
{
    /**
     * @param array<string, list<string>> $duplicates key => "<module>: <identifier>" it would duplicate, see
     *        \ILIAS\Language\ComponentTranslation\IdentifierUniqueness::findDuplicatesAmong()
     */
    public function __construct(private readonly array $duplicates)
    {
        parent::__construct(sprintf(
            'Identifiers that already exist in another module or in another spelling: %s',
            implode(', ', array_keys($duplicates))
        ));
    }

    /**
     * @return array<string, list<string>>
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }
}
