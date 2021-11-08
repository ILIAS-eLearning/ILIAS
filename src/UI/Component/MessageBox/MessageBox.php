<?php declare(strict_types=1);

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MessageBox;

use ILIAS\UI\Component\Component;

/**
 * Interface Message Box
 */
interface MessageBox extends Component
{
    // Types of Message Boxes:
    public const FAILURE = "failure";
    public const SUCCESS = "success";
    public const INFO = "info";
    public const CONFIRMATION = "confirmation";

    /**
     * Get the type of the Message Box.
     */
    public function getType() : string;

    /**
     * Get the message text of the Message Box.
     */
    public function getMessageText() : string;

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
     */
    public function withButtons(array $buttons) : MessageBox;

    /**
     * Get a Message Box like this, but with links.
     *
     * @param \ILIAS\UI\Component\Link\Standard[] $links
     */
    public function withLinks(array $links) : MessageBox;
}
