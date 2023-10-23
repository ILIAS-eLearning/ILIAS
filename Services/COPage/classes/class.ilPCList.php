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
    public function init(): void
    {
        $this->setType("list");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "List");
    }

    /**
     * Add a number of items to list
     */
    public function addItems(int $a_nr): void
    {
        for ($i = 1; $i <= $a_nr; $i++) {
            $new_item = $this->dom_doc->createElement("ListItem");
            $new_item = $this->getChildNode()->appendChild($new_item);
        }
    }


    /**
     * Get order type
     */
    public function getOrderType(): string
    {
        if ($this->getChildNode()->getAttribute("Type") == "Unordered") {
            return "Unordered";
        }

        $nt = $this->getChildNode()->getAttribute("NumberingType");
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
        if ($this->getChildNode()->getAttribute("Type") == "Unordered") {
            return "Unordered";
        }
        return "Ordered";
    }

    public function setListType(string $a_val): void
    {
        $this->getChildNode()->setAttribute("Type", $a_val);
    }

    /**
     * Get numbering type
     */
    public function getNumberingType(): string
    {
        $nt = $this->getChildNode()->getAttribute("NumberingType");
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
            $this->getChildNode()->setAttribute("NumberingType", $a_val);
        } else {
            if ($this->getChildNode()->hasAttribute("NumberingType")) {
                $this->getChildNode()->removeAttribute("NumberingType");
            }
        }
    }

    public function setStartValue(int $a_val): void
    {
        if ($a_val != "") {
            $this->getChildNode()->setAttribute("StartValue", $a_val);
        } else {
            if ($this->getChildNode()->hasAttribute("StartValue")) {
                $this->getChildNode()->removeAttribute("StartValue");
            }
        }
    }

    public function getStartValue(): int
    {
        return (int) $this->getChildNode()->getAttribute("StartValue");
    }

    public function setStyleClass(string $a_val): void
    {
        if (!in_array($a_val, array("", "BulletedList", "NumberedList"))) {
            $this->getChildNode()->setAttribute("Class", $a_val);
        } else {
            if ($this->getChildNode()->hasAttribute("Class")) {
                $this->getChildNode()->removeAttribute("Class");
            }
        }
    }

    public function setItemStyleClass(string $a_val): void
    {
        if (!in_array($a_val, array("", "StandardListItem"))) {
            $this->list_node->set_attribute("ItemClass", $a_val);
        } else {
            if ($this->list_node->has_attribute("ItemClass")) {
                $this->list_node->remove_attribute("ItemClass");
            }
        }
    }

    public function getStyleClass(): string
    {
        return $this->getChildNode()->getAttribute("Class");
    }

    public function getItemStyleClass(): string
    {
        return $this->list_node->get_attribute("ItemClass");
    }
}
