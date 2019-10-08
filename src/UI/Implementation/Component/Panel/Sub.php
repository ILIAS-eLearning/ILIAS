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
}
