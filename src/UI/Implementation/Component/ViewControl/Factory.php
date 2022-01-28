<?php declare(strict_types=1);

/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl as VC;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Component;

class Factory implements VC\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function mode(array $labelled_actions, string $aria_label) : VC\Mode
    {
        return new Mode($labelled_actions, $aria_label);
    }

    /**
     * @inheritdoc
     */
    public function section(Button $previous_action, Component $button, Button $next_action) : VC\Section
    {
        return new Section($previous_action, $button, $next_action);
    }

    /**
     * @inheritdoc
     */
    public function sortation(array $options) : VC\Sortation
    {
        return new Sortation($options, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function pagination() : VC\Pagination
    {
        return new Pagination($this->signal_generator);
    }
}
