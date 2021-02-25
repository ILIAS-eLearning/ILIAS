<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Sub
 * @package ILIAS\UI\Implementation\Component\Sub
 */
class Sub extends Panel implements C\Panel\Sub
{
    use ComponentHelper;

    /**
     * Card to be displayed on the right of the Sub Panel
     * @var C\Card\Card
     */
    private $card = null;

    /**
     * Secondary panel to be displayed on the right of the Sub Panel
     * @var C\Panel\Secondary\Secondary
     */
    private $secondary = null;

    /**
     * @inheritdoc
     */
    public function withCard(C\Card\Card $card)
    {
        $clone = clone $this;
        $clone->card = $card;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @inheritdoc
     */
    public function withSecondaryPanel(C\Panel\Secondary\Secondary $secondary)
    {
        $clone = clone $this;
        $clone->secondary = $secondary;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSecondaryPanel()
    {
        return $this->secondary;
    }
}
