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
 * @author Tim Schmitz <schmitz@leifos.de>
 */
interface ilWebLinkRepository
{
    /**
     * Creates a new item, complete with parameters. New parameters cannot
     * be created on their own, but only by adding them as drafts to a
     * drafted item, and then updating or creating with it.
     */
    public function createItem(ilWebLinkDraftItem $item): ilWebLinkItem;

    public function createList(ilWebLinkDraftList $list): ilWebLinkList;

    public function createAllItemsInDraftContainer(ilWebLinkDraftItemsContainer $container): ilWebLinkItemsContainer;

    public function getAllItemsAsContainer(bool $only_active = false): ilWebLinkItemsContainer;

    public function getItemByLinkId(int $link_id): ilWebLinkItem;

    public function doesOnlyOneItemExist(bool $only_active = false): bool;

    public function getParameterinItemByParamId(
        ilWebLinkItem $item,
        int $param_id
    ): ilWebLinkParameter;

    public function getList(): ilWebLinkList;

    public function doesListExist(): bool;

    /**
     * Updates an item. New parameters added as drafts update the parameter
     * they replace, or else are created fresh. Current parameters of the
     * item not added to the draft are deleted.
     */
    public function updateItem(
        ilWebLinkItem $item,
        ilWebLinkDraftItem $drafted_item
    ): void;

    public function updateList(
        ilWebLinkList $list,
        ilWebLinkDraftList $drafted_list
    ): void;

    public function deleteAllItems(): void;

    public function deleteItemByLinkID(int $link_id): void;

    public function deleteParameterByLinkIdAndParamId(
        int $link_id,
        int $param_id
    ): void;

    public function deleteList(): void;
}
