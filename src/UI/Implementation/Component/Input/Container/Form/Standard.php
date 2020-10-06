<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as C;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\Data\URI;

/**
 * This implements a standard form.
 */
class Standard extends Form implements C\Standard
{

    /**
     * @var string
     */
    protected $post_url;

    /**
     * @var URI
     */
    protected $cancel_url;

    /**
     * @var string
     */
    protected $label_submit = 'save';

    /**
     * @var bool
     */
    protected $bottom_buttons_only = false;

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

    /**
     * @inheritdoc
     */
    public function withCancelURL(URI $url) : C\Standard
    {
        $clone = clone $this;
        $clone->cancel_url = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCancelURL() : ?URI
    {
        return $this->cancel_url;
    }

    /**
     * @inheritdoc
     */
    public function withSubmitLabel(string $label) : C\Standard
    {
        $clone = clone $this;
        $clone->label_submit = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSubmitLabel() : string
    {
        return $this->label_submit;
    }

    /**
     * @inheritdoc
     */
    public function withBottomButtonsOnly(bool $flag = true) : C\Standard
    {
        $clone = clone $this;
        $clone->bottom_buttons_only = $flag;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function hasBottomButtonsOnly() : bool
    {
        return $this->bottom_buttons_only;
    }
}
