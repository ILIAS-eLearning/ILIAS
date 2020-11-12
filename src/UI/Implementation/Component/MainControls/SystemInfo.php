<?php

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class SystemInfo
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SystemInfo implements MainControls\SystemInfo
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var string
     */
    protected $head_line;

    /**
     * @var string
     */
    protected $information_text;

    /**
     * @var URI|null
     */
    protected $dismiss_action = null;

    /**
     * @var string
     */
    protected $denotation = self::DENOTATION_NEUTRAL;

    /**
     * @var Signal
     */
    protected $close_signal;

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * SystemInfo constructor.
     * @param SignalGeneratorInterface $signal_generator
     * @param string                   $head_line
     * @param string                   $information_text
     */
    public function __construct(SignalGeneratorInterface $signal_generator, string $head_line, string $information_text)
    {
        $this->signal_generator = $signal_generator;
        $this->head_line = $head_line;
        $this->information_text = $information_text;
        $this->initSignals();
    }

    protected function initSignals() : void
    {
        $this->close_signal = $this->signal_generator->create();
    }

    public function getHeadLine() : string
    {
        return $this->head_line;
    }

    public function getInformationText() : string
    {
        return $this->information_text;
    }

    public function isDismissable() : bool
    {
        return !is_null($this->dismiss_action);
    }

    public function getDismissAction() : URI
    {
        return $this->dismiss_action;
    }

    public function withDismissAction(?URI $uri) : \ILIAS\UI\Component\MainControls\SystemInfo
    {
        $clone = clone $this;
        $clone->dismiss_action = $uri;
        return $clone;
    }

    public function withDenotation(string $denotation) : \ILIAS\UI\Component\MainControls\SystemInfo
    {
        if (
            $denotation !== MainControls\SystemInfo::DENOTATION_NEUTRAL
            && $denotation !== MainControls\SystemInfo::DENOTATION_IMPORTANT
            && $denotation !== MainControls\SystemInfo::DENOTATION_BREAKING
        ) {
            throw new \InvalidArgumentException("Unknown denotation '$denotation'");
        }

        $clone = clone $this;
        $clone->denotation = $denotation;
        return $clone;
    }

    public function getDenotation() : string
    {
        return $this->denotation;
    }

    /**
     * @return Signal
     */
    public function getCloseSignal() : Signal
    {
        return $this->close_signal;
    }

    /**
     * @inheritDoc
     */
    public function withResetSignals()
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }
}
