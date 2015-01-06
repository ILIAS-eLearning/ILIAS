<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFormGUI.php");

/**
* This class represents a hierarchical form. These forms are used for
* quick editing, where each node is represented by it's title.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilHierarchyFormGUI extends ilFormGUI
{
	/**
	* Constructor
	*
	* @param
	*/
	function __construct()
	{
		global $lng, $tpl;
		
		$this->maxdepth = -1;
		$this->multi_commands = array();
		$this->commands = array();
		$this->drag_target[] = array();
		$this->drag_content[] = array();
		$lng->loadLanguageModule("form");
		$this->setCheckboxName("cbox");
		parent::ilFormGUI();
		$this->help_items = array();
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initDragDrop();
		$tpl->addJavascript("./Services/Form/js/ServiceFormHierarchyForm.js");
	}

	/**
	* Set parent gui object/cmd
	*
	* This is needed, if the expand feature is used.
	*/
	function setParentCommand($a_parent_obj, $a_parent_cmd)
	{
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
	}
	
	/**
	* Get Parent object
	*
	* @return	object		parent gui object
	*/
	function getParentObject()
	{
		return $this->parent_obj;
	}
	
	/**
	* Get parent command
	*
	* @return	string		parent command
	*/
	function getParentCommand()
	{
		return $this->parent_cmd;
	}

	/**
	* Set Id. Currently not possible, due to js handling (ID must always be "hform")
	*
	* @param	string	$a_id	Id
	*/
	function setId($a_id)
	{
		die("ilHierarchyFormGUI does currently not support multiple forms (multiple IDs). ID is always hform.");
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	function getId()
	{
		return "hform";
	}

	/**
	* Set Tree Object.
	*
	* @param	object	$a_tree	Tree Object
	*/
	function setTree($a_tree)
	{
		$this->tree = $a_tree;
	}

	/**
	* Get Tree Object.
	*
	* @return	object	Tree Object
	*/
	function getTree()
	{
		return $this->tree;
	}

	/**
	* Set Current Top Node ID.
	*
	* @param	string	$a_currenttopnodeid	Current Top Node ID
	*/
	function setCurrentTopNodeId($a_currenttopnodeid)
	{
		$this->currenttopnodeid = $a_currenttopnodeid;
	}

	/**
	* Get Current Top Node ID.
	*
	* @return	string	Current Top Node ID
	*/
	function getCurrentTopNodeId()
	{
		return $this->currenttopnodeid;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Icon.
	*
	* @param	string	$a_icon	Icon
	*/
	function setIcon($a_icon)
	{
		$this->icon = $a_icon;
	}

	/**
	* Get Icon.
	*
	* @return	string	Icon
	*/
	function getIcon()
	{
		return $this->icon;
	}

	/**
	* Set Checkbox Name.
	*
	* @param	string	$a_checkboxname	Checkbox Name
	*/
	function setCheckboxName($a_checkboxname)
	{
		$this->checkboxname = $a_checkboxname;
	}

	/**
	* Get Checkbox Name.
	*
	* @return	string	Checkbox Name
	*/
	function getCheckboxName()
	{
		return $this->checkboxname;
	}

	/**
	* Set Drag Icon Path.
	*
	* @param	string	$a_dragicon	Drag Icon Path
	*/
	function setDragIcon($a_dragicon)
	{
		$this->dragicon = $a_dragicon;
	}

	/**
	* Get Drag Icon Path.
	*
	* @return	string	Drag Icon Path
	*/
	function getDragIcon()
	{
		return $this->dragicon;
	}

	/**
	* Set Maximum Depth.
	*
	* @param	int	$a_maxdepth	Maximum Depth
	*/
	function setMaxDepth($a_maxdepth)
	{
		$this->maxdepth = $a_maxdepth;
	}

	/**
	* Get Maximum Depth.
	*
	* @return	int	Maximum Depth
	*/
	function getMaxDepth()
	{
		return $this->maxdepth;
	}

	/**
	* Set Explorer Updater
	*
	* @param	object	$a_tree	Tree Object
	*/
	function setExplorerUpdater($a_exp_frame, $a_exp_id, $a_exp_target_script)
	{
		$this->exp_frame = $a_exp_frame;
		$this->exp_id = $a_exp_id;
		$this->exp_target_script = $a_exp_target_script;
	}
	
	/**
	* Set Explorer Updater
	*
	* @param	object	$a_tree	Tree Object
	*/
	function setTriggeredUpdateCommand($a_triggered_update_command)
	{
		$this->triggered_update_command = $a_triggered_update_command;
	}

	/**
	* Get all help items
	*/
	function addHelpItem($a_text, $a_image = "")
	{
		$this->help_items[] = array("text" => $a_text,
			"image" => $a_image);
	}

	/**
	* Get all help items
	*/
	function getHelpItems()
	{
		return $this->help_items;
	}
	
	/**
	* Makes a nodes (following droparea) a drag target
	*
	* @param	string	$a_id		node ID
	* @param	string	$a_group	drag and drop group
	*/
	function makeDragTarget($a_id, $a_group, $a_first_child_drop_area = false, $a_as_subitem = false, $a_diss_text = "")
	{
		if ($a_first_child_drop_area == true)		// first child drop areas only insert as subitems
		{
			$a_as_subitem = true;
		}
		
		if ($a_id != "")
		{
			if ($a_first_child_drop_area)
			{
				$a_id.= "fc";
			}
			
			$this->drag_target[] = array("id" => $a_id, "group" => $a_group);
			$this->diss_menues[$a_id][$a_group][] = array("subitem" => $a_as_subitem, "text" => $a_diss_text);
		}
	}
	
	/**
	* Makes a node a drag content
	*
	* @param	string	$a_id		node ID
	* @param	string	$a_group	drag and drop group
	*/
	function makeDragContent($a_id, $a_group)
	{
		if ($a_id != "")
		{
			$this->drag_content[] = array("id" => $a_id, "group" => $a_group);
		}
	}

	/**
	* Add a multi command (for selection of items)
	*
	* @param	string	$a_txt	command text
	* @param	string	$a_cmd	command
	*/
	function addMultiCommand($a_txt, $a_cmd)
	{
		$this->multi_commands[] = array("text" => $a_txt, "cmd" => $a_cmd);
	}

	/**
	* Add a command
	*
	* @param	string	$a_txt	command text
	* @param	string	$a_cmd	command
	*/
	function addCommand($a_txt, $a_cmd)
	{
		$this->commands[] = array("text" => $a_txt, "cmd" => $a_cmd);
	}
	
	/**
	* Set highlighted nodes
	*
	* @param	array		highlighted nodes
	*/
	function setHighlightedNodes($a_val)
	{
		$this->highlighted_nodes = $a_val;
	}
	
	/**
	* Get highlighted nodes.
	*
	* @return	array		highlighted nodes
	*/
	function getHighlightedNodes()
	{
		return $this->highlighted_nodes;
	}
	
	/**
	* Set focus if
	*
	* @param	int		node id
	*/
	function setFocusId($a_val)
	{
		$this->focus_id = $a_val;
	}
	
	/**
	* Get focus id
	*
	* @return	int		node id
	*/
	function getFocusId()
	{
		return $this->focus_id;
	}
	
	/**
	* Set expand variable
	*
	* @param	
	*/
	function setExpandVariable($a_val)
	{
		$this->expand_variable = $a_val;
	}
	
	/**
	* Get expand variable
	*
	* @return	
	*/
	function getExpandVariable()
	{
		return $this->expand_variable;
	}
	
	/**
	* Set expanded Array
	*
	* @param	array	expanded array
	*/
	function setExpanded($a_val)
	{
		$this->expanded = $a_val;
	}
	
	/**
	* Get expanded array
	*
	* @return	array	expanded array
	*/
	function getExpanded()
	{
		return $this->expanded;
	}
	
	/**
	* Update expand information in session
	*
	* @param	string		node id
	*/
	function updateExpanded()
	{
		$ev = $this->getExpandVariable();

		if ($ev == "")
		{
			return;
		}
		
		// init empty session
		if (!is_array($_SESSION[$ev]))
		{
			$_SESSION[$ev] = array($this->getTree()->getRootId());
		}

		if ($_POST["il_hform_expand"] != "")
		{
			$node_id = $_POST["il_hform_expand"];
		}
		if ($_GET[$ev] != "")
		{
			$node_id = $_GET[$ev];
		}
		
		// if positive => expand this node
		if ($node_id > 0 && !in_array($node_id,$_SESSION[$ev]))
		{
			array_push($_SESSION[$ev], $node_id);
		}
		// if negative => compress this node
		if ($node_id < 0)
		{
			$key = array_keys($_SESSION[$ev],-(int) $node_id);
			unset($_SESSION[$ev][$key[0]]);
		}
		$this->setExpanded($_SESSION[$ev]);
	}

	/**
	 * Set type whitelist
	 *
	 * @param array $a_val white list of types	
	 */
	function setTypeWhiteList($a_val)
	{
		$this->white_list = $a_val;
	}
	
	/**
	 * Get type whitelist
	 *
	 * @return array white list of types
	 */
	function getTypeWhiteList()
	{
		return $this->white_list;
	}

	/**
	* Get all childs of current node. Standard implementation uses
	* tree object.
	*/
	function getChilds($a_node_id = false)
	{
		if ($a_node_id == false)
		{
			$a_node_id = $this->getCurrentTopNodeId();
		}
		
		$tree_childs = $this->getTree()->getChilds($a_node_id);
		$childs = array();
		foreach($tree_childs as $tree_child)
		{
			
			if (!is_array($this->white_list) || in_array($tree_child["type"], $this->white_list))
			{
				$childs[] = array("node_id" => $tree_child["child"],
					"title" => $tree_child["title"],
					"type" => $tree_child["type"],
					"depth" => $tree_child["depth"]
					);
			}
		}
		
		return $childs;
	}
	
	/**
	* Get Form Content
	*/
	function getContent()
	{
		global $lng;
		
		if ($this->getExpandVariable() != "")
		{
			$this->updateExpanded();
		}
		
		$ttpl = new ilTemplate("tpl.hierarchy_form.html", true, true, "Services/Form");
		$ttpl->setVariable("TXT_SAVING", $lng->txt("saving"));
		$top_node_data = $this->getTree()->getNodeData($this->getCurrentTopNodeId());
		$top_node = array("node_id" => $top_node_data["child"],
				"title" => $top_node_data["title"],
				"type" => $top_node_data["type"]);

		$childs = null;
		$nodes_html = $this->getLevelHTML($top_node, 0, $childs);


		// commands
		$secs = array("1", "2");
		foreach ($secs as $sec)
		{
			reset($this->commands);
			reset($this->multi_commands);
			if (count($this->multi_commands) > 0 || count($this->commands) > 0)
			{
				if (count($childs) > 0)
				{
					$single = false;
					foreach($this->commands as $cmd)
					{
						$ttpl->setCurrentBlock("cmd".$sec);
						$ttpl->setVariable("CMD", $cmd["cmd"]);
						$ttpl->setVariable("CMD_TXT", $cmd["text"]);
						$ttpl->parseCurrentBlock();
						$single = true;
					}
	
					$multi = false;
					foreach($this->multi_commands as $cmd)
					{
						$ttpl->setCurrentBlock("multi_cmd".$sec);
						$ttpl->setVariable("MULTI_CMD", $cmd["cmd"]);
						$ttpl->setVariable("MULTI_CMD_TXT", $cmd["text"]);
						$ttpl->parseCurrentBlock();
						$multi = true;
					}
					if ($multi)
					{
						$ttpl->setCurrentBlock("multi_cmds".$sec);
						$ttpl->setVariable("MCMD_ALT", $lng->txt("commands"));
						if ($sec == "1")
						{
							$ttpl->setVariable("MCMD_IMG", ilUtil::getImagePath("arrow_downright.svg"));
						}
						else
						{
							$ttpl->setVariable("MCMD_IMG", ilUtil::getImagePath("arrow_upright.svg"));
						}
						$ttpl->parseCurrentBlock();
					}
				}
				
				if ($single || $multi)
				{
					$ttpl->setCurrentBlock("commands".$sec);
					$ttpl->parseCurrentBlock();
				}
				$single = true;
			}
		}

		// explorer updater
		if ($this->exp_frame != "")
		{
			$ttpl->setCurrentBlock("updater");
			$ttpl->setVariable("UPDATER_FRAME", $this->exp_frame);
			$ttpl->setVariable("EXP_ID_UPDATER", $this->exp_id);
			$ttpl->setVariable("HREF_UPDATER", $this->exp_target_script);
			$ttpl->parseCurrentBlock();
		}

		// drag and drop initialisation
		foreach($this->drag_target as $drag_target)
		{
			$ttpl->setCurrentBlock("dragtarget");
			$ttpl->setVariable("EL_ID", $drag_target["id"]);
			$ttpl->setVariable("GROUP", $drag_target["group"]);
			$ttpl->parseCurrentBlock();
		}
		foreach($this->drag_content as $drag_content)
		{
			$ttpl->setCurrentBlock("dragcontent");
			$ttpl->setVariable("EL_ID", $drag_content["id"]);
			$ttpl->setVariable("GROUP", $drag_content["group"]);
			$ttpl->parseCurrentBlock();
		}
		
		// disambiguation menues and "insert as first child" flags
		if (is_array($this->diss_menues))
		{
			foreach($this->diss_menues as $node_id => $d_menu)
			{
				foreach($d_menu as $group => $menu)
				{
					if (count($menu) > 1)
					{
						foreach($menu as $menu_item)
						{
							$ttpl->setCurrentBlock("dmenu_cmd");
							$ttpl->setVariable("SUBITEM", (int) $menu_item["subitem"]);
							$ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
							$ttpl->parseCurrentBlock();
						}
						
						$ttpl->setCurrentBlock("disambiguation_menu");
						$ttpl->setVariable("DNODE_ID", $node_id);
						$ttpl->setVariable("GRP", $group);
						$ttpl->parseCurrentBlock();
					}
					else if (count($menu) == 1)
					{
						// set first child flag
						$ttpl->setCurrentBlock("as_subitem_flag");
						$ttpl->setVariable("SI_NODE_ID", $node_id);
						$ttpl->setVariable("SI_GRP", $group);
						$ttpl->setVariable("SI_SI", (int) $menu[0]["subitem"]);
						$ttpl->parseCurrentBlock();
						
					}
				}
			}
		}
		$this->diss_menues[$a_id][$a_group][] = array("type" => $a_type, "text" => $a_diss_text);


		if ($this->triggered_update_command != "")
		{
			$ttpl->setCurrentBlock("tr_update");
			$ttpl->setVariable("UPDATE_CMD", $this->triggered_update_command);
			$ttpl->parseCurrentBlock();
		}

		// disambiguation menues and "insert as first child" flags
		if (is_array($this->diss_menues))
		{
			foreach($this->diss_menues as $node_id => $d_menu)
			{
				foreach($d_menu as $group => $menu)
				{
					if (count($menu) > 1)
					{
						foreach($menu as $menu_item)
						{
							$ttpl->setCurrentBlock("dmenu_cmd");
							$ttpl->setVariable("SUBITEM", (int) $menu_item["subitem"]);
							$ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
							$ttpl->parseCurrentBlock();
						}
						
						$ttpl->setCurrentBlock("disambiguation_menu");
						$ttpl->setVariable("DNODE_ID", $node_id);
						$ttpl->setVariable("GRP", $group);
						$ttpl->parseCurrentBlock();
					}
					else if (count($menu) == 1)
					{
						// set first child flag
						$ttpl->setCurrentBlock("as_subitem_flag");
						$ttpl->setVariable("SI_NODE_ID", $node_id);
						$ttpl->setVariable("SI_GRP", $group);
						$ttpl->setVariable("SI_SI", (int) $menu[0]["subitem"]);
						$ttpl->parseCurrentBlock();
						
					}
				}
			}
		}
		$this->diss_menues[$a_id][$a_group][] = array("type" => $a_type, "text" => $a_diss_text);
		
		// nodes
		$ttpl->setVariable("NODES", $nodes_html);
		
		// title
//echo "<br>".htmlentities($this->getTitle())." --- ".htmlentities(ilUtil::prepareFormOutput($this->getTitle()));
		$ttpl->setVariable("TITLE", $this->getTitle());
		
		
		return $ttpl->get();
	}

	/**
	 * Get Legend
	 *
	 * @return string legend html
	 */
	function getLegend()
	{
		global $lng;

		include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");

		$ttpl = new ilTemplate("tpl.hierarchy_form_legend.html", true, true, "Services/Form");
		if ($this->getDragIcon() != "")
		{
			$ttpl->setCurrentBlock("help_drag");
			$ttpl->setVariable("IMG_DRAG", $this->getDragIcon());
			$ttpl->setVariable("DRAG_ARROW",
				ilGlyphGUI::get(ilGlyphGUI::DRAG));
			$ttpl->setVariable("TXT_DRAG",
				$lng->txt("form_hierarchy_drag_drop_help"));
			$ttpl->setVariable("PLUS", ilGlyphGUI::get(ilGlyphGUI::ADD));
			$ttpl->parseCurrentBlock();
		}

		// additional help items
		foreach ($this->getHelpItems() as $help)
		{
			if ($help["image"] != "")
			{
				$ttpl->setCurrentBlock("help_img");
				$ttpl->setVariable("IMG_HELP", $help["image"]);
				$ttpl->parseCurrentBlock();
			}
			$ttpl->setCurrentBlock("help_item");
			$ttpl->setVariable("TXT_HELP", $help["text"]);
			$ttpl->parseCurrentBlock();
		}

		$ttpl->setVariable("TXT_ADD_EL",
			$lng->txt("form_hierarchy_add_elements"));
		$ttpl->setVariable("PLUS2", ilGlyphGUI::get(ilGlyphGUI::ADD));

		return $ttpl->get();
	}


	/**
	* Get Form HTML
	*/
	function getLevelHTML($a_par_node, $a_depth, &$a_childs)
	{
		global $lng;
		
		if ($this->getMaxDepth() > -1 && $this->getMaxDepth() < $a_depth)
		{
			return "";
		}

		$childs = $this->getChilds($a_par_node["node_id"]);
		$a_childs = $childs;
		$html = "";
		$last_child = null;
		$ttpl = new ilTemplate("tpl.hierarchy_form_nodes.html", true, true, "Services/Form");

		// prepended drop area
		if ($this->nodeAllowsChilds($a_par_node) && (count($childs) > 0 || $a_depth == 0))
		{
			$ttpl->setCurrentBlock("drop_area");
			$ttpl->setVariable("DNODE_ID", $a_par_node["node_id"]."fc");		// fc means "first child"
			$ttpl->setVariable("IMG_BLANK", ilUtil::getImagePath("spacer.png"));
			if (count($childs) == 0)
			{
				$ttpl->setVariable("NO_CONTENT_CLASS", "ilCOPGNoPageContent");
				$ttpl->setVariable("NO_CONTENT_TXT", " &nbsp;".$lng->txt("form_hier_click_to_add"));
			}
			$ttpl->parseCurrentBlock();
	
			$this->manageDragAndDrop($a_par_node, $a_depth, true, null, $childs);
			$menu_items = $this->getMenuItems($a_par_node, $a_depth, true, null, $childs);
//var_dump($menu_items);
			if (count($menu_items) > 0)
			{
				// determine maximum of multi add numbers
				$max = 1;
				foreach($menu_items as $menu_item)
				{
					if ($menu_item["multi"] > $max)
					{
						$max = $menu_item["multi"];
					}
				}
				
				reset($menu_items);
				$mcnt = 1;
				foreach($menu_items as $menu_item)
				{
					if ($menu_item["multi"] > 1)
					{
						for($i = 1; $i <= $menu_item["multi"]; $i++)
						{
							$ttpl->setCurrentBlock("multi_add");
							$ttpl->setVariable("MA_NUM", $i);
							$ttpl->setVariable("MENU_CMD", $menu_item["cmd"]);
							$ttpl->setVariable("FC", "1");
							$ttpl->setVariable("CMD_NODE", $a_par_node["node_id"]);
							$ttpl->setVariable("MCNT", $mcnt."fc");
							$ttpl->parseCurrentBlock();
						}
					}
					
					// buffer td for lower multis
					if ($max > $menu_item["multi"])
					{
						$ttpl->setCurrentBlock("multi_buffer");
						$ttpl->setVariable("BUF_SPAN", $max - $menu_item["multi"]);
						$ttpl->parseCurrentBlock();
					}
					$ttpl->setCurrentBlock("menu_cmd");
					$ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
					$ttpl->setVariable("MENU_CMD", $menu_item["cmd"]);
					$ttpl->setVariable("CMD_NODE", $a_par_node["node_id"]);
					$ttpl->setVariable("FC", "1");
					$ttpl->setVariable("MCNT", $mcnt."fc");
					$ttpl->parseCurrentBlock();
					$mcnt++;
				}
				$ttpl->setCurrentBlock("drop_area_menu");
				$ttpl->setVariable("MNODE_ID", $a_par_node["node_id"]."fc");
				$ttpl->parseCurrentBlock();
	
				$ttpl->setCurrentBlock("element");
				$ttpl->parseCurrentBlock();
			}
		}
		
		// insert childs
		if (count($childs) > 0)
		{
			for($i = 0; $i < count($childs); $i++)
			{
				$next_sibling = ($i < (count($childs) - 1))
					? $next_sibling = $childs[$i+1]
					: null;

				$this->renderChild($ttpl, $childs[$i], $a_depth, $next_sibling);
				$last_child = $child;
			}
		}

		$html = $ttpl->get();
		unset($ttpl);
		
		return $html;
	}
	
	/**
	* Render a single child (including grandchilds)
	*/
	function renderChild($a_tpl, $a_child, $a_depth, $next_sibling = null)
	{
		global $ilCtrl;
		
		// image
		$a_tpl->setCurrentBlock("img");
		$a_tpl->setVariable("IMGPATH", $this->getChildIcon($a_child));
		$a_tpl->setVariable("IMGALT", $this->getChildIconAlt($a_child));
		$a_tpl->setVariable("IMG_NODE", $a_child["node_id"]);
		$a_tpl->setVariable("NODE_ID", $a_child["node_id"]);
		$a_tpl->setVariable("TYPE", $a_child["type"]);
		$a_tpl->parseCurrentBlock();
		
		// checkbox
		$a_tpl->setCurrentBlock("cbox");
		$a_tpl->setVariable("CNODE_ID", $a_child["node_id"]);
		$a_tpl->setVariable("CBOX_NAME", $this->getCheckboxName());
		$a_tpl->parseCurrentBlock();
		
		// node info
		if (($info = $this->getChildInfo($a_child)) != "")
		{
			$a_tpl->setCurrentBlock("node_info");
			$a_tpl->setVariable("NODE_INFO", $info);
			$a_tpl->parseCurrentBlock();
		}
		
		// commands of child node
		$child_commands = $this->getChildCommands($a_child);
		if (is_array($child_commands))
		{
			foreach($child_commands as $command)
			{
				$a_tpl->setCurrentBlock("node_cmd");
				$a_tpl->setVariable("HREF_NODE_CMD", $command["link"]);
				$a_tpl->setVariable("TXT_NODE_CMD", $command["text"]);
				$a_tpl->parseCurrentBlock();
			}
		}
		
		// title
		$a_tpl->setCurrentBlock("text");
		$hl = $this->getHighlightedNodes();
		if (is_array($hl) && in_array($a_child["node_id"], $hl))
		{
			$a_tpl->setVariable("CLASS", ' class="ilHFormHighlighted" ');
		}
		$a_tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($this->getChildTitle($a_child)));
		$a_tpl->setVariable("TNODE_ID", $a_child["node_id"]);
		$a_tpl->parseCurrentBlock();
		$grandchilds = null;
		$grandchilds_html = $this->getLevelHTML($a_child, $a_depth + 1, $grandchilds);
		
		// focus
		if ($this->getFocusId() == $a_child["node_id"])
		{
			$a_tpl->setCurrentBlock("focus");
			$a_tpl->setVariable("FNODE_ID", $a_child["node_id"]);
			$a_tpl->parseCurrentBlock();
		}
		
		// expander
		if ($this->getExpandVariable() != "")
		{
			$a_tpl->setCurrentBlock("expand_icon");
			if (!is_null($grandchilds) && count($grandchilds) > 0)
			{
				if (!in_array($a_child["node_id"],$this->getExpanded()))
				{
					$ilCtrl->setParameter($this->getParentObject(), $this->getExpandVariable(), $a_child["node_id"]);
					$a_tpl->setVariable("IMG_EXPAND", ilUtil::getImagePath("browser/plus.png"));
					$a_tpl->setVariable("HREF_NAME", "n".$a_child["node_id"]);
					$a_tpl->setVariable("HREF_EXPAND",
						$ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCommand(), "n".$a_child["node_id"]));
					$grandchilds_html = "";
				}
				else
				{
					$ilCtrl->setParameter($this->getParentObject(), $this->getExpandVariable(), -$a_child["node_id"]);
					$a_tpl->setVariable("IMG_EXPAND", ilUtil::getImagePath("browser/minus.png"));
					$a_tpl->setVariable("HREF_NAME", "n".$a_child["node_id"]);
					$a_tpl->setVariable("HREF_EXPAND",
						$ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCommand(), "n".$a_child["node_id"]));
				}
				$ilCtrl->setParameter($this->getParentObject(), $this->getExpandVariable(), "");
			}
			else
			{
				$a_tpl->setVariable("IMG_EXPAND", ilUtil::getImagePath("spacer.png"));
			}
			$a_tpl->parseCurrentBlock();
		}
		
		// childs
		$a_tpl->setCurrentBlock("list_item");
		$a_tpl->setVariable("CHILDS", $grandchilds_html);
		$a_tpl->parseCurrentBlock();
		
		$a_tpl->setCurrentBlock("element");
		$a_tpl->parseCurrentBlock();
		
		// drop area after child
		$a_tpl->setCurrentBlock("drop_area");
		$a_tpl->setVariable("DNODE_ID", $a_child["node_id"]);
		$a_tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("spacer.png"));
		$a_tpl->parseCurrentBlock();

		// manage drag and drop areas
		$this->manageDragAndDrop($a_child, $a_depth, false, $next_sibling, $grandchilds);
		
		// drop area menu
		$menu_items = $this->getMenuItems($a_child, $a_depth, false, $next_sibling, $grandchilds);
		if (count($menu_items) > 0)
		{
			// determine maximum of multi add numbers
			$max = 1;
			foreach($menu_items as $menu_item)
			{
				if ($menu_item["multi"] > $max)
				{
					$max = $menu_item["multi"];
				}
			}
			
			reset($menu_items);
			$mcnt = 1;
			foreach($menu_items as $menu_item)
			{
				if ($menu_item["multi"] > 1 )
				{
					for($i = 1; $i <= $menu_item["multi"]; $i++)
					{
						$a_tpl->setCurrentBlock("multi_add");
						$a_tpl->setVariable("MA_NUM", $i);
						$a_tpl->setVariable("MENU_CMD", $menu_item["cmd"]);
						if ($menu_item["as_subitem"])
						{
							$a_tpl->setVariable("FC", "1");
							$a_tpl->setVariable("MCNT", $mcnt."fc");
						}
						else
						{
							$a_tpl->setVariable("FC", "0");
							$a_tpl->setVariable("MCNT", $mcnt);
						}
						$a_tpl->setVariable("CMD_NODE", $a_child["node_id"]);
						$a_tpl->parseCurrentBlock();
					}
				}
				
				// buffer td for lower multis
				if ($max > $menu_item["multi"])
				{
					$a_tpl->setCurrentBlock("multi_buffer");
					$a_tpl->setVariable("BUF_SPAN", $max - $menu_item["multi"]);
					$a_tpl->parseCurrentBlock();
				}
				
				$a_tpl->setCurrentBlock("menu_cmd");
				$a_tpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
				$a_tpl->setVariable("MENU_CMD", $menu_item["cmd"]);
				if ($menu_item["as_subitem"])
				{
					$a_tpl->setVariable("FC", "1");
					$a_tpl->setVariable("MCNT", $mcnt."fc");
				}
				else
				{
					$a_tpl->setVariable("FC", "0");
					$a_tpl->setVariable("MCNT", $mcnt);
				}
				$a_tpl->setVariable("CMD_NODE", $a_child["node_id"]);
				$a_tpl->parseCurrentBlock();
				$mcnt++;
			}
			$a_tpl->setCurrentBlock("drop_area_menu");
			$a_tpl->setVariable("MNODE_ID", $a_child["node_id"]);
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("element");
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	* Get icon path for an item.
	*
	* @param	array		item array
	* @return	string		icon path
	*/
	function getChildIcon($a_item)
	{
		return ilUtil::getImagePath("icon_".$a_item["type"].".svg");
	}
	
	/**
	* Get icon alt text for an item.
	*
	* @param	array		item array
	* @return	string		icon alt text
	*/
	function getChildIconAlt($a_item)
	{
		global $lng;
		
		return $lng->txt($a_item["type"]);
	}

	/**
	* Get item commands
	*
	* @param	array		item array
	* @return	array		array of arrays("text", "link")
	*/
	function getChildCommands($a_item)
	{
		return false;
	}

	/**
	 * Get child title
	 *
	 * @param array $a_child node array
	 * @return string node title
	 */
	function getChildTitle($a_child)
	{
		return $a_child["title"];
	}	
	
	/**
	 * Get child info
	 *
	 * @param array $a_child node array
	 * @return string node title
	 */
	function getChildInfo($a_child)
	{
		return "";
	}		
	
	/**
	* Get menu items for drop area of node.
	*
	* This function will be most likely overwritten by sub class
	*
	* @param	array	$a_child		node array ("title", "node_id", "type")
	* @param	boolean	$a_first_child	if false, the menu of the drop area
	*									right after the node (same level) is set
	*									if true, the menu of the drop area before
	*									the first child (if nodes are allowed)
	*									of the node is set
	*/
	function getMenuItems($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs = null)
	{
		return array();
	}
	
	/**
	* Checks, whether current nodes allows childs at all.
	* Should be overwritten.
	*/
	function nodeAllowsChilds($a_node)
	{
		return true;
	}
	
	/**
	* Makes nodes drag and drop content and targets.
	* Must be overwritten to support drag and drop.
	*
	* @param	object	$a_node		node array
	*/
	function manageDragAndDrop($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs = null)
	{
		//$this->makeDragTarget($a_node["id"], $a_group);
		//$this->makeDragTarget($a_node["id"], $a_group);
	}

	/**
	* Get multi number of _POST input
	*/
	static function getPostMulti()
	{
		return (int) ($_POST["il_hform_multi"] > 1
			? $_POST["il_hform_multi"]
			: 1);
	}
	
	/**
	* Get node ID of _POST input
	*/
	static function getPostNodeId()
	{
		return $_POST["il_hform_node"];
	}

	/**
	* Should node be inserted as first child of target node (true) or as successor (false)
	*/
	static function getPostFirstChild()
	{
		return (((int) $_POST["il_hform_fc"]) == 1);
	}

	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		return parent::getHTML().$this->getLegend();
	}

	
}
