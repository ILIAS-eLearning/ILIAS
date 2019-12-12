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

/**
* This class represents an option in a radio group
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
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
