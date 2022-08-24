<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Onloadable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Base class for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class Modal implements M\Modal
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    protected SignalGeneratorInterface $signal_generator;
    protected Signal $show_signal;
    protected Signal $close_signal;
    protected string $async_render_url = '';
    protected bool $close_with_keyboard = true;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function getAsyncRenderUrl(): string
    {
        return $this->async_render_url;
    }

    /**
     * @inheritdoc
     */
    public function withAsyncRenderUrl(string $url): M\Modal
    {
        $clone = clone $this;
        $clone->async_render_url = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withCloseWithKeyboard(bool $state): M\Modal
    {
        $clone = clone $this;
        $clone->close_with_keyboard = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCloseWithKeyboard(): bool
    {
        return $this->close_with_keyboard;
    }

    /**
     * @inheritdoc
     */
    public function getShowSignal(): Signal
    {
        return $this->show_signal;
    }

    /**
     * @inheritdoc
     */
    public function getCloseSignal(): Signal
    {
        return $this->close_signal;
    }

    /**
     * @inheritdoc
     */
    public function withResetSignals(): Modal
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnLoad(Signal $signal): Onloadable
    {
        return $this->withTriggeredSignal($signal, 'ready');
    }

    /**
     * @inheritdoc
     */
    public function appendOnLoad(Signal $signal): Onloadable
    {
        return $this->appendTriggeredSignal($signal, 'ready');
    }

    /**
     * Set the show and close signals for this modal
     */
    public function initSignals(): void
    {
        $this->show_signal = $this->signal_generator->create();
        $this->close_signal = $this->signal_generator->create();
    }
}
