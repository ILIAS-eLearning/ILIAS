<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCTab
*
* Tab content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTab extends ilPageContent
{
    public $dom;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("tabstab");
    }

    /**
    * insert new tab item after current one
    */
    public function newItemAfter()
    {
        $tab = $this->getNode();
        $new_tab = $this->dom->create_element("Tab");
        if ($next_tab = $tab->next_sibling()) {
            $new_tab = $next_tab->insert_before($new_tab, $next_tab);
        } else {
            $parent_tabs = $tab->parent_node();
            $new_tab = $parent_tabs->append_child($new_tab);
        }
    }


    /**
    * insert new tab item before current one
    */
    public function newItemBefore()
    {
        $tab = $this->getNode();
        $new_tab = $this->dom->create_element("Tab");
        $new_tab = $tab->insert_before($new_tab, $tab);
    }


    /**
    * delete tab
    */
    public function deleteItem()
    {
        $tab = $this->getNode();
        $tab->unlink($tab);
    }

    /**
    * move tab item down
    */
    public function moveItemDown()
    {
        $tab = $this->getNode();
        $next = $tab->next_sibling();
        $next_copy = $next->clone_node(true);
        $next_copy = $tab->insert_before($next_copy, $tab);
        $next->unlink($next);
    }

    /**
    * move tab item up
    */
    public function moveItemUp()
    {
        $tab = $this->getNode();
        $prev = $tab->previous_sibling();
        $tab_copy = $tab->clone_node(true);
        $tab_copy = $prev->insert_before($tab_copy, $prev);
        $tab->unlink($tab);
    }
}
