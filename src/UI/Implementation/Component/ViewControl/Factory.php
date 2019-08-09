<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl as VC;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements VC\Factory
{
    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function mode($labelled_actions, $aria_label)
    {
        return new Mode($labelled_actions, $aria_label);
    }

    /**
     * @inheritdoc
     */
    public function section(Button $previous_action, \ILIAS\UI\Component\Component $button, Button $next_action)
    {
        return new Section($previous_action, $button, $next_action);
    }

    /**
     * @inheritdoc
     */
    public function sortation(array $options)
    {
        return new Sortation($options, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function pagination()
    {
        return new Pagination($this->signal_generator);
    }
}
