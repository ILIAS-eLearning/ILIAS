<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes a Sub Panel.
 */
interface Sub extends Panel
{
    /**
     * Sets the component to be displayed on the right of the Sub Panel
     * @param \ILIAS\UI\Component\Card\Card | \ILIAS\UI\Component\Panel\Secondary\Secondary $component
     * @return Sub
     */
    public function withFurtherInformation($component);

    /**
     * Gets the component to be displayed on the right of the Sub Panel
     * @return \ILIAS\UI\Component\Card\Card | \ILIAS\UI\Component\Panel\Secondary\Secondary | null
     */
    public function getFurtherInformation();
}
