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

namespace ILIAS\UI\Component\Popover;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\ReplaceContentSignal;

/**
 * Describes the Popover component
 */
interface Popover extends Component, Triggerable
{
    public const POS_AUTO = 'auto';
    public const POS_VERTICAL = 'vertical';
    public const POS_HORIZONTAL = 'horizontal';

    /**
     * Get the same popover displaying a title above the content.
     */
    public function withTitle(string $title): Popover;

    /**
     * Get the title of the popover.
     */
    public function getTitle(): string;

    /**
     * Get the same popover being rendered below or above the trigger, based on the available
     * space.
     */
    public function withVerticalPosition(): Popover;

    /**
     * Get the same popover being rendered to the left or right of the trigger, based on the
     * available space.
     */
    public function withHorizontalPosition(): Popover;

    /**
     * Get the position of the popover.
     */
    public function getPosition(): string;

    /**
     * Get a popover like this who's content is rendered via ajax by the given $url before the
     * popover is shown.
     *
     * Means: After the show signal has been triggered but before the popover is displayed to the
     * user, an ajax request is sent to this url. The request MUST return the rendered content for
     * the popover.
     */
    public function withAsyncContentUrl(string $url): Popover;

    /**
     * Get the url returning the rendered content, if the popovers content is rendered via ajax.
     */
    public function getAsyncContentUrl(): string;

    /**
     * Get the signal to show this popover in the frontend.
     */
    public function getShowSignal(): Signal;

    /**
     * Get the signal to replace the content of this popover.
     */
    public function getReplaceContentSignal(): ReplaceContentSignal;

    /**
     * Get a popover which can be used in fixed places such as the main menu.
     * This popover will stay fixed when scrolling and therefore remain on the screen.
     */
    public function withFixedPosition(): Popover;

    /**
     * @return bool whether it's fixed or not
     */
    public function isFixedPosition(): bool;
}
