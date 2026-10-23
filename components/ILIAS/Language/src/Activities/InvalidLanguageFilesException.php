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

namespace ILIAS\Language\Activities;

/**
 * A state of the server, not of the call: 409, so Failure::of() passes it on and the GUI can name the languages.
 */
class InvalidLanguageFilesException extends \RuntimeException
{
    /**
     * @param list<string> $language_keys
     */
    public function __construct(private readonly array $language_keys, ?\Throwable $previous = null)
    {
        parent::__construct('Invalid language files: ' . implode(', ', $language_keys), 409, $previous);
    }

    /**
     * @return list<string>
     */
    public function getLanguageKeys(): array
    {
        return $this->language_keys;
    }
}
