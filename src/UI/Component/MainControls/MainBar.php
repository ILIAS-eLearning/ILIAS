<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\MainControls\Slate;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Component;

/**
 * This describes the MainBar
 */
interface MainBar extends Component, JavaScriptBindable
{
    /**
     * Append an entry.
     *
     * @param Button\Bulky|Link\Bulky|Slate\Slate $entry
     * @throws \InvalidArgumentException 	if $id is already taken
     */
    public function withAdditionalEntry(string $id, $entry) : MainBar;

    /**
     * @return array <string, Button\Bulky|Link\Bulky|Slate>
     */
    public function getEntries() : array;

    /**
     * Append a tool-entry.
     * Define a tools-trigger via "withToolsButton" first.
     *
     * @throws \InvalidArgumentException 	if $id is already taken
     * @throws \LogicException 	if no tool-button was set
     */
    public function withAdditionalToolEntry(
        string $id,
        Slate\Slate $entry,
        bool $initially_hidden = false,
        Button\Close $close_button = null
    ) : MainBar;

    /**
     * @return array <string, Slate>
     */
    public function getToolEntries() : array;

    /**
     * @throws \InvalidArgumentException 	if $active is not an element-identifier in entries
     */
    public function withActive(string $active) : MainBar;

    public function getActive() : ?string;

    /**
     * Set button for the tools-trigger.
     */
    public function withToolsButton(Button\Bulky $button) : MainBar;

    /**
     * Returns the button of the tools-trigger.
     */
    public function getToolsButton() : Button\Bulky;

    /**
     * Get the signal that is triggered when any entry in the bar is clicked.
     */
    public function getEntryClickSignal() : Signal;

    /**
     * Get the signal that is triggered when any entry in the tools-button is clicked.
     */
    public function getToolsClickSignal() : Signal;

    /**
     * Get the signal that is used for removing a tool.
     */
    public function getToolsRemovalSignal() : Signal;

    /**
     * This signal disengages all slates when triggered.
     */
    public function getDisengageAllSignal() : Signal;

    /**
     * There are tools that are rendered invisible before first activation.
     * @return string[]
     */
    public function getInitiallyHiddenToolIds() : array;

    /**
     * Signal to engage a tool from outside the MainBar.
     */
    public function getEngageToolSignal(string $tool_id) : Signal;

    /**
     * Buttons to close tools; maybe configure with callback.
     * @return array <string, Button\Close>
     */
    public function getCloseButtons() : array;

    /**
     * Get a copy of this MainBar without any entries.
     */
    public function withClearedEntries() : MainBar;

    /**
     * Signal to toggle the tools-section.
     */
    public function getToggleToolsSignal() : Signal;
}
