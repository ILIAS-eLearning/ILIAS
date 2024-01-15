<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\HasHelpTopics;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * This describes commonalities between standard and primary buttons.
 */
interface Button extends Component, JavaScriptBindable, Clickable, Hoverable, Engageable, HasHelpTopics
{
    /**
     * Get the label on the button.
     */
    public function getLabel(): string|Glyph;

    /**
     * Get a button like this, but with an additional/replaced label.
     */
    public function withLabel(string|Glyph $label): Button;

    /**
     * Get the action of the button, i.e. an URL that the button links to or
     * some signals the button triggers on click.
     *
     * @return	string|(Signal[])
     */
    public function getAction();

    /**
     * Get to know if the button is activated.
     */
    public function isActive(): bool;

    /**
     * Get a button like this, but action should be unavailable atm.
     *
     * The button will still have an action afterwards, this might be usefull
     * at some point where we want to reactivate the button client side.
     */
    public function withUnavailableAction(bool $flag = true): Button;

    /**
     * Get a button like this, but with an additional/replaced aria-label.
     */
    public function withAriaLabel(string $aria_label): Button;

    /**
     * Get the aria-label on the button.
     */
    public function getAriaLabel(): string;
}
