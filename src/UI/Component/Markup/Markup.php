<?php

/* Copyright (c) 2021 Adrian Lüthi <adi.l@bluewin.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Markup;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a markup control
 *
 * Interface Markup
 * @package ILIAS\UI\Component\Markup
 */
interface Markup extends \ILIAS\UI\Component\Component, JavaScriptBindable
{
    /**
     * Sets content of control
     *
     * @param string $content
     * @return Markup
     */
    public function withContent(string $content);

    /**
     * @return string
     */
    public function getContent() : string;
}
