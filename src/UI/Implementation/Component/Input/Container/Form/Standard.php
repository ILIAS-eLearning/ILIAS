<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input;

/**
 * This implements a standard form.
 */
class Standard extends Form implements C\Input\Container\Form\Standard
{

    /**
     * @var string
     */
    protected $post_url;


    public function __construct(Input\Field\Factory $input_factory, $post_url, array $inputs)
    {
        parent::__construct($input_factory, $inputs);
        $this->checkStringArg("post_url", $post_url);
        $this->post_url = $post_url;
    }


    /**
     * @inheritdoc
     */
    public function getPostURL()
    {
        return $this->post_url;
    }
}
