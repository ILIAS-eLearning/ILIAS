<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/UIComponent/Explorer/classes/class.ilExplorer.php';

/*
*/
class ilShopRepositoryExplorer extends ilExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	public $root_id;
	public $output;
	public $ctrl;

	/**
	 * @param     $a_target
	 * @param int $a_top_node
	 */
	public function __construct($a_target, $a_top_node = 0)
	{
		global $tree, $ilCtrl;

		$this->ctrl = $ilCtrl;

		$this->force_open_path = array();

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";
		$this->frame_target = false;
		$this->setSessionExpandVariable("repexpand");
		#$this->setTitle($lng->txt("overview"));

# Es sollen nur container angezeigt werden, die entweder als container (z.B. Kurse) kaufbar sind oder kaufbare Objekte enthalten können!

/*		if ($ilSetting->get("repository_tree_pres") == "" ||
			($ilSetting->get("rep_tree_limit_grp_crs") && $a_top_node == 0))
		{*/
			$this->addFilter("root");
			$this->addFilter("cat");
			$this->addFilter('catr');
			$this->addFilter("grp");
			$this->addFilter("icrs");
			$this->addFilter("crs");
			$this->addFilter('crsr');
			$this->addFilter('rcrs');

#			$this->addFilter("file");
#			$this->addFilter("tst");
#			$this->addFilter("exc");
			$this->setFiltered(true);
			$this->setFilterMode(IL_FM_POSITIVE);
/*		}
		else if ($ilSetting->get("repository_tree_pres") == "all_types")
		{
			foreach ($objDefinition->getAllRBACObjects() as $rtype)
			{
				$this->addFilter($rtype);
			}
			$this->setFiltered(true);
			$this->setFilterMode(IL_FM_POSITIVE);
		}*/
	}

	/**
	 * set force open path
	 */
	function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
	}

	/**
	* note: most of this stuff is used by ilCourseContentInterface too
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		switch($a_type)
		{

			case "cat":
				return "ilias.php?baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "catr":
				return "ilias.php?cmd=redirect&baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "grp":
				return "ilias.php?baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "crs":
				return "ilias.php?baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "crsr":
				return "ilias.php?cmd=redirect&baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "icrs":
				$ilCtrl->setParameterByClass("ilobjilinccoursegui", "ref_id", $a_node_id);
				$link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjilinccoursegui"), "");
				$ilCtrl->setParameterByClass("ilobjilinccoursegui", "ref_id", $_GET["ref_id"]);
				return $link;

			case 'rcrs':
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			default:
				include_once('./Services/Link/classes/class.ilLink.php');
				return ilLink::_getStaticLink($a_node_id, $a_type, true);

		}
	}

	/**
	* get image path
	*/
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		if ($a_type != "")
		{
			return ilObject::_getIcon($a_obj_id, "tiny", $a_type);
		}

		return parent::getImage($a_name);
	}

	function isClickable($a_type, $a_ref_id,$a_obj_id = 0)
	{
		global $rbacsystem,$ilDB,$ilUser,$ilAccess;

		if(!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id,$a_obj_id))
		{
			return false;
		}

		switch ($a_type)
		{
			case "crs":
				return $ilAccess->checkAccess("read", "", $a_ref_id);	

			// visible groups can allways be clicked; group processing decides
			// what happens next
			case "grp":
				return true;

			case 'tst':
				if(!$rbacsystem->checkAccess("read", $a_ref_id))
				{
					return false;
				}

				$query = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s",$a_obj_id);
				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return (bool) $row->complete;
				}
				return false;

			case 'svy':
				if(!$rbacsystem->checkAccess("read", $a_ref_id))
				{
					return false;
				}

				$query = sprintf("SELECT * FROM svy_svy WHERE obj_fi=%s",$a_obj_id);
				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return (bool) $row->complete;
				}
				return false;

			// media pools can only be edited
			case "mep":
				if ($rbacsystem->checkAccess("read", $a_ref_id))
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
				return ilContainerReferenceAccess::_isAccessible($a_ref_id);


			// all other types are only clickable, if read permission is given
			default:
				if ($rbacsystem->checkAccess("read", $a_ref_id))
				{
					// check if lm is online
					if ($a_type == "lm")
					{
						include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
						$lm_obj =& new ilObjLearningModule($a_ref_id);
						if((!$lm_obj->getOnline()) && (!$rbacsystem->checkAccess('write',$a_ref_id)))
						{
							return false;
						}
					}
					// check if fblm is online
					if ($a_type == "htlm")
					{
						include_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
						$lm_obj =& new ilObjFileBasedLM($a_ref_id);
						if((!$lm_obj->getOnline()) && (!$rbacsystem->checkAccess('write',$a_ref_id)))
						{
							return false;
						}
					}
					// check if fblm is online
					if ($a_type == "sahs")
					{
						include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
						$lm_obj =& new ilObjSAHSLearningModule($a_ref_id);
						if((!$lm_obj->getOnline()) && (!$rbacsystem->checkAccess('write',$a_ref_id)))
						{
							return false;
						}
					}
					// check if glossary is online
					if ($a_type == "glo")
					{
						$obj_id = ilObject::_lookupObjectId($a_ref_id);
						include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
						if((!ilObjGlossary::_lookupOnline($obj_id)) &&
							(!$rbacsystem->checkAccess('write',$a_ref_id)))
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

	function showChilds($a_ref_id, $a_obj_id = 0)
	{
		global $rbacsystem;

		if ($a_ref_id == 0)
		{
			return true;
		}
		if(!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id,$a_obj_id))
		{
			return false;
		}
		if ($rbacsystem->checkAccess("read", $a_ref_id))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isVisible($a_ref_id,$a_type)
	{
		global $ilAccess,$tree;

		if(!$ilAccess->checkAccess('visible', '', $a_ref_id))
		{
			return false;
		}
		return true;
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer a_obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $tree;

		// custom icons
		$path = ilObject::_getIcon($a_obj_id, "tiny", "root");

		$tpl->setCurrentBlock("icon");
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];

		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
		}

		$tpl->setVariable("ICON_IMAGE", $path);
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("icon")." ".$title);
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $title);
		$tpl->setVariable("LINK_TARGET", "ilias.php?baseClass=ilshopcontroller&ref_id=1");

		#$tpl->setVariable("TARGET", " target=\"_self\"");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
		$tpl->parseCurrentBlock();
	}

	/**
	 * sort nodes
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function sortNodes($a_nodes,$a_parent_obj_id)
	{
		global $objDefinition;

		if ($a_parent_obj_id > 0)
		{
			$parent_type = ilObject::_lookupType($a_parent_obj_id);
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
		$group = array();

		foreach ($a_nodes as $node)
		{
			$g = $objDefinition->getGroupOfObj($node["type"]);
			if ($g == "")
			{
				$g = $node["type"];
			}
			$group[$g][] = $node;
		}

		$nodes = array();
		foreach ($this->type_grps[$parent_type] as $t => $g)
		{
			if (is_array($group[$t]))
			{
				// do we have to sort this group??
				include_once("./Services/Container/classes/class.ilContainer.php");
				include_once("./Services/Container/classes/class.ilContainerSorting.php");
				$sort = ilContainerSorting::_getInstance($a_parent_obj_id);
				$group = $sort->sortItems($group);

				foreach ($group[$t] as $k => $item)
				{
					$nodes[] = $item;
				}
			}
		}

		return $nodes;
		//return parent::sortNodes($a_nodes,$a_parent_obj_id);
	}

	/**
	 * Force expansion of node
	 *
	 * @param
	 * @return
	 */
	function forceExpanded($a_node)
	{
		if (in_array($a_node, $this->force_open_path))
		{
			return true;
		}
		return false;
	}


} 
?>