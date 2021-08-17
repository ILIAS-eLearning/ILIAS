<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Interfaces;

use ILIAS\UI\Component as C;

/**
 * Some Random Comment
 */
interface ProperEntry
{
    /**
     * ---
     * description:
     *   purpose:
     *   composition:
     *   effect:
     *
     * rules:
     *   usage:
     *   ordering:
     *   responsiveness:
     *   accessibility:
     * ---
     *
     * @  Missing Namespace
     */
    public function component1();

    /**
     * ---
     * description:
     *   purpose:
     *   composition:
     *   effect:
     *
     * rules:
     *   usage:
     *   ordering:
     *   responsiveness:
     *   accessibility:
     * ---
     *
     * @return  tests\UI\Crawler\Fixture\ComponentsTreeFixture\Component2\Factory
     */
    public function component2();
}
