<?php
/**
* Class Explorer 
* class for explorer view in admin frame
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-core
* @todo maybe only container should be visible, because the number of objects could be to big for recursion
* implement a sort function
*/
class Explorer
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
	* expanded
	* @var array
	* @access public
	*/
	var $expanded;
	
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	function Explorer($a_target)
	{
		global $ilias;
		
		$this->ilias =& $ilias;
		$this->output = "";
		$this->expanded = array();
		$this->target = $a_target;		
		$this->tree = new Tree(1,0);
		$this->frameTarget = "content";
	}
	
	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	function setOutput($a_parent, $a_depth = 1)
	{
		global $rbacadmin, $rbacsystem;
		static $counter = 0;

		if ($objects =  $this->tree->getChilds($a_parent,"title"))
		{
			$tab = ++$a_depth - 2;
			
			// Maybe call a lexical sort function for the child objects
			foreach ($objects as $key => $object)
			{
				//ask for FILTER
				if ($this->filtered == false || $this->checkFilter($object["type"])==true)
				{
					if ($rbacsystem->checkAccess('visible',$object["id"],$object["parent"]))
					{
						if ($object["id"] != 1)
						{
							$data = $this->tree->getParentNodeData($object["id"],$object["parent"]);
							$parent_index = $this->getIndex($data);
						}

						$this->format_options["$counter"]["parent"] = $object["parent"];
						$this->format_options["$counter"]["obj_id"] = $object["id"];
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
						if ($object["id"] != 1 and (!in_array($object["parent"],$this->expanded) 
						   or !$this->format_options["$parent_index"]["visible"]))
						{
							$this->format_options["$counter"]["visible"] = false;
						}
						
						// if object exists parent is container
						if ($object["id"] != 1)
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
						$this->setOutput($object["id"],$a_depth);
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
			if ($options["visible"] or $key == 0 )
			{
				$this->formatObject($options["obj_id"],$options);
			}
		}

		return implode('',$this->output);
	}
	
	/**
	* Creates output
	* recursive method
	* @access	public
	* @param	integer
	* @param	integer
	* @return	string
	*/
	function formatObject($a_obj_id,$a_option)
	{
		$tpl = new Template("tpl.tree.html", true, true);
		
		foreach ($a_option["tab"] as $picture)
		{
			if ($picture == 'plus')
			{
				$target = $this->createTarget('+',$a_obj_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_TARGET", $target);
				$tpl->setVariable("ICONIMG", $picture);
				$tpl->parseCurrentBlock();
			}
			if ($picture == 'minus')
			{
				$target = $this->createTarget('-',$a_obj_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_TARGET", $target);
				$tpl->setVariable("ICONIMG", $picture);
				$tpl->parseCurrentBlock();
			}
			if ($picture == 'blank' or $picture == 'winkel' 
			   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke')
			{
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("ICONIMG", $picture);
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setCurrentBlock("row");
		$tpl->setVariable("TYPE", $a_option["type"]);
		$tpl->setVariable("LINK_TARGET", $this->target."?obj_id=".$a_obj_id."&parent=".$a_option["parent"]."&amp;expand=".$_GET["expand"]);
		$tpl->setVariable("TITLE", $a_option["title"]);

		if ($this->frameTarget != "")
		{
			$tpl->setVariable("TARGET", " target=\"".$this->frameTarget."\"");
		}
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
	function createTarget($a_type,$a_obj_id)
	{
		$tmp_expanded = $this->expanded;

		if ($a_type == '+')
		{
			return $_SERVER["SCRIPT_NAME"]."?expand=".$_GET["expand"]."|".$a_obj_id.
				"&amp;obj_id=".$_GET["obj_id"]."&amp;parent=".$_GET["parent"];
		}

		if ($a_type == '-')
		{
			$tmp = "?expand=";
			$tmp_expanded = array_flip($tmp_expanded);
			unset($tmp_expanded["$a_obj_id"]);
			$tmp_expanded = array_flip($tmp_expanded);
			
			return $tmp.implode('|',$tmp_expanded).
				"&amp;obj_id=".$_GET["obj_id"]."&amp;parent=".$_GET["parent"];
		}
	}

	/**
	* set target
	* frame or not frame?
	* @param string
	* @access public
	*/	
	function setFrameTarget($target)
	{
		$this->frameTarget = $target;
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
	* get index of format_options array from specific obj_id,parent_id
	* @access	private
	* @param	array		object data
	* @return	integer		index
	**/
	function getIndex($a_data)
	{
		foreach ($this->format_options as $key => $value)
		{
			if (($value["obj_id"] == $a_data["obj_id"]) 
			   && ($value["parent"] == $a_data["parent"]))
			{
				return $key;
			}
		}

		$this->ilias->raiseError("Error in tree",$this->ilias->error_obj->FATAL);
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
	* @access	private
	* @param	string		pipe-separated integer
	*/
	function setExpand($str)
	{
		$this->expanded = explode('|',$str);
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
} // END class.Explorer
?>
