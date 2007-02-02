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
* Class Mail Explorer 
* class for explorer view for mailboxes
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
require_once("classes/class.ilExplorer.php");

class ilMailExplorer extends ilExplorer
{
	/**
	 * user_id
	 * @var int uid
	 * @access private
	 */
	var $user_id;

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilMailExplorer($a_target,$a_user_id)
	{
		parent::ilExplorer($a_target);
		$this->tree = new ilTree($a_user_id);
		$this->tree->setTableNames('mail_tree','mail_obj_data');
		$this->root_id = $this->tree->readRootId();
		$this->user_id = $a_user_id;
	}
	
	/**
	* Creates output
	* overwritten method from class Explorer
	* @access	public
	* @return	string
	*/
	function getOutput()
	{
		global $tpl;
		
		$this->format_options[0]["tab"] = array();
		
		$depth = $this->tree->getMaximumDepth();
		
		for ($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}
		
		$tpl->addBlockFile("EXPLORER_TOP", "exp_top", "tpl.explorer_top.html");
		
		// set global body class
		$tpl->setVariable("BODY_CLASS", "il_Explorer");
		
		$tpl_tree = new ilTemplate("tpl.tree.html", true, true);
		
		$cur_depth = -1;

		foreach ($this->format_options as $key => $options)
		{
			if (!$options["visible"])
			{
				continue;
			}

			// end tags
			$this->handleListEndTags($tpl_tree, $cur_depth, $options["depth"]);
			
			// start tags
			$this->handleListStartTags($tpl_tree, $cur_depth, $options["depth"]);
			
			$cur_depth = $options["depth"];

			if ($options["visible"] and $key != 0)
			{
				$this->formatObject($tpl_tree, $options["child"],$options);
			}
			if($key == 0)
			{
				$this->formatHeader($tpl_tree, $options["child"],$options);
			}
		}
		
		$this->handleListEndTags($tpl_tree, $cur_depth, -1);
		
		return $tpl_tree->get();
	}
	

	/**
	* Overwritten method from class.Explorer.php to avoid checkAccess selects
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/

	function setOutput($a_parent, $a_depth = 1)
	{
		global $lng;
		static $counter = 0;

		if ($objects =  $this->tree->getChilds($a_parent,"title,type"))
		{
//			var_dump("<pre>",$objects,"</pre");
			$tab = ++$a_depth - 2;
			
			if($a_depth < 4)
			{
				for($i=0;$i<count($objects);++$i)
				{
					$objects[$i]["title"] = $lng->txt("mail_".$objects[$i]["title"]);
				}
			}

			foreach ($objects as $key => $object)
			{
				//ask for FILTER
				if ($object["child"] != $this->root_id)
				{
					//$data = $this->tree->getParentNodeData($object["child"]);
					$parent_index = $this->getIndex($object);
				}
				$this->format_options["$counter"]["parent"] = $object["parent"];
				$this->format_options["$counter"]["child"] = $object["child"];
				$this->format_options["$counter"]["title"] = $object["title"];
				$this->format_options["$counter"]["type"] = $object["type"];
				$this->format_options["$counter"]["desc"] = $object["type"];
				$this->format_options["$counter"]["depth"] = $tab;
				$this->format_options["$counter"]["container"] = false;
				$this->format_options["$counter"]["visible"]	  = true;

				// Create prefix array
				for ($i = 0; $i < $tab; ++$i)
				{
					$this->format_options["$counter"]["tab"][] = 'blank';
				}
				
				// fix explorer (sometimes explorer disappears)
				if ($parent_index == 0)
				{
					if (!in_array($object["parent"], $this->expanded))
					{
						$this->expanded[] = $object["parent"];
					}
				}
				
				// only if parent is expanded and visible, object is visible
				if ($object["child"] != $this->root_id  and (!in_array($object["parent"],$this->expanded) 
														  or !$this->format_options["$parent_index"]["visible"]))
				{
					$this->format_options["$counter"]["visible"] = false;
				}
				
				// if object exists parent is container
				if ($object["child"] != $this->root_id)
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
			} //foreach
		} //if
	} //function

	/**
	* method to create a mail system specific header
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
	}

	/**
	* set the expand option
	* this value is stored in a SESSION variable to save it different view (lo view, frm view,...)
	* @access	private
	* @param	string		pipe-separated integer
	*/
	function setExpand($a_node_id)
	{
		// IF ISN'T SET CREATE SESSION VARIABLE
		if(!is_array($_SESSION["mexpand"]))
		{
			$_SESSION["mexpand"] = array();
		}
		// IF $_GET["expand"] is positive => expand this node
		if($a_node_id > 0 && !in_array($a_node_id,$_SESSION["mexpand"]))
		{
			array_push($_SESSION["mexpand"],$a_node_id);
		}
		// IF $_GET["expand"] is negative => compress this node
		if($a_node_id < 0)
		{
			$key = array_keys($_SESSION["mexpand"],-(int) $a_node_id);
			unset($_SESSION["mexpand"][$key[0]]);
		}
		$this->expanded = $_SESSION["mexpand"];
	}
	/**
	* Creates Get Parameter
	* @access	private
	* @param	string
	* @param	integer
	* @return	string
	*/
	function createTarget($a_type,$a_child)
	{
		// SET expand parameter:
		//     positive if object is expanded
		//     negative if object is compressed
		$a_child = $a_type == '+' ? $a_child : -(int) $a_child;

		return eregi_replace("(mexpand=)(-?[0-9]+)", "\\1".$a_child, $_SERVER["REQUEST_URI"]);
	}
} // END class.ilMailExplorer
?>