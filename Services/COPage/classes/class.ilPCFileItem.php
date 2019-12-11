<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCFileItem
*
* File Item content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileItem extends ilPageContent
{
    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("flit");
    }

    /**
    * insert new list item after current one
    */
    public function newItemAfter($a_id, $a_location, $a_format)
    {
        $li = $this->getNode();
        $new_item = $this->dom->create_element("FileItem");
        if ($next_li = $li->next_sibling()) {
            $new_item = $next_li->insert_before($new_item, $next_li);
        } else {
            $parent_list = $li->parent_node();
            $new_item = $parent_list->append_child($new_item);
        }

        // Identifier
        $id_node = $this->dom->create_element("Identifier");
        $id_node = $new_item->append_child($id_node);
        $id_node->set_attribute("Catalog", "ILIAS");
        $id_node->set_attribute("Entry", "il__file_" . $a_id);

        // Location
        $loc_node = $this->dom->create_element("Location");
        $loc_node = $new_item->append_child($loc_node);
        $loc_node->set_attribute("Type", "LocalFile");
        $loc_node->set_content($a_location);

        // Format
        $form_node = $this->dom->create_element("Format");
        $form_node = $new_item->append_child($form_node);
        $form_node->set_content($a_format);
    }


    /**
    * insert new list item before current one
    */
    public function newItemBefore($a_id, $a_location, $a_format)
    {
        $li = $this->getNode();
        $new_item = $this->dom->create_element("FileItem");
        $new_item = $li->insert_before($new_item, $li);

        // Identifier
        $id_node = $this->dom->create_element("Identifier");
        $id_node = $new_item->append_child($id_node);
        $id_node->set_attribute("Catalog", "ILIAS");
        $id_node->set_attribute("Entry", "il__file_" . $a_id);

        // Location
        $loc_node = $this->dom->create_element("Location");
        $loc_node = $new_item->append_child($loc_node);
        $loc_node->set_attribute("Type", "LocalFile");
        $loc_node->set_content($a_location);

        // Format
        $form_node = $this->dom->create_element("Format");
        $form_node = $new_item->append_child($form_node);
        $form_node->set_content($a_format);
    }

    /**
    * Delete file item
    */
    public function deleteItem()
    {
        $li = $this->getNode();
        $li->unlink($li);
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
