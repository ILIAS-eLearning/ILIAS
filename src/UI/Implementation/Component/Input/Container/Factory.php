<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Component\Input as I;

class Factory implements I\Container\Factory
{
    protected Form\Factory $form_factory;
    protected Filter\Factory $filter_factory;
    protected ViewControl\Factory $view_control_factory;

    public function __construct(
        Form\Factory $form_factory,
        Filter\Factory $filter_factory,
        ViewControl\Factory $view_control_factory
    ) {
        $this->form_factory = $form_factory;
        $this->filter_factory = $filter_factory;
        $this->view_control_factory = $view_control_factory;
    }

    /**
     * @inheritdoc
     */
    public function form() : I\Container\Form\Factory
    {
        return $this->form_factory;
    }

    /**
     * @inheritdoc
     */
    public function filter() : I\Container\Filter\Factory
    {
        return $this->filter_factory;
    }

    /**
     * @inheritdoc
     */
    public function viewControl() : I\Container\ViewControl\Factory
    {
        return $this->view_control_factory;
    }
}
