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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * This describes the MetaBar.
 */
interface MetaBar extends Component, JavaScriptBindable
{
    /**
     * Append an entry.
     *
     * @param Button\Bulky|Slate $entry
     * @throws \InvalidArgumentException 	if $id is already taken
     */
    public function withAdditionalEntry(string $id, $entry) : MetaBar;

    /**
     * @return array <string, Bulky|Slate>
     */
    public function getEntries() : array;

    /**
     * The Signal is triggered when any Entry is being clicked.
     */
    public function getEntryClickSignal() : Signal;

    /**
     * This signal disengages all slates when triggered.
     */
    public function getDisengageAllSignal() : Signal;

    /**
     * Get a copy of this MetaBar without any entries.
     */
    public function withClearedEntries() : MetaBar;
}
