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
	function addSubItem($a_item)
	{
		$this->sub_items[] = $a_item;
	}

	/**
	* Get Subitems
	*
	* @return	array	Array of items
	*/
	function getSubItems()
	{
		return $this->sub_items;
	}

	/**
	* Check SubItems
	*
	* @return	boolean		Input ok, true/false
	*/	
	final function checkSubItemsInput()
	{
		$ok = true;
		foreach($this->getSubItems() as $item)
		{
			$item_ok = $item->checkInput();
			if(!$item_ok)
			{
				$ok = false;
			}
		}
		return $ok;
	}

	/**
	* Get sub form html
	*
	*/
	final function getSubForm()
	{
		// subitems
		$pf = null;
		if (count($this->getSubItems()) > 0)
		{
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
	function getItemByPostVar($a_post_var)
	{
		if ($this->getPostVar() == $a_post_var)
		{
			return $this;
		}

		foreach($this->getSubItems() as $item)
		{
			if ($item->getType() != "section_header")
			{
				$ret = $item->getItemByPostVar($a_post_var);
				if (is_object($ret))
				{
					return $ret;
				}
			}
		}
		
		return false;
	}

}
