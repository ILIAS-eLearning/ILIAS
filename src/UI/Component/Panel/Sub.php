<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Panel\Secondary\Secondary;

/**
 * This describes a Sub Panel.
 */
interface Sub extends Panel
{
    /**
     * Sets the component to be displayed on the right of the Sub Panel
     * @param Card|Secondary $component
     */
    public function withFurtherInformation($component) : Sub;

    /**
     * Gets the component to be displayed on the right of the Sub Panel
     * @return Card|Secondary|null
     */
    public function getFurtherInformation();
}
