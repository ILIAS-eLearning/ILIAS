<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Describes a source for input names.
 */
interface NameSource
{
    /**
     * Generates a unique name on every call.
     */
    public function getNewName() : string;
}
