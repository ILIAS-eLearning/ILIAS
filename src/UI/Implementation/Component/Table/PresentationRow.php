<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class PresentationRow implements T\PresentationRow
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var Signal
     */
    protected $show_signal;

    /**
     * @var Signal
     */
    protected $close_signal;

    /**
     * @var Signal
     */
    protected $toggle_signal;

    /**
     * @var	string|null
     */
    private $headline;

    /**
     * @var	string|null
     */
    private $subheadline;

    /**
     * @var	ILIAS\UI\Component\Button\Button|ILIAS\UI\Component\Dropdown\Dropdown|null
     */
    private $action;

    /**
     * @var	array
     */
    private $important_fields;

    /**
     * @var
     */
    private $content;

    /**
     * @var	string
     */
    private $further_fields_headline;

    /**
     * @var	array
     */
    private $further_fields;

    /**
     * @var	array
     */
    private $data;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->actions = null;
        $this->initSignals();
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
     * Set the signals for this component.
     */
    protected function initSignals()
    {
        $this->show_signal = $this->signal_generator->create();
        $this->close_signal = $this->signal_generator->create();
        $this->toggle_signal = $this->signal_generator->create();
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
    public function getCloseSignal()
    {
        return $this->close_signal;
    }


    /**
     * @inheritdoc
     */
    public function getToggleSignal()
    {
        return $this->toggle_signal;
    }


    /**
     * @inheritdoc
     */
    public function withHeadline($headline)
    {
        $this->checkStringArg("string", $headline);
        $clone = clone $this;
        $clone->headline = $headline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * @inheritdoc
     */
    public function withSubheadline($subheadline)
    {
        $this->checkStringArg("string", $subheadline);
        $clone = clone $this;
        $clone->subheadline = $subheadline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSubheadline()
    {
        return $this->subheadline;
    }

    /**
     * @inheritdoc
     */
    public function withImportantFields(array $fields)
    {
        $clone = clone $this;
        $clone->important_fields = $fields;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getImportantFields()
    {
        return $this->important_fields;
    }


    /**
     * @inheritdoc
     */
    public function withContent(\ILIAS\UI\Component\Listing\Descriptive $content)
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
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
    public function withFurtherFieldsHeadline($headline)
    {
        $this->checkStringArg("string", $headline);
        $clone = clone $this;
        $clone->further_fields_headline = $headline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFurtherFieldsHeadline()
    {
        return $this->further_fields_headline;
    }

    /**
     * @inheritdoc
     */
    public function withFurtherFields(array $fields)
    {
        $clone = clone $this;
        $clone->further_fields = $fields;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFurtherFields()
    {
        return $this->further_fields;
    }


    /**
     * @inheritdoc
     */
    public function withAction($action)
    {
        $check =
            is_null($action)
            || $action instanceof \ILIAS\UI\Component\Button\Button
            || $action instanceof \ILIAS\UI\Component\Dropdown\Dropdown;

        $expected =
            " NULL or " .
            " \ILIAS\UI\Component\Button\Button or " .
            " \ILIAS\UI\Component\ropdown\Dropdown";

        $this->checkArg("action", $check, $this->wrongTypeMessage($expected, $action));
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->action;
    }
}
