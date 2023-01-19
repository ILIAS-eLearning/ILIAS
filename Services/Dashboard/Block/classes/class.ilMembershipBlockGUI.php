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
        $groupedItems = $this->blockView->getItemGroups();
        $item_groups = [];
        $list_items = [];

        foreach ($groupedItems as $group) {
            $list_items = [];

            foreach ($group->getItems() as $item) {
                try {
                    $itemListGUI = $this->list_factory->byType($item['type']);
                    ilObjectActivation::addListGUIActivationProperty($itemListGUI, $item);

                    $list_items[] = [
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'ref_id' => $item['ref_id'],
                        'obj_id' => $item['obj_id'],
                        'url' => '',
                        'mem_obj' => $item,
                        'type' => $item['type'],
                    ];
                } catch (ilException $e) {
                    $this->logging->warning('Listing failed for item with ID ' . $item['obj_id'] . ': ' . $e->getMessage());
                    continue;
                }
            }
            if (count($list_items) > 0) {
                $item_groups[$group->getLabel()] = $list_items;
            }
        }

        $this->setData($item_groups);
    }

    public function getItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $itemListGui = $this->list_factory->byType($data['type']);
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
        $itemListGui = $this->list_factory->byType($data['type']);
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
