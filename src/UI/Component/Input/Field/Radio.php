<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This is what a radio-input looks like.
 */
interface Radio extends FormInput
{

    /**
     * Add an option-entry to the radio-input.
     */
    public function withOption(string $value, string $label, string $byline = null) : Radio;

    /**
     * Get all options as value=>label.
     *
     * @return array <string,string>
     */
    public function getOptions() : array;

    /**
     * Get byline for a single option.
     * Returns null, if none present.
     */
    public function getBylineFor(string $value) : ?string;
}
