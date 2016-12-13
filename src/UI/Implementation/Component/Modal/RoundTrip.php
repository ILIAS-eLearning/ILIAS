<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Button;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTrip extends Modal implements Component\Modal\RoundTrip
{

    use ModalHelper;

    /**
     * @var Button\Button
     */
    protected $cancel_button;


    /**
     * @inheritdoc
     */
    public function __construct($title, Component\Component $content)
    {
        parent::__construct($title, $content);
        $this->cancel_button = $this->getCancelButton('Cancel');
        $this->buttons[] = $this->cancel_button;
    }


    /**
     * @inheritdoc
     */
    public function withButtons(array $buttons)
    {
        $classes = array(Button\Button::class);
        $this->checkArgListElements('buttons', $buttons, $classes);
        $clone = clone $this;
        $clone->buttons = array_merge($buttons, array($clone->cancel_button));

        return $clone;
    }

}
