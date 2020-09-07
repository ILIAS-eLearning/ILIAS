<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCList
*
* List content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCList extends ilPageContent
{
    public $list_node;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("list");
    }

    /**
    * Set pc node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->list_node = $a_node->first_child();		// this is the Table node
    }

    /**
    * Create new list
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->list_node = $this->dom->create_element("List");
        $this->list_node = $this->node->append_child($this->list_node);
    }

    /**
    * Add a number of items to list
    */
    public function addItems($a_nr)
    {
        for ($i = 1; $i <= $a_nr; $i++) {
            $new_item = $this->dom->create_element("ListItem");
            $new_item = $this->list_node->append_child($new_item);
        }
    }

    /**
    * Set order type
    */
    /*	function setOrderType($a_type = "Unordered")
        {
            switch ($a_type)
            {
                case "Unordered":
                    $this->list_node->set_attribute("Type", "Unordered");
                    if ($this->list_node->has_attribute("NumberingType"))
                    {
                        $this->list_node->remove_attribute("NumberingType");
                    }
                    break;

                case "Number":
                case "Roman":
                case "roman":
                case "Alphabetic":
                case "alphabetic":
                case "Decimal":
                    $this->list_node->set_attribute("Type", "Ordered");
                    $this->list_node->set_attribute("NumberingType", $a_type);
                    break;
            }
        }*/

    /**
    * Get order type
    */
    public function getOrderType()
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
                break;
                
            default:
                return "Number";
        }
    }

    /**
    * Get list type
    */
    public function getListType()
    {
        if ($this->list_node->get_attribute("Type") == "Unordered") {
            return "Unordered";
        }
        return "Ordered";
    }

    /**
    * Set list type
    *
    * @param	string		list type
    */
    public function setListType($a_val)
    {
        $this->list_node->set_attribute("Type", $a_val);
    }

    /**
    * Get numbering type
    */
    public function getNumberingType()
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
                break;
                
            default:
                return "Number";
        }
    }

    /**
    * Set numbering type
    *
    * @param	string	numbering type
    */
    public function setNumberingType($a_val)
    {
        if ($a_val != "") {
            $this->list_node->set_attribute("NumberingType", $a_val);
        } else {
            if ($this->list_node->has_attribute("NumberingType")) {
                $this->list_node->remove_attribute("NumberingType");
            }
        }
    }

    /**
    * Set start value
    *
    * @param	int		start value
    */
    public function setStartValue($a_val)
    {
        if ($a_val != "") {
            $this->list_node->set_attribute("StartValue", $a_val);
        } else {
            if ($this->list_node->has_attribute("StartValue")) {
                $this->list_node->remove_attribute("StartValue");
            }
        }
    }
    
    /**
    * Get start value
    *
    * @return	int		start value
    */
    public function getStartValue()
    {
        return $this->list_node->get_attribute("StartValue");
    }
    
    /**
    * Set style class
    *
    * @param	string		style class
    */
    public function setStyleClass($a_val)
    {
        if (!in_array($a_val, array("", "BulletedList", "NumberedList"))) {
            $this->list_node->set_attribute("Class", $a_val);
        } else {
            if ($this->list_node->has_attribute("Class")) {
                $this->list_node->remove_attribute("Class");
            }
        }
    }
    
    /**
    * Get style class
    *
    * @return	string		style class
    */
    public function getStyleClass()
    {
        return $this->list_node->get_attribute("Class");
    }
}
