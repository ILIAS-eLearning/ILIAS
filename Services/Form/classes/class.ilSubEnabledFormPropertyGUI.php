<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a property that may include a sub form
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
