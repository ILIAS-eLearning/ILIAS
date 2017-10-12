<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container;

/**
 * This is what a factory for input containers looks like.
 */
interface Factory {
    /**
     * ---
     * description:
     *   purpose: >
     *      Forms are used to have the user enter or modify data, validate the
     *      input and submit it to the system.
     *      Forms rather arrange their contents (i.e. fields) in a explanationary
     *      than space-saving way.
     *   composition: >
     *      Forms are composed of input fields, displaying their labels and bylines.
     *   rivals:
     *      filter: Filters are used to limit search results; they never modify content.
     *
     * ---
     * @return  \ILIAS\UI\Component\Input\Container\Form\Factory
     */
    public function form();
}
