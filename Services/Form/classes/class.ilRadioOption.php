<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents an option in a radio group
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilRadioOption
{
    protected $title;
    protected $value;
    protected $info;
    protected $sub_items = array();
    protected $disabled;
    
    public function __construct($a_title = "", $a_value = "", $a_info = "")
    {
        $this->setTitle($a_title);
        $this->setValue($a_value);
        $this->setInfo($a_info);
    }
    
    /**
    * Set Title.
    *
    * @param	string	$a_title	Title
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
    * Get Title.
    *
    * @return	string	Title
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Set Info.
    *
    * @param	string	$a_info	Info
    */
    public function setInfo($a_info)
    {
        $this->info = $a_info;
    }

    /**
    * Get Info.
    *
    * @return	string	Info
    */
    public function getInfo()
    {
        return $this->info;
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }
    
    public function setDisabled($a_disabled)
    {
        $this->disabled = $a_disabled;
    }
    
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
    * Add Subitem
    *
    * @param	object	$a_item		Item
    */
    public function addSubItem($a_item)
    {
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
}
