<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MessageBox;

use ILIAS\UI\Component\Component;

/**
 * Interface Message Box
 */
interface MessageBox extends Component
{
    // Types of Message Boxes:
    const FAILURE = "failure";
    const SUCCESS = "success";
    const INFO = "info";
    const CONFIRMATION = "confirmation";

    /**
     * Get the type of the Message Box.
     *
     * @return	string
     */
    public function getType();

    /**
     * Get the message text of the Message Box.
     *
     * @return	string
     */
    public function getMessageText();

    /**
     * Get the buttons of the Message Box.
     *
     * @return array
     */
    public function getButtons() : array;

    /**
     * Get the links of the Message Box.
     *
     * @return array
     */
    public function getLinks() : array;

    /**
     * Get a Message Box like this, but with buttons.
     *
     * @param \ILIAS\UI\Component\Button\Standard[] $buttons
     * @return	MessageBox
     */
    public function withButtons(array $buttons);

    /**
     * Get a Message Box like this, but with links.
     *
     * @param \ILIAS\UI\Component\Link\Standard[] $links
     * @return	MessageBox
     */
    public function withLinks(array $links);
}
