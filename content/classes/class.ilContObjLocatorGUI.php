<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Content Object Locator GUI
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilContObjLocatorGUI
{
	var $mode;
	var $temp_var;
	var $tree;
	var $obj;
	var $lng;
	var $tpl;


	function ilContObjLocatorGUI($a_tree)
	{
		global $lng, $tpl;

		$this->tree =& $a_tree;
		$this->mode = "std";
		$this->temp_var = "LOCATOR";
		$this->lng =& $lng;
		$this->tpl =& $tpl;
	}


	function setTemplateVariable($a_temp_var)
	{
		$this->temp_var = $a_temp_var;
	}


	function setObject($a_obj)
	{
		$this->obj =& $a_obj;
	}

	function setContentObject($a_cont_obj)
	{
		$this->cont_obj =& $a_cont_obj;
	}


	/**
	* display locator
	*/
	function display()
	{
		global $lng;

		$this->tpl->addBlockFile($this->temp_var, "locator", "tpl.locator.html");

		if (is_object($this->obj) && $this->tree->isInTree($this->obj->getId()))
		{
			$path = $this->tree->getPathFull($this->obj->getId());
		}
		else
		{
			$path = $this->tree->getPathFull($this->tree->getRootId());
			if (is_object($this->obj))
			{
				$path[] = array("child" => $this->obj->getId(), "title" => $this->obj->getTitle());
			}
		}

		$modifier = 1;

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			if ($row["child"] == 1)
			{
				$title = $this->cont_obj->getTitle();
				$cmd = "properties";
			}
			else
			{
				$title = $row["title"];
				$cmd = "view";
			}
			$this->tpl->setVariable("ITEM", $title);
			$obj_str = ($row["child"] == 1)
				? ""
				: "&obj_id=".$row["child"];
			$this->tpl->setVariable("LINK_ITEM", "lm_edit.php?cmd=$cmd&ref_id=".
				$this->cont_obj->getRefId().$obj_str);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->parseCurrentBlock();
	}

}
?>
