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

/**
 * Base container class for Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
abstract class ilWebLinkBaseItemsContainer
{
    /**
     * @var ilWebLinkBaseItem[]
     */
    protected array $items;

    /**
     * @param ilWebLinkBaseItem[]   $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @return ilWebLinkBaseItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getFirstItem(): ?ilWebLinkBaseItem
    {
        return $this->items[0] ?? null;
    }
}
