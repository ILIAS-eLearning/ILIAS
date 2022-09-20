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
 * Class ilPCList
 *
 * List content object (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCList extends ilPageContent
{
    public php4DOMElement $list_node;

    public function init(): void
    {
        $this->setType("list");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->list_node = $a_node->first_child();		// this is the Table node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->list_node = $this->dom->create_element("List");
        $this->list_node = $this->node->append_child($this->list_node);
    }

    /**
     * Add a number of items to list
     */
    public function addItems(int $a_nr): void
    {
        for ($i = 1; $i <= $a_nr; $i++) {
            $new_item = $this->dom->create_element("ListItem");
            $new_item = $this->list_node->append_child($new_item);
        }
    }


    /**
     * Get order type
     */
    public function getOrderType(): string
    {
        if ($this->list_node->get_attribute("Type") == "Unordered") {
            return "Unordered";
        }

        $nt = $this->list_node->get_attribute("NumberingType");
        switch ($nt) {
            case "Number":
            case "Roman":
            case "roman":
            case "Alphabetic":
            case "alphabetic":
            case "Decimal":
                return $nt;

            default:
                return "Number";
        }
    }

    public function getListType(): string
    {
        if ($this->list_node->get_attribute("Type") == "Unordered") {
            return "Unordered";
        }
        return "Ordered";
    }

    public function setListType(string $a_val): void
    {
        $this->list_node->set_attribute("Type", $a_val);
    }

    /**
     * Get numbering type
     */
    public function getNumberingType(): string
    {
        $nt = $this->list_node->get_attribute("NumberingType");
        switch ($nt) {
            case "Number":
            case "Roman":
            case "roman":
            case "Alphabetic":
            case "alphabetic":
            case "Decimal":
                return $nt;

            default:
                return "Number";
        }
    }

    public function setNumberingType(string $a_val): void
    {
        if ($a_val != "") {
            $this->list_node->set_attribute("NumberingType", $a_val);
        } else {
            if ($this->list_node->has_attribute("NumberingType")) {
                $this->list_node->remove_attribute("NumberingType");
            }
        }
    }

    public function setStartValue(int $a_val): void
    {
        if ($a_val != "") {
            $this->list_node->set_attribute("StartValue", $a_val);
        } else {
            if ($this->list_node->has_attribute("StartValue")) {
                $this->list_node->remove_attribute("StartValue");
            }
        }
    }

    public function getStartValue(): int
    {
        return (int) $this->list_node->get_attribute("StartValue");
    }

    public function setStyleClass(string $a_val): void
    {
        if (!in_array($a_val, array("", "BulletedList", "NumberedList"))) {
            $this->list_node->set_attribute("Class", $a_val);
        } else {
            if ($this->list_node->has_attribute("Class")) {
                $this->list_node->remove_attribute("Class");
            }
        }
    }

    public function getStyleClass(): string
    {
        return $this->list_node->get_attribute("Class");
    }
}
