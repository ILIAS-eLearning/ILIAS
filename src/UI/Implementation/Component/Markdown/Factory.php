<?php

/* Copyright (c) 2021 Adrian Lüthi <adi.l@bluewin.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Markdown;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Markup
 */
class Factory implements \ILIAS\UI\Component\Markup\Factory
{
    public function markup($content) {
        return new Markup($content);
    }
}
