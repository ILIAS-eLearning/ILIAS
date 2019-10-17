<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Component\Input as I;
use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Transformation;

class Factory implements I\Container\Factory
{
    /**
     * @var Form\Factory
     */
    protected $form_factory;

    public function __construct(
        Form\Factory $form_factory
    ) {
        $this->form_factory = $form_factory;
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        return $this->form_factory;
    }
}
