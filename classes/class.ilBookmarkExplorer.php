<?php

/*
* Explorer View for Bookmarks
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/

require_once("classes/class.ilExplorer.php");

class ilBookmarkExplorer extends ilExplorer
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
	function ilBookmarkExplorer($a_target,$a_user_id)
	{
		parent::ilExplorer($a_target);
		$this->tree = new ilTree($a_user_id);
		$this->tree->setTableNames('bookmark_tree','bookmark_data');
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
		$this->format_options[0]["tab"] = array();

		$depth = $this->tree->getMaximumDepth();

		for ($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}

		foreach ($this->format_options as $key => $options)
		{
			if ($options["visible"] and $key != 0)
			{
				$this->formatObject($options["child"],$options);
			}
			if($key == 0)
			{
				$this->formatHeader($options["child"],$options);
			}
		}

		return implode('',$this->output);
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
					//$objects[$i]["title"] = $lng->txt("mail_".$objects[$i]["title"]);
					//$objects[$i]["title"] = "TEEST";
				}
			}

			foreach ($objects as $key => $object)
			{
				if ($object["type"] != "bmf" && $object["type"] != "dum")
				{
					continue;
				}

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
				$this->format_options["$counter"]["depth"] = $tab;
				$this->format_options["$counter"]["container"] = false;
				$this->format_options["$counter"]["visible"]	  = true;

				// Create prefix array
				for ($i = 0; $i < $tab; ++$i)
				{
					$this->format_options["$counter"]["tab"][] = 'blank';
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
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
		global $lng, $ilias;

		$tpl = new ilTemplate("tpl.tree.html", true, true);

		$tpl->setCurrentBlock("row");
		$tpl->setVariable("TYPE", $a_option["type"]);
		$tpl->setVariable("TITLE", $lng->txt("bookmarks_of")." ".$ilias->account->getFullname());
		$tpl->setVariable("LINK_TARGET", $this->target."?".$this->target_get."=1");
		$tpl->setVariable("TARGET", " target=\"content\"");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
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

		return $_SERVER["SCRIPT_NAME"]."?cmd=explorer&mexpand=".$a_child;
	}
} // END class.ilMailExplorer
?>
