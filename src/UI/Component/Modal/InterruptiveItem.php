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

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Image\Image;

/**
 * Interface InterruptiveItem
 *
 * Represents an item to be displayed inside an interruptive modal
 */
interface InterruptiveItem
{
    /**
     * Return an ID of the item
     */
    public function getId(): string;

    /**
     * Get the title of the item
     */
    public function getTitle(): string;

    /**
     * Get the description of a title
     */
    public function getDescription(): string;

    /**
     * Get the icon of the item
     */
    public function getIcon(): ?Image;
}
