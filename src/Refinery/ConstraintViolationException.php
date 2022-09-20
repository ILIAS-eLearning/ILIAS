<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery;

use UnexpectedValueException;

/***
 * Signals the violation of some constraint on a value in a way that can be subject
 * to i18n.
 */
class ConstraintViolationException extends UnexpectedValueException
{
    private string $languageId;
    private array $languageValues;

    /**
     * Construct a violation on a constraint.
     *
     * @param string $message - developer-readable message in english.
     * @param string $languageId - id of a human-readable string in the "violation" lng-module
     * @param mixed ...$languageValues - values to be substituted in the lng-variable
     */
    public function __construct(
        string $message,
        string $languageId,
        ...$languageValues
    ) {
        parent::__construct($message);

        $this->languageId = $languageId;
        $this->languageValues = $languageValues;
    }

    public function getTranslatedMessage(callable $txt): string
    {
        return vsprintf($txt($this->languageId), $this->languageValues);
    }
}
