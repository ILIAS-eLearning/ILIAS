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

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Dropdown;

/**
 * This describes how a panel could be modified during construction of UI.
 */
interface Panel extends Component
{
    /**
     * Gets the title of the panel
     *
     * @return string $title Title of the Panel
     */
    public function getTitle(): string;

    /**
     * Gets the content to be displayed inside the panel
     * @return Component[]|Component
     */
    public function getContent();

    /**
     * Sets action Dropdown being displayed beside the title
     */
    public function withActions(Dropdown\Standard $actions): Panel;

    /**
     * Gets action Dropdown being displayed beside the title
     */
    public function getActions(): ?Dropdown\Standard;
}
