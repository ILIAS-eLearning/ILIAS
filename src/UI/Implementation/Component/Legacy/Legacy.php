<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
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
     * @var SignalGeneratorInterface
     */
    private $signal_generator;

    /**
     * @var array
     */
    private $signal_list;

    /**
     * Legacy constructor.
     * @param string $content
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct($content, SignalGeneratorInterface $signal_generator)
    {
        $this->checkStringArg("content", $content);

        $this->content = $content;
        $this->signal_generator = $signal_generator;
        $this->signal_list = array();
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
        $clone = clone $this;
        $clone->registerSignalAndCustomCode($signal_name, $js_code);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCustomSignal(string $signal_name) : Signal
    {
        if(!key_exists($signal_name, $this->signal_list))
        {
            throw new \InvalidArgumentException("Signal with name $signal_name is not registered");
        }

        return $this->signal_list[$signal_name]['signal'];
    }

    /**
     * @inheritdoc
     */
    public function getAllSignals() : array
    {
        return $this->signal_list;
    }

    private function registerSignalAndCustomCode(string $signal_name, string $js_code)
    {
        $this->signal_list[$signal_name] = array(
            'signal'   => $this->signal_generator->create(),
            'js_code' => $js_code
        );
    }
}
