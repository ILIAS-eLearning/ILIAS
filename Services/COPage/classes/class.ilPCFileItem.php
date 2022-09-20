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
 * Class ilPCFileItem
 *
 * File Item content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCFileItem extends ilPageContent
{
    public function init(): void
    {
        $this->setType("flit");
    }

    public function newItemAfter(
        int $a_id,
        string $a_location,
        string $a_format
    ): void {
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
    public function newItemBefore(
        int $a_id,
        string $a_location,
        string $a_format
    ): void {
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
    public function deleteItem(): void
    {
        $li = $this->getNode();
        $li->unlink($li);
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
