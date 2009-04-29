<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Form property dispatcher. Forwards control flow to property form input GUI
* classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup 
*/
class ilFormPropertyDispatchGUI
{
	/**
	* Constructor
	*/
	function _construct()
	{
	}
	
	/**
	* Set filter item
	*
	* @param	object		filter item
	*/
	function setFilterItem($a_val)
	{
		$this->filter_item = $a_val;
	}
	
	/**
	* Get filter item
	*
	* @return	object		filter item
	*/
	function getFilterItem()
	{
		return $this->filter_item;
	}
	
	/**
	* Execute command.
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
//echo "-".strtolower(get_class($this->getFilterItem()))."-".$next_class."-";
		if (strtolower(get_class($this->getFilterItem())) != $next_class)
		{
			die("ilFormPropertyDispatch: Forward Error.");
		}
		
		return $ilCtrl->forwardCommand($this->getFilterItem());
	}

}
