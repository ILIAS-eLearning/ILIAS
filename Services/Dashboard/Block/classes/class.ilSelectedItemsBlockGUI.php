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
class ilSelectedItemsBlockGUI extends ilDashboardBlockGUI
{
    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_SELECTED_ITEMS
        );
    }

    public function emptyHandling(): string
    {
        return '';
    }

    public function initData(): void
    {
        $this->setData([]);
    }

    public function getItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        return null;
    }

    public function getCardForData(array $data): ?\ILIAS\UI\Component\Card\RepositoryObject
    {
        return null;
    }
}
