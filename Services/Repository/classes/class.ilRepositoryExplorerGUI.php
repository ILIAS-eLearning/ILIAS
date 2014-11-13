<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Repository explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @todo: isClickable, top node id
 *
 * @ingroup ServicesRepository
 */
class ilRepositoryExplorerGUI extends ilTreeExplorerGUI
{
	protected $type_grps = array();
	protected $session_materials = array();
	
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $tree, $ilSetting, $objDefinition;

		$this->cur_ref_id = (int) $_GET["ref_id"];
		
		$this->top_node_id = 0;
		if ($ilSetting->get("rep_tree_limit_grp_crs") && $this->cur_ref_id > 0)
		{
			$path = $tree->getPathId($this->cur_ref_id);
			foreach ($path as $n)
			{
				if ($top_node > 0)
				{
					break;
				}
				if (in_array(ilObject::_lookupType(ilObject::_lookupObjId($n)),
					array("crs", "grp")))
				{
					$this->top_node_id = $n;
				}
			}
			
		}
		
		parent::__construct("rep_exp", $a_parent_obj, $a_parent_cmd, $tree);

		$this->setSkipRootNode(false);
		$this->setAjax(true);
		$this->setOrderField("title");

		if ($ilSetting->get("repository_tree_pres") == "" ||
			($ilSetting->get("rep_tree_limit_grp_crs") && $this->top_node_id == 0))
		{
			$this->setTypeWhiteList(array("root", "cat", "catr", "grp", "icrs",
				"crs", "crsr", "rcrs"));
		}
		else if ($ilSetting->get("repository_tree_pres") == "all_types")
		{
			$white = array();
			foreach ($objDefinition->getSubObjectsRecursively("root") as $rtype)
			{
				if (/* $rtype["name"] != "itgr" && */ !$objDefinition->isSideBlock($rtype["name"]))
				{
					$white[] = $rtype["name"];
				}
			}
			$this->setTypeWhiteList($white);
		}
		if ((int) $_GET["ref_id"] > 0)
		{
			$this->setPathOpen((int) $_GET["ref_id"]);
		}
	}

	/**
	 * Get root node
	 *
	 * @param
	 * @return
	 */
	function getRootNode()
	{
		if ($this->top_node_id > 0)
		{
			return $this->getTree()->getNodeData($this->top_node_id);
		}
		else
		{
			return parent::getRootNode();
		}
	}

	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeContent($a_node)
	{
		global $lng;
		
		$title = $a_node["title"];
						
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
		}
		else if($a_node["type"] == "sess" && 
			!trim($title))
		{
			// #14367 - see ilObjSessionListGUI
			include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
			$app_info = ilSessionAppointment::_lookupAppointment($a_node["obj_id"]); 	
			$title = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'],$app_info['fullday']);
		}		
		
		return $title;
	}
	
	/**
	 * Get node icon
	 *
	 * @param array 
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		$obj_id = ilObject::_lookupObjId($a_node["child"]);
		return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
	}

	/**
	 * Get node icon alt text
	 *
	 * @param array node array
	 * @return string alt text
	 */
	function getNodeIconAlt($a_node)
	{
		global $lng;

		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			$title = $a_node["title"];
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
			return $lng->txt("icon")." ".$title;
		}

		
		return parent::getNodeIconAlt($a_node);
	}
	
	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $_GET["ref_id"] ||
			($_GET["ref_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode())))
		{
			return true;
		}
		return false;
	}	
	
	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		global $ilCtrl;

		switch($a_node["type"])
		{
			case "root":
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			case "cat":
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			case "catr":
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			case "grp":
				$ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjgroupgui"), "");
				$ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $_GET["ref_id"]);
				return $link;

			case "crs":
				$ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjcoursegui"), "view");
				$ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $_GET["ref_id"]);
				return $link;
				
			case "crsr":
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			case "icrs":
				$ilCtrl->setParameterByClass("ilobjilinccoursegui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjilinccoursegui"), "");
				$ilCtrl->setParameterByClass("ilobjilinccoursegui", "ref_id", $_GET["ref_id"]);
				return $link;

			case 'rcrs':
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			default:
				include_once('./Services/Link/classes/class.ilLink.php');
				return ilLink::_getStaticLink($a_node["child"], $a_node["type"], true);

		}
	}

	/**
	 * Is node visible
	 *
	 * @param
	 * @return
	 */
	function isNodeVisible($a_node)
	{
		global $ilAccess,$tree,$ilSetting;

		if (!$ilAccess->checkAccess('visible', '', $a_node["child"]))
		{
			return false;
		}
		
		$is_course = false;
		$container_parent_id = $tree->checkForParentType($a_node["child"], 'grp');
		if(!$container_parent_id)
		{
			$is_course = true;
			$container_parent_id = $tree->checkForParentType($a_node["child"], 'crs');
		}	
		if($container_parent_id)
		{
			// do not display session materials for container course/group
			if($ilSetting->get("repository_tree_pres")  == "all_types" && $container_parent_id != $a_node["child"])
			{
				// get container event items only once
				if(!isset($this->session_materials[$container_parent_id]))
				{
					include_once './Modules/Session/classes/class.ilEventItems.php';
					$this->session_materials[$container_parent_id] = ilEventItems::_getItemsOfContainer($container_parent_id);
				}			
				if(in_array($a_node["child"], $this->session_materials[$container_parent_id]))
				{
					return false;
				}
			}					
		}
		
		return true;		
	}
	
	/**
	 * Sort childs
	 *
	 * @param array $a_childs array of child nodes
	 * @param mixed $a_parent_node parent node
	 *
	 * @return array array of childs nodes
	 */
	function sortChilds($a_childs, $a_parent_node_id)
	{
		global $objDefinition;

		$parent_obj_id = ilObject::_lookupObjId($a_parent_node_id);
		
		if ($parent_obj_id > 0)
		{
			$parent_type = ilObject::_lookupType($parent_obj_id);
		}
		else
		{
			$parent_type  = "dummy";
			$this->type_grps["dummy"] = array("root" => "dummy");
		}

		if (empty($this->type_grps[$parent_type]))
		{
			$this->type_grps[$parent_type] =
				$objDefinition->getGroupedRepositoryObjectTypes($parent_type);
		}
										
		// #14465 - item groups 
		include_once('./Services/Object/classes/class.ilObjectActivation.php');									
		$group = array();		
		foreach ($a_childs as $child)
		{					
			// item group: get childs
			if ($child["type"] == "itgr")
			{
				$g = $child["child"];
				$items = ilObjectActivation::getItemsByItemGroup($g);
				if ($items)
				{
					// add item group ref id to item group block
					$this->type_grps[$parent_type]["itgr"]["ref_ids"][] = $g;
					
					foreach($items as $item)
					{
						$group[$g][] = $item;
					}
				}
			}
			// type group
			else
			{			
				$g = $objDefinition->getGroupOfObj($child["type"]);			
				if ($g == "")
				{
					$g = $child["type"];
				}
				$group[$g][] = $child;		
			}
		}
		
		// custom block sorting?
		include_once("./Services/Container/classes/class.ilContainerSorting.php");	
		$sort = ilContainerSorting::_getInstance($parent_obj_id);									
		$block_pos = $sort->getBlockPositions();		
		if (sizeof($block_pos))
		{									
			$tmp = $this->type_grps[$parent_type];						

			$this->type_grps[$parent_type] = array();
			foreach ($block_pos as $block_type)
			{
				// type group
				if (!is_numeric($block_type) && 
					array_key_exists($block_type, $tmp))
				{
					$this->type_grps[$parent_type][$block_type] = $tmp[$block_type];
					unset($tmp[$block_type]);
				}
				// item group 
				else
				{
					// using item group ref id directly
					$this->type_grps[$parent_type][$block_type] = array();
				}
			}			

			// append missing
			if (sizeof($tmp))
			{
				foreach ($tmp as $block_type => $grp)
				{
					$this->type_grps[$parent_type][$block_type] = $grp;
				}
			}

			unset($tmp);
		}			
				
		$childs = array();
		$done = array();
		foreach ($this->type_grps[$parent_type] as $t => $g)
		{
			// type group
			if (is_array($group[$t]))
			{
				// do we have to sort this group??
				include_once("./Services/Container/classes/class.ilContainer.php");
				include_once("./Services/Container/classes/class.ilContainerSorting.php");
				$sort = ilContainerSorting::_getInstance($parent_obj_id);
				$group = $sort->sortItems($group);
				
				// need extra session sorting here
				if ($t == "sess")
				{

				}
				
				foreach ($group[$t] as $k => $item)
				{
					if (!in_array($item["child"], $done))
					{						
						$childs[] = $item;
						$done[] = $item["child"];						
					}
				}
			}
			// item groups (if not custom block sorting)
			else if ($t == "itgr" && 
				is_array($g["ref_ids"]))
			{
				foreach ($g["ref_ids"] as $ref_id)
				{
					if (isset($group[$ref_id]))
					{
						foreach ($group[$ref_id] as $k => $item)
						{
							if(!in_array($item["child"], $done))
							{								
								$childs[] = $item;
								$done[] = $item["child"];						
							}
						}
					}
				}
			}
		}
		
		return $childs;
	}

	/**
	 * Get childs of node
	 *
	 * @param
	 * @return
	 */
	function getChildsOfNode($a_parent_node_id)
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("read", $a_parent_node_id))
		{
			return array();
		}

		$obj_id = ilObject::_lookupObjId($a_parent_node_id);
		if (!ilConditionHandler::_checkAllConditionsOfTarget($a_parent_node_id, $obj_id))
		{
			return array();
		}

		return parent::getChildsOfNode($a_parent_node_id);
	}
	
	/**
	 * Is node clickable?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node clickable true/false
	 */
	function isNodeClickable($a_node)
	{
		global $rbacsystem,$tree,$ilDB,$ilUser,$ilAccess;

		$obj_id = ilObject::_lookupObjId($a_node["child"]);
		if (!ilConditionHandler::_checkAllConditionsOfTarget($a_node["child"], $obj_id))
		{
			return false;
		}

		switch ($a_node["type"])
		{
			case "crs":
				return $ilAccess->checkAccess("read", "", $a_node["child"]);			

			// visible groups can allways be clicked; group processing decides
			// what happens next
			case "grp":
				return true;
				
			case 'tst':
				if(!$rbacsystem->checkAccess("read", $a_node["child"]))
				{
					return false;
				}

				$query = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s", $obj_id);
				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return (bool) $row->complete;
				}
				return false;

			case 'svy':
				if(!$rbacsystem->checkAccess("read", $a_node["child"]))
				{
					return false;
				}

				$query = sprintf("SELECT * FROM svy_svy WHERE obj_fi=%s", $obj_id);
				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return (bool) $row->complete;
				}
				return false;

			// media pools can only be edited
			case "mep":
				if ($rbacsystem->checkAccess("read", $a_node["child"]))
				{
					return true;
				}
				else
				{
					return false;
				}
				break;
				
			case 'crsr':
			case 'catr':
				include_once('./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php');
				return ilContainerReferenceAccess::_isAccessible($a_node["child"]);
				

			// all other types are only clickable, if read permission is given
			default:
				if ($rbacsystem->checkAccess("read", $a_node["child"]))
				{
					// check if lm is online
					if ($a_node["type"] == "lm")
					{
						include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
						$lm_obj =& new ilObjLearningModule($a_node["child"]);
						if((!$lm_obj->getOnline()) && (!$rbacsystem->checkAccess('write', $a_node["child"])))
						{
							return false;
						}
					}
					// check if fblm is online
					if ($a_node["type"] == "htlm")
					{
						include_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
						$lm_obj =& new ilObjFileBasedLM($a_node["child"]);
						if((!$lm_obj->getOnline()) && (!$rbacsystem->checkAccess('write', $a_node["child"])))
						{
							return false;
						}
					}
					// check if fblm is online
					if ($a_node["type"] == "sahs")
					{
						include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
						$lm_obj =& new ilObjSAHSLearningModule($a_node["child"]);
						if((!$lm_obj->getOnline()) && (!$rbacsystem->checkAccess('write', $a_node["child"])))
						{
							return false;
						}
					}
					// check if glossary is online
					if ($a_node["type"] == "glo")
					{
						$obj_id = ilObject::_lookupObjectId($a_node["child"]);
						include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
						if((!ilObjGlossary::_lookupOnline($obj_id)) &&
							(!$rbacsystem->checkAccess('write', $a_node["child"])))
						{
							return false;
						}
					}

					return true;
				}
				else
				{
					return false;
				}
				break;
		}
	}

}

?>
