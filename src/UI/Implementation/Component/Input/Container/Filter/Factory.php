<?php declare(strict_types=1);

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component\Input\Container\Filter as F;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements F\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected Field\Factory $field_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Field\Factory $field_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->field_factory = $field_factory;
    }

    /**
     * @inheritdoc
     */
    public function standard(
        $toggle_action_on,
        $toggle_action_off,
        $expand_action,
        $collapse_action,
        $apply_action,
        $reset_action,
        array $inputs,
        array $is_input_rendered,
        bool $is_activated = false,
        bool $is_expanded = false
    ) : F\Standard {
        return new Standard(
            $this->signal_generator,
            $this->field_factory,
            $toggle_action_on,
            $toggle_action_off,
            $expand_action,
            $collapse_action,
            $apply_action,
            $reset_action,
            $inputs,
            $is_input_rendered,
            $is_activated,
            $is_expanded
        );
    }
}
