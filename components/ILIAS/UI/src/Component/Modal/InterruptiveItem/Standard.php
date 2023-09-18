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

namespace ILIAS\UI\Component\Modal\InterruptiveItem;

use ILIAS\UI\Component\Image\Image;

/**
 * Interface InterruptiveItem
 *
 * Represents an object to be displayed inside an interruptive modal
 */
interface Standard extends InterruptiveItem
{
    /**
     * Get the title of the object
     */
    public function getTitle(): string;

    /**
     * Get the description of the object
     */
    public function getDescription(): string;

    /**
     * Get the icon of the object
     */
    public function getIcon(): ?Image;
}
