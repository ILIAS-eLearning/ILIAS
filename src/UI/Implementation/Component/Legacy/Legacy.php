<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\NotImplementedException;

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

    /**
     * @inheritdoc
     */
    public function withCustomSignal(string $signal_name, string $js_code) : \ILIAS\UI\Component\Legacy\Legacy
    {
        throw new NotImplementedException("withCustomSignal is not implemented yet");
    }

    /**
     * @inheritdoc
     */
    public function getCustomSignal(string $signal_name) : Signal
    {
        throw new NotImplementedException("getCustomSignal is not implemented yet");
    }
}
