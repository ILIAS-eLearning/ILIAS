<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a property that may include a sub form
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSubEnabledFormPropertyGUI extends ilFormPropertyGUI
{
    protected $sub_items = array();
    
    /**
    * Add Subitem
    *
    * @param	object	$a_item		Item
    */
    public function addSubItem($a_item)
    {
        $a_item->setParent($this);
        $this->sub_items[] = $a_item;
    }

    /**
    * Get Subitems
    *
    * @return	array	Array of items
    */
    public function getSubItems()
    {
        return $this->sub_items;
    }
    
    /**
     * returns a flat array of possibly existing subitems recursively
     *
     * @return array
     */
    public function getSubInputItemsRecursive()
    {
        $subInputItems = array();
        
        foreach ($this->sub_items as $subItem) {
            if ($subItem->getType() == 'section_header') {
                continue;
            }
            
            $subInputItems[] = $subItem;
            
            if ($subItem instanceof ilSubEnabledFormPropertyGUI) {
                $subInputItems = array_merge($subInputItems, $subItem->getSubInputItemsRecursive());
            }
        }
        
        return $subInputItems;
    }

    /**
    * Check SubItems
    *
    * @return	boolean		Input ok, true/false
    */
    final public function checkSubItemsInput()
    {
        $ok = true;
        foreach ($this->getSubItems() as $item) {
            $item_ok = $item->checkInput();
            if (!$item_ok) {
                $ok = false;
            }
        }
        return $ok;
    }

    /**
    * Get sub form html
    *
    */
    final public function getSubForm()
    {
        // subitems
        $pf = null;
        if (count($this->getSubItems()) > 0) {
            $pf = new ilPropertyFormGUI();
            $pf->setMode("subform");
            $pf->setItems($this->getSubItems());
        }

        return $pf;
    }

    /**
    * Get item by post var
    *
    * @return	mixed	false or item object
    */
    public function getItemByPostVar($a_post_var)
    {
        if ($this->getPostVar() == $a_post_var) {
            return $this;
        }

        foreach ($this->getSubItems() as $item) {
            if ($item->getType() != "section_header") {
                $ret = $item->getItemByPostVar($a_post_var);
                if (is_object($ret)) {
                    return $ret;
                }
            }
        }
        
        return false;
    }
}
