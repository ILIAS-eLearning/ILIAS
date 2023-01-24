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
class ilMembershipBlockGUI extends ilDashboardBlockGUI
{
    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_MY_MEMBERSHIPS
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        return $this->lng->txt("rep_mo_mem_dash");
    }

    public function initData(): void
    {
        $provider = new ilPDSelectedItemsBlockMembershipsProvider($this->user);
        $data = $provider->getItems();

        $this->setData(['' => $data]);
    }

    public function getItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $itemListGui = $this->byType($data['type']);
        ilObjectActivation::addListGUIActivationProperty($itemListGui, $data);

        $list_item = $itemListGui->getAsListItem(
            (int) $data['ref_id'],
            (int) $data['obj_id'],
            (string) $data['type'],
            (string) $data['title'],
            (string) $data['description']
        );

        return $list_item;
    }

    public function getCardForData(array $data): ?\ILIAS\UI\Component\Card\RepositoryObject
    {
        $itemListGui = $this->byType($data['type']);
        ilObjectActivation::addListGUIActivationProperty($itemListGui, $data);

        $card = $itemListGui->getAsCard(
            (int) $data['ref_id'],
            (int) $data['obj_id'],
            (string) $data['type'],
            (string) $data['title'],
            (string) $data['description']
        );

        return $card;
    }

    public function getBlockType(): string
    {
        return 'pdmem';
    }
}
