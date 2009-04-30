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

define("IL_FM_POSITIVE", 1);
define("IL_FM_NEGATIVE", 2);

/**
* Class ilExplorer
* class for explorer view in admin frame
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
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
	* order direction
	* @var string
	* @access private
	*/
	var $order_direction = "asc";

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
	* process post sorting true/false
	* @var boolean
	* @access private
	*/
	var $post_sort;

	/**
	* set object type filter true/false
	* @var boolean
	* @access private
	*/
	var $filtered = false;

	/**
	* set filter mode
	* @var boolean
	* @access private
	*/
	var $filter_mode;

	/**
	* expand entire tree regardless of values in $expanded
	* @var boolean
	* @access private
	*/
	var $expand_all = false;
	
	/**
	* Root id. One can set it using setRoot
	* @var boolean
	* @access private
	*/
	var $root_id = null;
	
	var $use_standard_frame = false;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	function ilExplorer($a_target)
	{
		global $ilias, $objDefinition;

		if (!isset($a_target) or !is_string($a_target))
		{
			$this->ilias->raiseError(get_class($this)."::Constructor(): No target given!",$this->ilias->error_obj->WARNING);
		}

		// autofilter object types in devmode
		$devtypes = $objDefinition->getDevModeAll();

		if (count($devtypes > 0))
		{
			// activate filter if objects found in devmode
			$this->setFiltered(true);

			foreach ($devtypes as $type)
			{
				$this->addFilter($type);
			}
		}

		$this->ilias =& $ilias;
		$this->output = array();
		$this->expanded = array();
		$this->target = $a_target;
		$this->target_get = 'ref_id';
		$this->frame_target = "content";
		$this->order_column = "title";
		$this->tree = new ilTree(ROOT_FOLDER_ID);
		$this->expand_target = $_SERVER["PATH_INFO"];
		$this->rbac_check = true;
		$this->output_icons = true;
		$this->expand_variable = "expand";
		$this->textwidth=50;
		$this->post_sort=true;
		$this->setFilterMode(IL_FM_NEGATIVE);
		$this->highlighted = "";
		$this->show_minus = true;
		$this->counter = 0;
	}
	
	/**
	 * Init item counter
	 *
	 * @access public
	 * @param int number
	 * 
	 */
	public function initItemCounter($a_number)
	{
	 	$this->counter = $a_number;
	}
	
	/**
	* Set title
	*
	* @param	title
	*/
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	* Get title
	*
	* @return	title
	*/
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Set root node
	 *
	 * @access public
	 * @param int ref id of root node
	 * 
	 */
	public function setRoot($a_root_id)
	{
	 	#$this->tree = new ilTree(ROOT_FOLDER_ID,$a_root_id);
	 	$this->root_id = $a_root_id;
	}
	
	/**
	 * get root id
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getRoot()
	{
	 	return $this->root_id == null ?
	 		$this->tree->getRootId() :
	 		$this->root_id;
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
	* set the order direction
	* @access	public
	* @param	string		name of order column
	*/
	function setOrderDirection($a_direction)
	{
		if ($a_direction == "desc")
		{
			$this->order_direction = $a_direction;
		}
		else
		{
			$this->order_direction = "asc";
		}
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
	*										initially set to $_SERVER["PATH_INFO"]
	*/
	function setExpandTarget($a_exp_target)
	{
		$this->expand_target = $a_exp_target;
	}
	
	/**
	* set highlighted node
	*/
	function highlightNode($a_id)
	{
		$this->highlighted = $a_id;
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

	function isVisible($a_ref_id,$a_type)
	{
		global $rbacsystem, $ilBench;
		
		if (!$this->rbac_check)
		{
			return true;
		}
		
		$ilBench->start("Explorer", "setOutput_isVisible");
		$visible = $rbacsystem->checkAccess('visible',$a_ref_id);
		$ilBench->stop("Explorer", "setOutput_isVisible");

		return $visible;
	}

	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_ref_id = 0)
	{
		// in this standard implementation
		// only the type determines, wether an object should be clickable or not
		// but this method can be overwritten and make $exp->setFilterMode(IL_FM_NEGATIVE);use of the ref id
		// (this happens e.g. in class ilRepositoryExplorerGUI)
		if ($this->is_clickable[$a_type] == "n")
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* process post sorting
	* @param	boolean		$a_sort		true / false
	*/
	function setPostSort($a_sort)
	{
		$this->post_sort = $a_sort;
	}

	/**
	* set filter mode
	*
	* @param	int		$a_mode		filter mode IL_FM_NEGATIVE | IL_FM_NEGATIVE
	*/
	function setFilterMode($a_mode = IL_FM_NEGATIVE)
	{
		$this->filter_mode = $a_mode;
	}

	/**
	* get filter mode
	*
	* @return	int		filter mode IL_FM_NEGATIVE | IL_FM_NEGATIVE
	*/
	function getFilterMode()
	{
		return $this->filter_mode;
	}

	/**
	* Set use standard frame. If true, the standard
	* explorer frame (like in the repository) is put around the tree.
	*
	* @param	boolean		use standard explorer frame
	*/
	function setUseStandardFrame($a_val)
	{
		$this->use_standard_frame = $a_val;
	}
	
	/**
	* Get use standard explorer frame
	*
	* @return	boolean		use standard explorer frame
	*/
	function getUseStandardFrame()
	{
		return $this->use_standard_frame;
	}
	
	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	function setOutput($a_parent_id, $a_depth = 1,$a_obj_id = 0)
	{
		global $rbacadmin, $rbacsystem, $ilBench;

#echo 'ParentId: '.$a_parent_id.' depth: '.$a_depth.' obj_id: '.$a_obj_id;

		if (!isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::setOutput(): No node_id given!",$this->ilias->error_obj->WARNING);
		}

		if ($this->showChilds($a_parent_id,$a_obj_id))
		{
#echo "<br>getChildsFor:".$a_parent_id.":";
			$ilBench->start("Explorer", "setOutput_getChilds");
			$objects = $this->tree->getChilds($a_parent_id, $this->order_column);
			$ilBench->stop("Explorer", "setOutput_getChilds");
		}
		else
		{
			$objects = array();
		}
		
		$objects = $this->modifyChilds($a_parent_id, $objects);
		
		// force expansion (of single nodes)
		if ($this->forceExpanded($a_obj_id) && !in_array($a_obj_id, $this->expanded))
		{
			$this->expanded[] = $a_obj_id;
		}

		if (count($objects) > 0)
		{
			// Maybe call a lexical sort function for the child objects
			$ilBench->start("Explorer", "setOutput_sortNodes");
			$tab = ++$a_depth - 2;
			if ($this->post_sort)
			{
				$objects = $this->sortNodes($objects,$a_obj_id);
			}
			$ilBench->stop("Explorer", "setOutput_sortNodes");

			$skip_rest = false;
			
			foreach ($objects as $key => $object)
			{
//echo "<br>-KEY:$key";
//var_dump($object);
				// skip childs, if parent is not expanded
				if (!$this->forceExpanded($object["child"]) && $skip_rest)
				{
					continue;
				}
				//ask for FILTER
				if ($this->filtered == false or $this->checkFilter($object["type"]) == false)
				{
					if ($this->isVisible($object['child'],$object['type']))
					{
						$ilBench->start("Explorer", "setOutput_setFormatOptions");
						#echo 'CHILD getIndex() '.$object['child'].' parent: '.$this->getRoot();
						if ($object["child"] != $this->getRoot())
						{
							$parent_index = $this->getIndex($object);
						}
						$this->format_options["$this->counter"]["parent"]		= $object["parent"];
						$this->format_options["$this->counter"]["child"]		= $object["child"];
						$this->format_options["$this->counter"]["title"]		= $object["title"];
						$this->format_options["$this->counter"]["type"]		= $object["type"];
						$this->format_options["$this->counter"]["obj_id"]		= $object["obj_id"];
						$this->format_options["$this->counter"]["desc"] 		= "obj_".$object["type"];
						$this->format_options["$this->counter"]["depth"]		= $tab;
						$this->format_options["$this->counter"]["container"]	= false;
						$this->format_options["$this->counter"]["visible"]	= true;
						
						// Create prefix array
						for ($i = 0; $i < $tab; ++$i)
						{
							 $this->format_options["$this->counter"]["tab"][] = 'blank';
						}

						// fix explorer (sometimes explorer disappears)
						if ($parent_index == 0)
						{
							if (!$this->expand_all and !in_array($object["parent"], $this->expanded))
							{
								$this->expanded[] = $object["parent"];
							}
						}

						// only if parent is expanded and visible, object is visible
						if ($object["child"] != $this->getRoot() and ((!$this->expand_all and !in_array($object["parent"],$this->expanded))
						   or !$this->format_options["$parent_index"]["visible"]))
						{
							if (!$this->forceExpanded($object["child"]))
							{
								// if parent is not expanded, and one child is
								// visible we don't need more information and
								// can skip the rest of the childs
								if ($this->format_options["$this->counter"]["visible"])
								{
//echo "-setSkipping";
									$skip_rest = true;
								}
								$this->format_options["$this->counter"]["visible"] = false;
							}
						}

						// if object exists parent is container
						if ($object["child"] != $this->getRoot())
						{
							$this->format_options["$parent_index"]["container"] = true;

							if ($this->expand_all or in_array($object["parent"],$this->expanded))
							{
	//echo "<br>-".$object["child"]."-".$this->forceExpanded($object["child"])."-";
								if ($this->forceExpanded($object["parent"]))
								{
									$this->format_options["$parent_index"]["tab"][($tab-2)] = 'forceexp';
								}
								else
								{
									$this->format_options["$parent_index"]["tab"][($tab-2)] = 'minus';
								}
							}
							else
							{
								$this->format_options["$parent_index"]["tab"][($tab-2)] = 'plus';
							}
						}
	//echo "-"."$parent_index"."-";
	//var_dump($this->format_options["$parent_index"]);
						++$this->counter;
						$ilBench->stop("Explorer", "setOutput_setFormatOptions");
						
						// stop recursion if 2. level beyond expanded nodes is reached
						if ($this->expand_all or in_array($object["parent"],$this->expanded) or ($object["parent"] == 0)
							or $this->forceExpanded($object["child"]))
						{
							// recursive
							$this->setOutput($object["child"],$a_depth,$object['obj_id']);
						}
					} //if
				} //if FILTER
			} //foreach
		} //if
	} //function

	function modifyChilds($a_parent_id, $a_objects)
	{
		return $a_objects;
	}

	/**
	* determines wether the childs of an object should be shown or not
	* note: this standard implementation always returns true
	* but it could be overwritten by derived classes (e.g. ilRepositoryExplorerGUI)
	*/
	function showChilds($a_parent_id)
	{
		return true;
	}

	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		return false;
	}

	/**
	* Creates output
	* recursive method
	* @access	public
	* @return	string
	*/
	function getOutput()
	{
		global $ilBench, $tpl, $lng;

		$ilBench->start("Explorer", "getOutput");

		$this->format_options[0]["tab"] = array();

		$depth = $this->tree->getMaximumDepth();

		for ($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initConnection();
		$tpl->addJavaScript("./Services/Explorer/js/ilexplorercallback.js");

		$tpl->addBlockFile("EXPLORER_TOP", "exp_top", "tpl.explorer_top.html");
//echo "hh";
		// set global body class
		$tpl->setVariable("BODY_CLASS", "il_Explorer");
		
		$tpl_tree = new ilTemplate("tpl.tree.html", true, true);
		
		$cur_depth = -1;
		foreach ($this->format_options as $key => $options)
		{
//echo "-".$options["depth"]."-";
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
				$this->formatObject($tpl_tree, $options["child"],$options,$options['obj_id']);
			}
			if ($key == 0)
			{
				$this->formatHeader($tpl_tree, $options["child"],$options);
			}
						
		}
		
		$this->handleListEndTags($tpl_tree, $cur_depth, -1);
		
		$ilBench->stop("Explorer", "getOutput");
		
		$html = $tpl_tree->get();
		
		if ($this->getUseStandardFrame())
		{
			$mtpl = new ilTemplate("tpl.main.html", true, true);
			$mtpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
			$mtpl->setVariable("BODY_CLASS", "il_Explorer");
			$mtpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
			if ($this->getTitle() != "")
			{
				$mtpl->setVariable("TXT_EXPLORER_HEADER", $this->getTitle());
			}
			$mtpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
			$mtpl->setCurrentBlock("content");
			$mtpl->setVariable("EXPLORER", $html);
			$mtpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
			$mtpl->parseCurrentBlock();
			$html = $mtpl->get();
		}
		
		return $html;
	}
	
	
	
	/**
	* handle list end tags (</li> and </ul>)
	*/
	function handleListEndTags(&$a_tpl_tree, $a_cur_depth, $a_item_depth)
	{
		if ($a_item_depth < $a_cur_depth)
		{
			// </li></ul> for ending lists
			for ($i = 0; $i < ($a_cur_depth - $a_item_depth); $i++)
			{
				$a_tpl_tree->touchBlock("end_list_item");
				$a_tpl_tree->touchBlock("element");

				$a_tpl_tree->touchBlock("end_list");
				$a_tpl_tree->touchBlock("element");
			}
		}
		else if ($a_item_depth == $a_cur_depth)
		{
			// </li> for ending list items
			$a_tpl_tree->touchBlock("end_list_item");
			$a_tpl_tree->touchBlock("element");
		}
	}
	
	/**
	* handle list start tags (<ul> and <li>)
	*/
	function handleListStartTags(&$a_tpl_tree, $a_cur_depth, $a_item_depth)
	{
		// start tags
		if ($a_item_depth > $a_cur_depth)
		{
			// <ul><li> for new lists
			if ($a_item_depth > 1)
			{
				$a_tpl_tree->touchBlock("start_list");
			}
			else
			{
				$a_tpl_tree->touchBlock("start_list_no_indent");
			}
			$a_tpl_tree->touchBlock("element");
			
			$a_tpl_tree->touchBlock("start_list_item");
			$a_tpl_tree->touchBlock("element");
		}
		else
		{
			// <li> items
			$a_tpl_tree->touchBlock("start_list_item");
			$a_tpl_tree->touchBlock("element");
		}
	}

	/**
	* Creates output for header
	* (is empty here but can be overwritten in derived classes)
	*
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	*/
	function formatHeader(&$tpl,$a_obj_id,$a_option)
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
	function formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
									"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$pic = false;
		foreach ($a_option["tab"] as $picture)
		{
			if ($picture == 'plus')
			{
				$tpl->setCurrentBlock("exp_desc");
				$tpl->setVariable("EXP_DESC", $lng->txt("collapsed"));
				$tpl->parseCurrentBlock();
				$target = $this->createTarget('+',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/plus.gif"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}

			if ($picture == 'forceexp')
			{
				$tpl->setCurrentBlock("exp_desc");
				$tpl->setVariable("EXP_DESC", $lng->txt("expanded"));
				$tpl->parseCurrentBlock();
				$target = $this->createTarget('+',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/forceexp.gif"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}

			if ($picture == 'minus' && $this->show_minus)
			{
				$tpl->setCurrentBlock("exp_desc");
				$tpl->setVariable("EXP_DESC", $lng->txt("expanded"));
				$tpl->parseCurrentBlock();
				$target = $this->createTarget('-',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/minus.gif"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}

			/*
			if ($picture == 'blank' or $picture == 'winkel'
			   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke')
			{
				$picture = "blank";
				$tpl->setCurrentBlock("lines");
				$tpl->setVariable("IMGPATH_LINES", $this->getImage("browser/".$picture.".gif"));
				$tpl->parseCurrentBlock();
			}
			*/
		}
		
		if (!$pic)
		{
			$tpl->setCurrentBlock("blank");
			$tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.gif"));
			$tpl->parseCurrentBlock();
		}

		if ($this->output_icons)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"]."_s.gif", $a_option["type"], $a_obj_id));
			
			$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
			$this->iconList[] = "iconid_".$a_node_id;
			$tpl->setVariable("TXT_ALT_IMG",
				$this->getImageAlt($lng->txt("icon")." ".$lng->txt($a_option["desc"]), $a_option["type"], $a_obj_id));
			$tpl->parseCurrentBlock();
		}
		
		if(strlen($sel = $this->buildSelect($a_node_id,$a_option['type'])))
		{
			$tpl->setCurrentBlock('select');
			$tpl->setVariable('OBJ_SEL',$sel);
			$tpl->parseCurrentBlock();
		}

		if ($this->isClickable($a_option["type"], $a_node_id,$a_obj_id))	// output link
		{
			$tpl->setCurrentBlock("link");
			//$target = (strpos($this->target, "?") === false) ?
			//	$this->target."?" : $this->target."&";
			//$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
				
			$style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);
			
			if ($style_class != "")
			{
				$tpl->setVariable("A_CLASS", ' class="'.$style_class.'" ' );
			}

			if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "")
			{
				$tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
			}

			//$tpl->setVariable("LINK_NAME", $a_node_id);
			$tpl->setVariable("TITLE", ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
				$this->textwidth, true));
			$tpl->setVariable("DESC", ilUtil::shortenText(
				$this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]), $this->textwidth, true));
			$frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
			if ($frame_target != "")
			{
				$tpl->setVariable("TARGET", " target=\"".$frame_target."\"");
			}
			$tpl->parseCurrentBlock();
		}
		else			// output text only
		{
			$tpl->setCurrentBlock("text");
			$tpl->setVariable("OBJ_TITLE", ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]), $this->textwidth, true));
			$tpl->setVariable("OBJ_DESC", ilUtil::shortenText(
				$this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]), $this->textwidth, true));			
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("list_item");
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("element");
	}

	/**
	* get image path (may be overwritten by derived classes)
	*/
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		return ilUtil::getImagePath($a_name);
	}
	
	/**
	* get image alt text
	*/
	function getImageAlt($a_default_text, $a_type = "", $a_obj_id = "")
	{
		return $a_default_text;
	}
	
	/**
	* get style class for node
	*/
	function getNodeStyleClass($a_id, $a_type)
	{
		if ($a_id == $this->highlighted)
		{
			return "il_HighlightedNode";
		}
		return "";
	}

	/**
	* get link target (may be overwritten by derived classes)
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		$target = (strpos($this->target, "?") === false)
			? $this->target."?"
			: $this->target."&";
		return $target.$this->target_get."=".$a_node_id.$this->params_get;
	}

	/**
	* get onclick event handling (may be overwritten by derived classes)
	*/
	function buildOnClick($a_node_id, $a_type, $a_title)
	{
		return "";
	}

	/**
	* standard implementation for title, may be overwritten by derived classes
	*/
	function buildTitle($a_title, $a_id, $a_type)
	{
		return $a_title;
	}

	/**
	* standard implementation for description, may be overwritten by derived classes
	*/
	function buildDescription($a_desc, $a_id, $a_type)
	{
		return "";
	}
	
	/**
	* standard implementation for adding an option select box between image and title
	*/
	function buildSelect($a_node_id,$a_type)
	{
		return "";
	}
	

	/**
	* get frame target (may be overwritten by derived classes)
	*/
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return $this->frame_target;
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
		return $this->expand_target.$sep.$this->expand_variable."=".$a_node_id.$this->params_get."#".abs($a_node_id);
	}

	/**
	* set target
	* frame or not frame?
	* @param	string
	* @access	public
	*/
	function setFrameTarget($a_target)
	{
		$this->frame_target = $a_target;
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
		if(!is_array($this->format_options))
		{
			return -1;
		}
		
		foreach ($this->format_options as $key => $value)
		{
			if (($value["child"] == $a_data["parent"]))
			{
				return $key;
			}
		}
		
		return -1;
		// exit on error
		#$this->ilias->raiseError(get_class($this)."::getIndex(): Error in tree. No index found!",$this->ilias->error_obj->FATAL);
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
	* removes item from the filter
	* @access	public
	* @param	string		object type to remove
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
			$_SESSION[$this->expand_variable] = array($this->getRoot());
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
	* force expandAll. if true all nodes are expanded regardless of the values
	* in $expanded (default: false)
	* @access	public
	* @param	boolean
	*/
	function forceExpandAll($a_mode, $a_show_minus = true)
	{
		$this->expand_all = (bool) $a_mode;
		$this->show_minus = $a_show_minus;
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
			if (in_array($a_item, $this->filter))
			{
				$ret = true;
			}
			else
			{
				$ret = false;
			}
		}
		else
		{
			$ret = false;
		}

		if ($this->getFilterMode() == IL_FM_NEGATIVE )
		{
			return $ret;
		}
		else
		{
			return !$ret;
		}
	}

	/**
	* sort nodes and put adm object to the end of sorted array
	* @access	private
	* @param	array	node list as returned by iltree::getChilds();
	* @return	array	sorted nodes
	*/
	function sortNodes($a_nodes,$a_parent_obj_id)
	{
		foreach ($a_nodes as $key => $node)
		{
			if ($node["type"] == "adm")
			{
				$match = $key;
				$adm_node = $node;
				break;
			}
		}

		// cut off adm node
		isset($match) ? array_splice($a_nodes,$match,1) : "";

		$a_nodes = ilUtil::sortArray($a_nodes,$this->order_column,$this->order_direction);

		// append adm node to end of list
		isset ($match) ? array_push($a_nodes,$adm_node) : "";

		return $a_nodes;
	}
} // END class.ilExplorer
?>
