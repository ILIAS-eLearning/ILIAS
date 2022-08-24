<?php

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
 * Class ilPCListItem
 * List Item content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCListItem extends ilPageContent
{
    public function init(): void
    {
        $this->setType("li");
    }

    /**
     * insert new list item after current one
     */
    public function newItemAfter(): void
    {
        $li = $this->getNode();
        $new_li = $this->dom->create_element("ListItem");
        if ($next_li = $li->next_sibling()) {
            $new_li = $next_li->insert_before($new_li, $next_li);
        } else {
            $parent_list = $li->parent_node();
            $new_li = $parent_list->append_child($new_li);
        }
    }


    /**
     * insert new list item before current one
     */
    public function newItemBefore(): void
    {
        $li = $this->getNode();
        $new_li = $this->dom->create_element("ListItem");
        $new_li = $li->insert_before($new_li, $li);
    }


    /**
     * delete row of cell
     */
    public function deleteItem(): void
    {
        $parent_node = $this->getNode()->parent_node();
        $cnt = count($parent_node->child_nodes());
        if ($cnt == 1) {
            // if list item is the last one -> delete whole list
            $grandma = $parent_node->parent_node();
            $grandma->unlink($grandma);
        } else {
            $li = $this->getNode();
            $li->unlink($li);
        }
    }

    /**
     * move list item down
     */
    public function moveItemDown(): void
    {
        $li = $this->getNode();
        $next = $li->next_sibling();
        $next_copy = $next->clone_node(true);
        $next_copy = $li->insert_before($next_copy, $li);
        $next->unlink($next);
    }

    /**
     * move list item up
     */
    public function moveItemUp(): void
    {
        $li = $this->getNode();
        $prev = $li->previous_sibling();
        $li_copy = $li->clone_node(true);
        $li_copy = $prev->insert_before($li_copy, $prev);
        $li->unlink($li);
    }
}
