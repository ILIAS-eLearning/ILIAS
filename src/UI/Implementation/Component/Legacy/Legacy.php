<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Legacy
 * @package ILIAS\UI\Implementation\Component\Legacy
 */
class Legacy implements C\Legacy\Legacy
{
    use ComponentHelper;

    /**
     * @var	string
     */
    private $content;


    /**
     * Legacy constructor.
     * @param string $content
     */
    public function __construct($content)
    {
        $this->checkStringArg("content", $content);

        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }
}
