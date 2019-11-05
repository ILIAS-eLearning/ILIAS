<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

/***
 * Signals the violation of some constraint on a value in a way that can be subject
 * to i18n.
 */
class ConstraintViolationException extends \UnexpectedValueException
{
    /**
     * @var string
     */
    private $languageId;

    /**
     * @var array
     */
    private $languageValues;

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

    /**
     * @param callable $txt
     * @return string
     */
    public function getTranslatedMessage(callable $txt)
    {
        return vsprintf($txt($this->languageId), $this->languageValues);
    }
}
