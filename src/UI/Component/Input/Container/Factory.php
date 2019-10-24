<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container;

/**
 * This is what a factory for input containers looks like.
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *      Forms are used to let the user enter or modify data, check her inputs
     *      and submit them to the system.
     *      Forms arrange their contents (i.e. fields) in an explanatory rather
     *      than space-saving way.
     *   composition: >
     *      Forms are composed of input fields, displaying their labels and bylines.
     *   rivals:
     *      filter: >
     *          Filters are used to limit search results; they never modify data in
     *          the system.
     *
     *
     * ---
     * @return  \ILIAS\UI\Component\Input\Container\Form\Factory
     */
    public function form();
}
