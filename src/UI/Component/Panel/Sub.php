<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes a Sub Panel.
 */
interface Sub extends Panel
{
    /**
     * Sets the card to be displayed on the right of the Sub Panel
     * @param \ILIAS\UI\Component\Card\Card $card
     * @return Sub
     */
    public function withCard(\ILIAS\UI\Component\Card\Card $card);

    /**
     * Gets the card to be displayed on the right of the Sub Panel
     * @return \ILIAS\UI\Component\Card\Card | null
     */
    public function getCard();

    /**
     * Sets the Secondary Panel to be displayed on the right of the Sub Panel
     * @param \ILIAS\UI\Component\Panel\Secondary\Secondary $secondary
     * @return Sub
     */
    public function withSecondaryPanel(\ILIAS\UI\Component\Panel\Secondary\Secondary $secondary);

    /**
     * Gets the Secondary Panel to be displayed on the right of the Sub Panel
     * @return \ILIAS\UI\Component\Panel\Secondary\Secondary | null
     */
    public function getSecondaryPanel();
}
