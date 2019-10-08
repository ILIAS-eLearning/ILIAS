<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use \ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Popover
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
abstract class Popover implements Component\Popover\Popover
{
    use ComponentHelper;
    use JavaScriptBindable;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $position = self::POS_AUTO;
    /**
     * @var string
     */
    protected $ajax_content_url = '';
    /**
     * @var Signal
     */
    protected $show_signal;
    /**
     * @var ReplaceContentSignal
     */
    protected $replace_content_signal;
    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;
    /**
     * @var bool
     */
    protected $fixed_position = false;


    /**
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }


    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }


    /**
     * @inheritdoc
     */
    public function getAsyncContentUrl()
    {
        return $this->ajax_content_url;
    }


    /**
     * @inheritdoc
     */
    public function withVerticalPosition()
    {
        $clone = clone $this;
        $clone->position = self::POS_VERTICAL;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withHorizontalPosition()
    {
        $clone = clone $this;
        $clone->position = self::POS_HORIZONTAL;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withAsyncContentUrl($url)
    {
        $this->checkStringArg('url', $url);
        $clone = clone $this;
        $clone->ajax_content_url = $url;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withTitle($title)
    {
        $this->checkStringArg('title', $title);
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withResetSignals()
    {
        $clone = clone $this;
        $clone->initSignals();

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getShowSignal()
    {
        return $this->show_signal;
    }


    /**
     * @inheritdoc
     */
    public function getReplaceContentSignal()
    {
        return $this->replace_content_signal;
    }


    /**
     * Init any signals of this component
     */
    protected function initSignals()
    {
        $this->show_signal = $this->signal_generator->create();
        $this->replace_content_signal = $this->signal_generator->create("ILIAS\\UI\\Implementation\\Component\\ReplaceContentSignal");
    }


    /**
     * @inheritdoc
     */
    public function withFixedPosition()
    {
        $this->fixed_position = true;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function isFixedPosition()
    {
        return $this->fixed_position;
    }
}
