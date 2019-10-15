<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

use ILIAS\UI\Component;

class Factory implements Component\Input\Factory
{

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @var Field\Factory
     */
    protected $field_factory;

    /**
     * @var	Container\Factory
     */
    protected $container_factory;

    /**
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct(SignalGeneratorInterface $signal_generator, Field\Factory $field_factory, Container\Factory $container_factory)
    {
        $this->signal_generator = $signal_generator;
        $this->field_factory = $field_factory;
        $this->container_factory = $container_factory;
    }

    /**
     * @inheritdoc
     */
    public function field()
    {
        return $this->field_factory;
    }

    /**
     * @inheritdoc
     */
    public function container()
    {
        return $this->container_factory;
    }
}
