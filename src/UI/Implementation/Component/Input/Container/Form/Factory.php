<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as F;
use ILIAS\UI\Implementation\Component\Input;

class Factory implements F\Factory
{
    protected Input\Field\Factory $field_factory;
    protected Input\NameSource $name_source;

    public function __construct(Input\Field\Factory $field_factory, Input\NameSource $name_source)
    {
        $this->field_factory = $field_factory;
        $this->name_source = $name_source;
    }

    /**
     * @inheritdoc
     */
    public function standard(string $post_url, array $inputs) : F\Standard
    {
        return new Standard($this->field_factory, $this->name_source, $post_url, $inputs);
    }
}
