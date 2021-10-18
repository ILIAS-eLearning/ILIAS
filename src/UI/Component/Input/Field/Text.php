<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes text inputs.
 */
interface Text extends FilterInput
{
    /**
     * Defines the Max Length of text that can be entered in the text input
     */
    public function withMaxLength(int $max_length);

    /**
     * Gets the max length of the text input
     */
    public function getMaxLength() : ?int;
}
