<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Signals that a necessary confirmation from the admin is missing.
 */
class NoConfirmationException extends NotExecutableException 
{
    /**
     * @var string
     */
    protected $confirmation;

    public function __construct(string $confirmation, ...$rest)
    {
        parent::__construct(...$rest);
        $this->confirmation = $confirmation;
    }

    public function getRequestedConfirmation()
    {
        return $this->confirmation;
    }
}
