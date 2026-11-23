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

    public function indentItem(): bool
    {
        $item = $this->getDomNode();
        if (!$item instanceof DOMElement) {
            return false;
        }

        $source_list = $item->parentNode;
        $previous_item = $item->previousElementSibling;
        if (!$source_list instanceof DOMElement || $source_list->nodeName !== "List"
            || $previous_item?->nodeName !== "ListItem") {
            return false;
        }

        $last_block = $previous_item->lastElementChild;
        $target_list = $last_block?->firstElementChild;
        if ($last_block?->nodeName !== "PageContent" || $target_list?->nodeName !== "List") {
            $target_list = $this->createNestedList($previous_item, $source_list);
        }

        $target_list->appendChild($item);
        return true;
    }

    public function outdentItem(): bool
    {
        $item = $this->getDomNode();
        $source_list = $item?->parentNode;
        $block = $source_list?->parentNode;
        $parent_item = $block?->parentNode;
        $target_list = $parent_item?->parentNode;
        if ($source_list?->nodeName !== "List" || $block?->nodeName !== "PageContent"
            || $parent_item?->nodeName !== "ListItem" || $target_list?->nodeName !== "List") {
            return false;
        }

        $xpath = new DOMXPath($this->dom_doc);
        $only_item = !$xpath->evaluate("boolean(../ListItem[2])", $item);
        if ($only_item && $xpath->evaluate("boolean(../Title | ../@Id | ../../BibItemIdentifier)", $item)) {
            return false;
        }

        $next_item = $item->nextElementSibling;
        $has_previous_item = $item->previousElementSibling?->nodeName === "ListItem";
        $target_list->insertBefore($item, $parent_item->nextSibling);
        if ($next_item !== null && !$has_previous_item) {
            // The source list now contains just the following siblings.
            $item->appendChild($block);
        } elseif ($next_item !== null) {
            // Keep following siblings one level deeper and in reading order.
            $following_list = $this->createNestedList($item, $source_list);
            while ($next_item !== null) {
                $following_item = $next_item;
                $next_item = $following_item->nextElementSibling;
                $following_list->appendChild($following_item);
            }
        } elseif ($only_item) {
            $parent_item->removeChild($block);
        }
        return true;
    }

    private function createNestedList(DOMElement $item, DOMElement $source_list): DOMElement
    {
        $block = $item->appendChild($this->createPageContentNode(false));
        $list = $block->appendChild($this->dom_doc->createElement("List"));
        foreach (["Type", "Class", "ItemClass", "NumberingType"] as $attribute) {
            if ($source_list->hasAttribute($attribute)) {
                $list->setAttribute($attribute, $source_list->getAttribute($attribute));
            }
        }
        return $list;
    }
}
