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
 * Thrown by a local write path of language entries (e.g. ilObjLanguageExt::importLanguageFile())
 * when values contain markup TranslationMarkupPolicy does not allow - before anything is written.
 * Carries the affected keys for the caller to report; the message does not contain the values.
 */
class ilLanguageInvalidMarkupException extends ilLanguageException
{
    /**
     * @param array<string, list<string>> $invalid_values key => violations, see
     *        \ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy::findInvalidValues()
     */
    public function __construct(private readonly array $invalid_values)
    {
        parent::__construct(sprintf(
            'Markup that is not allowed in language entries: %s',
            implode(', ', array_keys($invalid_values))
        ));
    }

    /**
     * @return array<string, list<string>>
     */
    public function getInvalidValues(): array
    {
        return $this->invalid_values;
    }
}
