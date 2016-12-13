<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Button;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Interruptive extends Modal implements Component\Modal\Interruptive
{

    use ModalHelper;

    /**
     * @var Button\Primary
     */
    protected $cancel_button;

    /**
     * @var Button\Standard
     */
    protected $action_button;


    /**
     * @inheritdoc
     */
    public function __construct($title, Component\Component $content)
    {
        parent::__construct($title, $content);
        // An interruptive modal always has a primary cancel button on the right side
        $this->cancel_button = $this->getCancelButton('Cancel');
        $this->buttons[] = $this->cancel_button;
    }


    /**
     * @inheritdoc
     */
    public function withActionButton(Button\Button $button)
    {
        $this->checkArgInstanceOf('button', $button, Button\Button::class);
        $clone = clone $this;
        $clone->action_button = $button;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getActionButton()
    {
        return $this->action_button;
    }


    /**
     * @inheritdoc
     */
    public function getButtons()
    {
        if ($this->action_button) {
            return array($this->action_button, $this->cancel_button);
        }

        return parent::getButtons();
    }


}
