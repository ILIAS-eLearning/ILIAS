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
        $li = $this->getDomNode();
        $new_li = $this->dom_doc->createElement("ListItem");
        if ($next_li = $li->nextSibling) {
            $new_li = $next_li->parentNode->insertBefore($new_li, $next_li);
        } else {
            $parent_list = $li->parentNode;
            $new_li = $parent_list->appendChild($new_li);
        }
    }


    /**
     * insert new list item before current one
     */
    public function newItemBefore(): void
    {
        $li = $this->getDomNode();
        $new_li = $this->dom_doc->createElement("ListItem");
        $new_li = $li->parentNode->insertBefore($new_li, $li);
    }


    /**
     * delete row of cell
     */
    public function deleteItem(): void
    {
        $parent_node = $this->getDomNode()->parentNode;
        $cnt = count($parent_node->childNodes);
        if ($cnt == 1) {
            // if list item is the last one -> delete whole list
            $grandma = $parent_node->parentNode;
            $grandma->parentNode->removeChild($grandma);
        } else {
            $li = $this->getDomNode();
            $li->parentNode->removeChild($li);
        }
    }

    /**
     * move list item down
     */
    public function moveItemDown(): void
    {
        $li = $this->getDomNode();
        $next = $li->nextSibling;
        $next_copy = $next->cloneNode(true);
        $next_copy = $li->parentNode->insertBefore($next_copy, $li);
        $next->parentNode->removeChild($next);
    }

    /**
     * move list item up
     */
    public function moveItemUp(): void
    {
        $li = $this->getDomNode();
        $prev = $li->previousSibling;
        $li_copy = $li->cloneNode(true);
        $li_copy = $prev->parentNode->insertBefore($li_copy, $prev);
        $li->parentNode->removeChild($li);
    }
}
