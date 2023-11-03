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

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Dropdown;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\ViewControl\HasViewControls;

/**
 * This describes a Secondary Panel.
 */
interface Secondary extends Component\Component, HasViewControls
{
    /**
     * Sets a Component being displayed below the content
     */
    public function withFooter(Shy $component): Secondary;

    /**
     * Gets the Component being displayed below the content
     */
    public function getFooter(): ?Shy;

    /**
     * Sets the action dropdown to be displayed on the right of the title
     */
    public function withActions(Dropdown\Standard $actions): Secondary;

    /**
     * Gets the action dropdown to be displayed on the right of the title
     */
    public function getActions(): ?Dropdown\Standard;
}
