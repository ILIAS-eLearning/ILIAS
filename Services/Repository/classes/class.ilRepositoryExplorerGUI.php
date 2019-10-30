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
	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilObjectDefinition
	 */
	protected $obj_definition;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	protected $type_grps = array();
	protected $session_materials = array();

	protected $parent_node_id = [];
	protected $node_data = [];
	
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		$this->tree = $DIC->repositoryTree();
		$this->settings = $DIC->settings();
		$this->obj_definition = $DIC["objDefinition"];
		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->access = $DIC->access();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->db = $DIC->database();
		$this->user = $DIC->user();
		$tree = $DIC->repositoryTree();
		$ilSetting = $DIC->settings();
		$objDefinition = $DIC["objDefinition"];

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
			$this->setTypeWhiteList($objDefinition->getExplorerContainerTypes());
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

		$this->setChildLimit((int) $ilSetting->get("rep_tree_limit_number"));
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
			$root_node = $this->getTree()->getNodeData($this->top_node_id);
		}
		else
		{
			$root_node = parent::getRootNode();
		}
		$this->node_data[$root_node["child"]] = $root_node;
		return $root_node;
	}

	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeContent($a_node)
	{
		$lng = $this->lng;
		
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
		$lng = $this->lng;

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
		$ilCtrl = $this->ctrl;

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

			case "grpr":
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
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

			case 'rcrs':
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			case 'prg':
				$ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $a_node["child"]);
				$link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjstudyprogrammegui"), "view");
				$ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $_GET["ref_id"]);
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
		$ilAccess = $this->access;
		$tree = $this->tree;
		$ilSetting = $this->settings;

		if (!$ilAccess->checkAccess('visible', '', $a_node["child"]))
		{
			return false;
		}

		if ($ilSetting->get("repository_tree_pres")  == "all_types") {
			/*$container_parent_id = $tree->checkForParentType($a_node["child"], 'grp');
			if (!$container_parent_id) {
				$container_parent_id = $tree->checkForParentType($a_node["child"], 'crs');
			}*/
			// see #21215
			$container_parent_id = $this->getParentCourseOrGroup($a_node["child"]);
			if ($container_parent_id > 0) {
				// do not display session materials for container course/group
				if ($container_parent_id != $a_node["child"]) {
					// get container event items only once
					if (!isset($this->session_materials[$container_parent_id])) {
						include_once './Modules/Session/classes/class.ilEventItems.php';
						$this->session_materials[$container_parent_id] = ilEventItems::_getItemsOfContainer($container_parent_id);
					}
					if (in_array($a_node["child"], $this->session_materials[$container_parent_id])) {
						return false;
					}
				}
			}
		}
		
		return true;		
	}
	
	/**
	 * Get upper course or group
	 *
	 * @param int $node_id
	 * @return int
	 */
	protected function getParentCourseOrGroup($node_id)
	{
		$current_node_id = $node_id;
		while (isset($this->parent_node_id[$current_node_id])) {
			$parent_node_id = $this->parent_node_id[$current_node_id];
			if (isset($this->node_data[$parent_node_id]) && in_array($this->node_data[$parent_node_id]["type"], ["grp", "crs"])) {
				return $parent_node_id;
			}
			$current_node_id = $parent_node_id;
		}
		return 0;
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
		$objDefinition = $this->obj_definition;
		$ilAccess = $this->access;

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

		// alex: if this is not initialized, things are messed up
		// see bug 0015978
		$this->type_grps = array();

		if (empty($this->type_grps[$parent_type]))
		{
			$this->type_grps[$parent_type] =
				$objDefinition->getGroupedRepositoryObjectTypes($parent_type);
		}

		// #14465 - item groups
		include_once('./Services/Object/classes/class.ilObjectActivation.php');									
		$group = array();
		$igroup = array(); // used for item groups, see bug #0015978
		$in_any_group = array(); 
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

					// #16697 - check item group permissions
					$may_read = $ilAccess->checkAccess('read', '', $g);
					
					// see bug #0015978
					if ($may_read)
					{
						include_once("./Services/Container/classes/class.ilContainerSorting.php");
						$items = ilContainerSorting::_getInstance($parent_obj_id)->sortSubItems('itgr', $child["obj_id"], $items);
					}

					foreach($items as $item)
					{
						$in_any_group[] = $item["child"];
						
						if ($may_read)
						{
							$igroup[$g][] = $item;
							$group[$g][] = $item;
						}
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
		
		$in_any_group = array_unique($in_any_group);

		// custom block sorting?
		include_once("./Services/Container/classes/class.ilContainerSorting.php");	
		$sort = ilContainerSorting::_getInstance($parent_obj_id);									
		$block_pos = $sort->getBlockPositions();
		if (is_array($block_pos) && count($block_pos) > 0)
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
				// see bug #0015978
				// custom sorted igroups
				if (is_array($igroup[$t]))
				{
					foreach ($igroup[$t] as $k => $item)
					{
						if (!in_array($item["child"], $done))
						{
							$childs[] = $item;
							$done[] = $item["child"];
						}
					}
				}
				else
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
						if (!in_array($item["child"], $done) &&
							!in_array($item["child"], $in_any_group)) // #16697
						{
							$childs[] = $item;
							$done[] = $item["child"];
						}
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
		$rbacsystem = $this->rbacsystem;
		
		if (!$rbacsystem->checkAccess("read", $a_parent_node_id))
		{
			return array();
		}

		$obj_id = ilObject::_lookupObjId($a_parent_node_id);
		if (!ilConditionHandler::_checkAllConditionsOfTarget($a_parent_node_id, $obj_id))
		{
			return array();
		}

		$childs = parent::getChildsOfNode($a_parent_node_id);

		foreach ($childs as $c) {
			$this->parent_node_id[$c["child"]] = $a_parent_node_id;
			$this->node_data[$c["child"]] = $c;
		}

		return $childs;
	}
	
	/**
	 * Is node clickable?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node clickable true/false
	 */
	function isNodeClickable($a_node)
	{
		$rbacsystem = $this->rbacsystem;
		$tree = $this->tree;
		$ilDB = $this->db;
		$ilUser = $this->user;
		$ilAccess = $this->access;

		$obj_id = ilObject::_lookupObjId($a_node["child"]);
		if (!ilConditionHandler::_checkAllConditionsOfTarget($a_node["child"], $obj_id))
		{
			return false;
		}

		switch ($a_node["type"])
		{
			case 'tst':
				if(!$rbacsystem->checkAccess("read", $a_node["child"]))
				{
					return false;
				}

				$query = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s", $obj_id);
				$res = $ilDB->query($query);
				while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
				while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
			case 'grpr':
			case 'crsr':
			case 'catr':
				include_once('./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php');
				return ilContainerReferenceAccess::_isAccessible($a_node["child"]);
			
			case 'prg': 
					return $rbacsystem->checkAccess("read", $a_node["child"]);

			// all other types are only clickable, if read permission is given
			default:
				if ($rbacsystem->checkAccess("read", $a_node["child"]))
				{
					// check if lm is online
					if ($a_node["type"] == "lm")
					{
						include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
						$lm_obj = new ilObjLearningModule($a_node["child"]);
						if(($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_node["child"])))
						{
							return false;
						}
					}
					// check if fblm is online
					if ($a_node["type"] == "htlm")
					{
						include_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
						$lm_obj = new ilObjFileBasedLM($a_node["child"]);
						if(($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_node["child"])))
						{
							return false;
						}
					}
					// check if fblm is online
					if ($a_node["type"] == "sahs")
					{
						include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
						$lm_obj = new ilObjSAHSLearningModule($a_node["child"]);
						if(($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_node["child"])))
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
