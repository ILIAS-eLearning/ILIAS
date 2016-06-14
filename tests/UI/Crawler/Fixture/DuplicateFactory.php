<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Interfaces;

use ILIAS\UI\Component as C;
/**
 * Some Random Comment
 */
interface ProperEntry {
    /**
     * ---
     * title: Duplicate Forever
     * description:
     * rules:
     * ---
     *
     * @return  tests\UI\Crawler\Fixture\Component2\LoopFactory
     */
    public function component1();

    /**
     * ---
     * title: Duplicate Forever
     * description:
     * rules:
     * ---
     *
     * @return  tests\UI\Crawler\Fixture\ComponentsTreeFixture\Component2\Factory
     */
    public function component2();
}