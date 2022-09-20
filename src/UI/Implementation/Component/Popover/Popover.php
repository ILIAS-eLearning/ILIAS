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

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Popover
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
abstract class Popover implements C\Popover\Popover
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected string $title = '';
    protected string $position = self::POS_AUTO;
    protected string $ajax_content_url = '';
    protected C\Signal $show_signal;
    protected C\ReplaceContentSignal $replace_content_signal;
    protected SignalGeneratorInterface $signal_generator;
    protected bool $fixed_position = false;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function getAsyncContentUrl(): string
    {
        return $this->ajax_content_url;
    }

    /**
     * @inheritdoc
     */
    public function withVerticalPosition(): C\Popover\Popover
    {
        $clone = clone $this;
        $clone->position = self::POS_VERTICAL;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withHorizontalPosition(): C\Popover\Popover
    {
        $clone = clone $this;
        $clone->position = self::POS_HORIZONTAL;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAsyncContentUrl(string $url): C\Popover\Popover
    {
        $clone = clone $this;
        $clone->ajax_content_url = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withTitle(string $title): C\Popover\Popover
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withResetSignals(): C\Popover\Popover
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getShowSignal(): C\Signal
    {
        return $this->show_signal;
    }

    /**
     * @inheritdoc
     */
    public function getReplaceContentSignal(): C\ReplaceContentSignal
    {
        return $this->replace_content_signal;
    }

    /**
     * Init any signals of this component
     */
    protected function initSignals()
    {
        $this->show_signal = $this->signal_generator->create();
        /** @var C\ReplaceContentSignal $signal */
        $signal = $this->signal_generator->create("ILIAS\\UI\\Implementation\\Component\\ReplaceContentSignal");
        $this->replace_content_signal = $signal;
    }

    /**
     * @inheritdoc
     */
    public function withFixedPosition(): C\Popover\Popover
    {
        $this->fixed_position = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isFixedPosition(): bool
    {
        return $this->fixed_position;
    }
}
