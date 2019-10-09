<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as F;
use ILIAS\UI\Implementation\Component\Input;

class Factory implements F\Factory
{
    /**
     * @var Input\Field\Factory
     */
    protected $field_factory;

    public function __construct(
        Input\Field\Factory $field_factory
    ) {
        $this->field_factory = $field_factory;
    }

    /**
     * @inheritdoc
     */
    public function standard($post_url, array $inputs)
    {
        return new Standard($this->field_factory, $post_url, $inputs);
    }
}
