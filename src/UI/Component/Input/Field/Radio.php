<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This is what a radio-input looks like.
 */
interface Radio extends Input
{

    /**
     * Add an option-entry to the radio-input.
     *
     * @param 	string 	$value
     * @param 	string 	$label
     * @param 	string 	$byline | null
     * @param 	array<string,Input> | null	$dependant_fields 	a list of UI input-fields
     *
     * @return 	Radio
     */
    public function withOption(string $value, string $label, string $byline=null, $dependant_fields=null) : Radio;

    /**
     * Get all options as value=>label.
     *
     * @return array <string,string>
     */
    public function getOptions() : array;

    /**
     * Get byline for a single option.
     * Returns null, if none present.
     *
     * @param sring 	$value
     *
     * @return array|null
     */
    public function getBylineFor(string $value);

    /**
     * Get dependant fields for a single option.
     * Returns null, if none present.
     *
     * @param sring 	$value
     *
     * @return array|null
     */
    public function getDependantFieldsFor(string $value);
}
