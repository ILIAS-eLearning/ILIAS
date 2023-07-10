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
        $li = $this->getDomNode();
        $new_item = $this->dom_doc->createElement("FileItem");
        if ($next_li = $li->nextSibling) {
            $new_item = $next_li->parentNode->insertBefore($new_item, $next_li);
        } else {
            $parent_list = $li->parentNode;
            $new_item = $parent_list->appendChild($new_item);
        }

        // Identifier
        $id_node = $this->dom_doc->createElement("Identifier");
        $id_node = $new_item->appendChild($id_node);
        $id_node->setAttribute("Catalog", "ILIAS");
        $id_node->setAttribute("Entry", "il__file_" . $a_id);

        // Location
        $loc_node = $this->dom_doc->createElement("Location");
        $loc_node = $new_item->appendChild($loc_node);
        $loc_node->setAttribute("Type", "LocalFile");
        $this->dom_util->setContent($loc_node, $a_location);

        // Format
        $form_node = $this->dom_doc->createElement("Format");
        $form_node = $new_item->appendChild($form_node);
        $this->dom_util->setContent($form_node, $a_format);
    }


    /**
     * insert new list item before current one
     */
    public function newItemBefore(
        int $a_id,
        string $a_location,
        string $a_format
    ): void {
        $li = $this->getDomNode();
        $new_item = $this->dom_doc->createElement("FileItem");
        $new_item = $li->parentNode->insertBefore($new_item, $li);

        // Identifier
        $id_node = $this->dom_doc->createElement("Identifier");
        $id_node = $new_item->appendChild($id_node);
        $id_node->setAttribute("Catalog", "ILIAS");
        $id_node->setAttribute("Entry", "il__file_" . $a_id);

        // Location
        $loc_node = $this->dom_doc->createElement("Location");
        $loc_node = $new_item->appendChild($loc_node);
        $loc_node->setAttribute("Type", "LocalFile");
        $this->dom_util->setContent($loc_node, $a_location);

        // Format
        $form_node = $this->dom_doc->createElement("Format");
        $form_node = $new_item->appendChild($form_node);
        $this->dom_util->setContent($form_node, $a_format);
    }

    /**
     * Delete file item
     */
    public function deleteItem(): void
    {
        $li = $this->getDomNode();
        $li->parentNode->removeChild($li);
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
