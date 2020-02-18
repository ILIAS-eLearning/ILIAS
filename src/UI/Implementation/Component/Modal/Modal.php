<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Base class for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class Modal implements Component\Modal\Modal
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @var Signal
     */
    protected $show_signal;

    /**
     * @var Signal
     */
    protected $close_signal;
    /**
     * @var string
     */
    protected $async_render_url = '';

    /**
     * @var bool
     */
    protected $close_with_keyboard = true;

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
    public function getAsyncRenderUrl()
    {
        return $this->async_render_url;
    }

    /**
     * @inheritdoc
     */
    public function withAsyncRenderUrl($url)
    {
        $this->checkStringArg('url', $url);
        $clone = clone $this;
        $clone->async_render_url = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withCloseWithKeyboard($state)
    {
        $clone = clone $this;
        $clone->close_with_keyboard = (bool) $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCloseWithKeyboard()
    {
        return $this->close_with_keyboard;
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
    public function withResetSignals()
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnLoad(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'ready');
    }

    /**
     * @inheritdoc
     */
    public function appendOnLoad(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'ready');
    }


    /**
     * Set the show and close signals for this modal
     */
    protected function initSignals()
    {
        $this->show_signal = $this->signal_generator->create();
        $this->close_signal = $this->signal_generator->create();
    }
}
