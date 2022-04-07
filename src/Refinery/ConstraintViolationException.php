<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use UnexpectedValueException;

/***
 * Signals the violation of some constraint on a value in a way that can be subject
 * to i18n.
 * @author  Niels Theen <ntheen@databay.de>
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

    public function getTranslatedMessage(callable $txt) : string
    {
        return vsprintf($txt($this->languageId), $this->languageValues);
    }
}
