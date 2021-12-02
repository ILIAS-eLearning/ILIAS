<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input;

/**
 * This implements a standard form.
 */
class Standard extends Form implements C\Input\Container\Form\Standard
{
    protected ?string $submit_caption = null;
    protected string $post_url;

    public function __construct(
        Input\Field\Factory $input_factory,
        Input\NameSource $name_source,
        string $post_url,
        array $inputs
    ) {
        parent::__construct($input_factory, $name_source, $inputs);
        $this->post_url = $post_url;
    }

    /**
     * @inheritdoc
     */
    public function getPostURL() : string
    {
        return $this->post_url;
    }

    /**
     * @inheritDoc
     */
    public function withSubmitCaption(string $caption) : C\Input\Container\Form\Standard
    {
        $clone = clone $this;
        $clone->submit_caption = $caption;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSubmitCaption() : ?string
    {
        return $this->submit_caption;
    }
}
