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
* Class ilExplorer
* class for explorer view in admin frame
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ilExplorer
{
	/**
	* ilias object
	* @var object Ilias
	* @access public
	*/
	var $ilias;

	/**
	* output
	* @var string
	* @access public
	*/
	var $output;

	/**
	* contains format options
	* @var array
	* @access public
	*/
	var $format_options;

	/**
	* tree
	* @var object Tree
	* @access public
	*/
	var $tree;

	/**
	* target
	* @var string
	* @access public
	*/
	var $target;

	/**
	* target get parameter
	* @var string
	* @access public
	*/
	var $target_get;

	/**
	* additional get parameter
	* @var string
	* @access public
	*/
	var $params_get;

	/**
	* expanded
	* @var array
	* @access public
	*/
	var $expanded;

	/**
	* order column
	* @var string
	* @access private
	*/
	var $order_column;

	/**
	* target script for expand icon links
	* @var string
	* @access private
	*/
	var $expand_target;

	/**
	* rbac check true/false (default true)
	* @var boolean
	* @access private
	*/
	var $rbac_check;


	/**
	* output icons true/false (default true)
	* @var boolean
	* @access private
	*/
	var $output_icons;

	/**
	* name of session expand variable
	* @var boolean
	* @access private
	*/
	var $expand_variable;

	/**
	* array ($type => clickable (empty means true, "n" means false)
	* @var array
	* @access private
	*/
	var $is_clickable;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	function ilExplorer($a_target)
	{
		global $ilias;

		if (!isset($a_target) or !is_string($a_target))
		{
			$this->ilias->raiseError(get_class($this)."::Constructor(): No target given!",$this->ilias->error_obj->WARNING);
		}

		$this->ilias =& $ilias;
		$this->output = array();
		$this->expanded = array();
		$this->target = $a_target;
		$this->target_get = 'ref_id';
		$this->frameTarget = "content";
		$this->order_column = "title";
		$this->tree = new ilTree(ROOT_FOLDER_ID);
		$this->expand_target = $_SERVER["SCRIPT_NAME"];
		$this->rbac_check = true;
		$this->output_icons = true;
		$this->expand_variable = "expand";
	}

	/**
	* set the order column
	* @access	public
	* @param	string		name of order column
	*/
	function setOrderColumn($a_column)
	{
		$this->order_column = $a_column;
	}

	/**
	* set the varname in Get-string
	* @access	public
	* @param	string		varname containing Ids to be used in GET-string
	*/
	function setTargetGet($a_target_get)
	{
		if (!isset($a_target_get) or !is_string($a_target_get))
		{
			$this->ilias->raiseError(get_class($this)."::setTargetGet(): No target given!",$this->ilias->error_obj->WARNING);
		}

		$this->target_get = $a_target_get;
	}

	/**
	* set additional params to be passed in Get-string
	* @access	public
	* @param	array
	*/
	function setParamsGet($a_params_get)
	{
		if (!isset($a_params_get) or !is_array($a_params_get))
		{
			$this->ilias->raiseError(get_class($this)."::setTargetGet(): No target given!",$this->ilias->error_obj->WARNING);
		}

		foreach ($a_params_get as $key => $val)
		{
			$str .= "&".$key."=".$val;
		}

		$this->params_get = $str;
	}


	/**
	* target script for expand icons
	*
	* @param	string		$a_exp_target	script name of target script(may include parameters)
	*										initially set to $_SERVER["SCRIPT_NAME"]
	*/
	function setExpandTarget($a_exp_target)
	{
		$this->expand_target = $a_exp_target;
	}

	/**
	* check permissions via rbac
	*
	* @param	boolean		$a_check		check true/false
	*/
	function checkPermissions($a_check)
	{
		$this->rbac_check = $a_check;
	}

	/**
	* set name of expand session variable
	*
	* @param	string		$a_var_name		variable name
	*/
	function setSessionExpandVariable($a_var_name = "expand")
	{
		$this->expand_variable = $a_var_name;
	}

	/**
	* output icons
	*
	* @param	boolean		$a_icons		output icons true/false
	*/
	function outputIcons($a_icons)
	{
		$this->output_icons = $a_icons;
	}


	/**
	* (de-)activates links for a certain object type
	*
	* @param	string		$a_type			object type
	* @param	boolean		$a_clickable	true/false
	*/
	function setClickable($a_type, $a_clickable)
	{
		if($a_clickable)
		{
			$this->is_clickable[$a_type] = "";
		}
		else
		{
			$this->is_clickable[$a_type] = "n";
		}
	}


	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type)
	{
		if($this->is_clickable[$a_type] == "n")
		{
			return false;
		}
		else
		{
			return true;
		}
	}


	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	function setOutput($a_parent_id, $a_depth = 1)
	{
		global $rbacadmin, $rbacsystem;
		static $counter = 0;

		if (!isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::setOutput(): No node_id given!",$this->ilias->error_obj->WARNING);
		}
		$objects = $this->tree->getChilds($a_parent_id, $this->order_column);
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
						if ($object["child"] != $this->tree->getRootId())
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
						if ($object["child"] != $this->tree->getRootId() and (!in_array($object["parent"],$this->expanded)
						   or !$this->format_options["$parent_index"]["visible"]))
						{
							$this->format_options["$counter"]["visible"] = false;
						}

						// if object exists parent is container
						if ($object["child"] != $this->tree->getRootId())
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

	/**
	* Creates output
	* recursive method
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
			if ($key == 0)
			{
				$this->formatHeader($options["child"],$options);
			}
		}

		return implode('',$this->output);
	}

	/**
	* Creates output for header
	* (is empty here but can be overwritten in derived classes)
	*
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	*/
	function formatHeader($a_obj_id,$a_option)
	{
	}

	/**
	* Creates output
	* recursive method
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject($a_node_id,$a_option)
	{
		global $lng;

		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
									"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$tpl = new ilTemplate("tpl.tree.html", true, true);

		foreach ($a_option["tab"] as $picture)
		{
			if ($picture == 'plus')
			{
				$target = $this->createTarget('+',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_TARGET", $target);
				$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/plus.gif"));
				$tpl->parseCurrentBlock();
			}

			if ($picture == 'minus')
			{
				$target = $this->createTarget('-',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_TARGET", $target);
				$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/minus.gif"));
				$tpl->parseCurrentBlock();
			}

			if ($picture == 'blank' or $picture == 'winkel'
			   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke')
			{
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/".$picture.".gif"));
				$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
				$tpl->parseCurrentBlock();
			}
		}

		if ($this->output_icons)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" ,ilUtil::getImagePath("icon_".$a_option["type"].".gif"));
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
			$tpl->parseCurrentBlock();
		}

		if($this->isClickable($a_option["type"]))	// output link
		{
			$tpl->setCurrentBlock("link");
			$target = (strpos($this->target, "?") === false) ?
				$this->target."?" : $this->target."&";
			$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			$tpl->setVariable("TITLE", $a_option["title"]);

			if ($this->frameTarget != "")
			{
				$tpl->setVariable("TARGET", " target=\"".$this->frameTarget."\"");
			}
			$tpl->parseCurrentBlock();
		}
		else			// output text only
		{
			$tpl->setCurrentBlock("text");
			$tpl->setVariable("OBJ_TITLE", $a_option["title"]);
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

	/**
	* Creates Get Parameter
	* @access	private
	* @param	string
	* @param	integer
	* @return	string
	*/
	function createTarget($a_type,$a_node_id)
	{
		if (!isset($a_type) or !is_string($a_type) or !isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::createTarget(): Missing parameter or wrong datatype! ".
									"type: ".$a_type." node_id:".$a_node_id,$this->ilias->error_obj->WARNING);
		}

		// SET expand parameter:
		//     positive if object is expanded
		//     negative if object is compressed
		$a_node_id = $a_type == '+' ? $a_node_id : -(int) $a_node_id;

		$sep = (is_int(strpos($this->expand_target, "?")))
			? "&"
			: "?";
		return $this->expand_target.$sep.$this->expand_variable."=".$a_node_id.$this->params_get;
	}

	/**
	* set target
	* frame or not frame?
	* @param	string
	* @access	public
	*/
	function setFrameTarget($a_target)
	{
		$this->frameTarget = $a_target;
	}

	/**
	* Creates lines for explorer view
	* @access	private
	* @param	integer
	*/
	function createLines($a_depth)
	{
		for ($i = 0; $i < count($this->format_options); ++$i)
		{
			if ($this->format_options[$i]["depth"] == $a_depth+1
			   and !$this->format_options[$i]["container"]
				and $this->format_options[$i]["depth"] != 1)
			{
				$this->format_options[$i]["tab"]["$a_depth"] = "quer";
			}

			if ($this->format_options[$i]["depth"] == $a_depth+2)
			{
				if ($this->is_in_array($i+1,$this->format_options[$i]["depth"]))
				{
					$this->format_options[$i]["tab"]["$a_depth"] = "winkel";
				}
				else
				{
					$this->format_options[$i]["tab"]["$a_depth"] = "ecke";
				}
			}

			if ($this->format_options[$i]["depth"] > $a_depth+2)
			{
				if ($this->is_in_array($i+1,$a_depth+2))
				{
					$this->format_options[$i]["tab"]["$a_depth"] = "hoch";
				}
			}
		}
	}

	/**
	* DESCRIPTION MISSING
	* @access	private
	* @param	integer
	* @param	integer
	* @return	boolean
	*/
	function is_in_array($a_start,$a_depth)
	{
		for ($i=$a_start;$i<count($this->format_options);++$i)
		{
			if ($this->format_options[$i]["depth"] < $a_depth)
			{
				break;
			}

			if ($this->format_options[$i]["depth"] == $a_depth)
			{
				return true;
			}
		}
		return false;
	}

	/**
	* get index of format_options array from specific ref_id,parent_id
	* @access	private
	* @param	array		object data
	* @return	integer		index
	**/
	function getIndex($a_data)
	{
		foreach ($this->format_options as $key => $value)
		{
			if (($value["child"] == $a_data["parent"]))
			{
				return $key;
			}
		}

		// exit on error
		$this->ilias->raiseError(get_class($this)."::getIndex(): Error in tree. No index found!",$this->ilias->error_obj->FATAL);
	}

	/**
	* adds item to the filter
	* @access	public 
	* @param	string		object type to add
	* @return	boolean
	*/
	function addFilter($a_item)
	{
		$ispresent = 0;
		
		if (is_array($this->filter))
		{
			//run through filter
		    foreach ($this->filter as $item)
			{
				if ($item == $a_item)
				{
				    $is_present = 1;

					return false;
				}
			}
		}
		else
		{
			$this->filter = array();
		}
		
		if ($is_present == 0)
		{
			$this->filter[] = $a_item;

		}

		return true;
	}
	
	/**
	* adds item to the filter
	* @access	public
	* @param	string		object type to delete
	* @return	boolean
	*/
	function delFilter($a_item)
	{
		//check if a filter exists
		if (is_array($this->filter))
		{
			//build copy of the existing filter without the given item
			$tmp = array();

			foreach ($this->filter as $item)
		    {
				if ($item != $a_item)
				{
				    $tmp[] = $item;
				}
				else
				{
					$deleted = 1;
				}
			}
			$this->filter = $tmp;
		}
		else
		{
			return false;
		}

		if ($deleted == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
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
		if(!is_array($_SESSION[$this->expand_variable]))
		{
			$_SESSION[$this->expand_variable] = array($this->tree->getRootId());
		}
		// IF $_GET["expand"] is positive => expand this node
		if ($a_node_id > 0 && !in_array($a_node_id,$_SESSION[$this->expand_variable]))
		{
			array_push($_SESSION[$this->expand_variable],$a_node_id);
		}
		// IF $_GET["expand"] is negative => compress this node
		if ($a_node_id < 0)
		{
			$key = array_keys($_SESSION[$this->expand_variable],-(int) $a_node_id);
			unset($_SESSION[$this->expand_variable][$key[0]]);
		}
		$this->expanded = $_SESSION[$this->expand_variable];
	}

	/**
	* active/deactivate the filter
	* @access	public
	* @param	boolean
	* @return	boolean
	*/
	function setFiltered($a_bool)
	{
		$this->filtered = $a_bool;
		return true;
	}

	/**
	* check if item is in filter
	* @access	private
	* @param	string
	* @return	integer
	*/
	function checkFilter($a_item)
	{
		if (is_array($this->filter))
		{
			return in_array($a_item, $this->filter);
		}
		else
		{
			return false;
		}
	}
} // END class.ilExplorer
?>
