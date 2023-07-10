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

namespace ILIAS\UI\Component\Dropdown;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Link\Standard;

/**
 * This describes commonalities between all types of Dropdowns
 */
interface Dropdown extends Component, JavaScriptBindable, Clickable, Hoverable
{
    /**
     * Get the items of the Dropdown.
     * @return	array<Shy|Horizontal|Standard>
     */
    public function getItems(): array;

    /**
     * Get the label of the Dropdown.
     */
    public function getLabel(): ?string;

    /**
     * Get the aria-label of the Dropdown.
     */
    public function getAriaLabel(): ?string;

    /**
     * Get a Dropdown like this, but with an additional/replaced label.
     */
    public function withLabel(string $label): Dropdown;

    /**
     * Get a Dropdown like this, but with an additional/replaced aria-label.
     */
    public function withAriaLabel(string $label): Dropdown;
}
