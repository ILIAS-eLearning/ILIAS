<?php

/* Copyright (c) 2021 Adrian Lüthi <adi.l@bluewin.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Markdown;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Class Image
 * @package ILIAS\UI\Implementation\Component\Markup
 */
class Markup implements C\Markup\Markup
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var string
     */
    private $content;

    /**
     * @inheritdoc
     */
    public function __construct($content)
    {
        $this->checkStringArg("content", $content);

        $this->content = $content;

        $this->on_load_code_binder = function($id) {
            return "il.UI.markup.initiateMarkup($id);";
        };
    }

    /**
     * @inheritdoc
     */
    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function withContent($content)
    {
        $this->checkStringArg("content", $content);

        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }
}
