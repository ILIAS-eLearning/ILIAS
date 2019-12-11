<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCListItem
*
* List Item content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCListItem extends ilPageContent
{
    public $dom;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("li");
    }

    /**
    * insert new list item after current one
    */
    public function newItemAfter()
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
    public function newItemBefore()
    {
        $li = $this->getNode();
        $new_li = $this->dom->create_element("ListItem");
        $new_li = $li->insert_before($new_li, $li);
    }


    /**
    * delete row of cell
    */
    public function deleteItem()
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
    public function moveItemDown()
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
    public function moveItemUp()
    {
        $li = $this->getNode();
        $prev = $li->previous_sibling();
        $li_copy = $li->clone_node(true);
        $li_copy = $prev->insert_before($li_copy, $prev);
        $li->unlink($li);
    }
}
