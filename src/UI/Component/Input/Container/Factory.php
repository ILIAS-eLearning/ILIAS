<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container;

/**
 * This is how a factory for inputs looks like.
 */
interface Factory {
    /**
     * ---
     * description:
     *   purpose: >
     *      TBD
     *   composition: >
     *      TBD
     *   effect: >
     *      TBD
     *
     * rules:
     *   wording:
     *     1: TBD
     *
     * ---
     * @return  \ILIAS\UI\Component\Input\Container\Form\Factory
     */
    public function form();
}
