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
require_once("classes/class.ilExplorer.php");
require_once("classes/class.ilObjectFactory.php");

class ilGroupExplorer extends ilExplorer
{
	var $grp_tree;
	
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int group ref_id
	*/
	function ilGroupExplorer($a_target, $a_ref_id)
	{
		parent::ilExplorer($a_target);
		
		//TODO: solange die Tabelle grp_tree mit obj_id's arbeitet muß: ref_id->obj_id
		$obj_data = & $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);
		$this->grp_tree = new ilTree($obj_data->getId());
		$this->grp_tree->setTableNames("grp_tree","object_data");
		$this->tree = $this->grp_tree;
		
		/*echo "-".$this->tree->readRootId();
		echo "-".$this->tree->getRootId();
		echo "-".$this->tree->getTreeId();
		echo " BR ";*/
		
		
	}
	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	/*function setOutput($a_parent_id, $a_depth = 1)
	{
		global $rbacadmin, $rbacsystem;
		static $counter = 0;

		if (!isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::setOutput(): No node_id given!",$this->ilias->error_obj->WARNING);
		}
		$objects = $this->grp_tree->getChilds($a_parent_id, $this->order_column);
		
		if (count($objects) > 0)
		{
			$tab = ++$a_depth - 2;
			// Maybe call a lexical sort function for the child objects
			foreach ($objects as $key => $object)
			{
				//ask for FILTER
				if ($this->filtered == false || $this->checkFilter($object["type"])==true)
				{
					if ($rbacsystem->checkAccess("visible",$object["child"]) || (!$this->rbac_check))
					{
						if ($object["child"] != $this->grp_tree->getRootId())
						{
							$parent_index = $this->getIndex($object);
						}
						$this->format_options["$counter"]["parent"]		= $object["parent"];
						$this->format_options["$counter"]["child"]		= $object["child"];
						$this->format_options["$counter"]["title"]		= $object["title"];
						$this->format_options["$counter"]["type"]		= $object["type"];
						$this->format_options["$counter"]["desc"] 		= "obj_".$object["type"];
						$this->format_options["$counter"]["depth"]		= $tab;
						$this->format_options["$counter"]["container"]	= false;
						$this->format_options["$counter"]["visible"]	= true;

						// Create prefix array
						for ($i = 0; $i < $tab; ++$i)
						{
							 $this->format_options["$counter"]["tab"][] = 'blank';
						}

						// only if parent is expanded and visible, object is visible
						if ($object["child"] != $this->grp_tree->getRootId() and (!in_array($object["parent"],$this->expanded)
						   or !$this->format_options["$parent_index"]["visible"]))
						{
							$this->format_options["$counter"]["visible"] = false;
						}

						// if object exists parent is container
						if ($object["child"] != $this->grp_tree->getRootId())
						{
							$this->format_options["$parent_index"]["container"] = true;

							if (in_array($object["parent"],$this->expanded))
							{
								$this->format_options["$parent_index"]["tab"][($tab-2)] = 'minus';
							}
							else
							{
								$this->format_options["$parent_index"]["tab"][($tab-2)] = 'plus';
							}
						}

						++$counter;

						// Recursive
						$this->setOutput($object["child"],$a_depth);
					} //if
				} //if FILTER
			} //foreach
		} //if
	} //function
	*/
	
}






?>
