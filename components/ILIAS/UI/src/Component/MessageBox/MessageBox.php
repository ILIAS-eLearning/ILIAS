<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getType(): string;

    /**
     * Get the message text of the Message Box.
     */
    public function getMessageText(): string;

    /**
     * Get the buttons of the Message Box.
     *
     * @return array
     */
    public function getButtons(): array;

    /**
     * Get the links of the Message Box.
     *
     * @return array
     */
    public function getLinks(): array;

    /**
     * Get a Message Box like this, but with buttons.
     *
     * @param \ILIAS\UI\Component\Button\Standard[] $buttons
     */
    public function withButtons(array $buttons): MessageBox;

    /**
     * Get a Message Box like this, but with links.
     *
     * @param \ILIAS\UI\Component\Link\Standard[] $links
     */
    public function withLinks(array $links): MessageBox;
}
