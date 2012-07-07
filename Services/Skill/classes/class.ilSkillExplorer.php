<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Explorer for skill management
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

class ilSkillExplorer extends ilExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $slm_obj;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilSkillExplorer($a_target, $a_templates = false)
	{
		$this->templates = $a_templates;
		
		parent::ilExplorer($a_target);
		
		$this->setFilterMode(IL_FM_POSITIVE);
		if ($a_templates)
		{
			$this->addFilter("skrt");
			$this->addFilter("sktp");
			$this->addFilter("sctp");
		}
		else
		{
			$this->addFilter("skrt");
			$this->addFilter("skll");
			$this->addFilter("scat");
			$this->addFilter("sktr");
		}
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->tree = new ilSkillTree();
		$this->root_id = $this->tree->readRootId();
		
		$this->setSessionExpandVariable("skexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
		
		$this->setOrderColumn("order_nr");
//		$this->textwidth = 200;

		$this->force_open_path = array();
	}

	/**
	* set force open path
	*/
	function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias, $ilCtrl;

		$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
			$this->tree->readRootId());
		
		if ($this->templates)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_sctp_s.png"));
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt("skmg_skill_templates"));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("link");
			$tpl->setVariable("TITLE", $lng->txt("skmg_skill_templates"));
			if ($this->highlighted == $this->tree->readRootId())
			{
				$tpl->setVariable("A_CLASS", "class='il_HighlightedNode'");
			}
			$tpl->setVariable("LINK_TARGET",
				$ilCtrl->getLinkTargetByClass("ilskillrootgui", "listTemplates"));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_scat_s.png"));
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt("skmg_skills"));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("link");
			$tpl->setVariable("TITLE", $lng->txt("skmg_skills"));
			if ($this->highlighted == $this->tree->readRootId())
			{
				$tpl->setVariable("A_CLASS", "class='il_HighlightedNode'");
			}
			$tpl->setVariable("LINK_TARGET",
				$ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills"));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->touchBlock("element");

		$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
			$_GET["obj_id"]);

	}

	/**
	* overwritten method from base class
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		
/*		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"]."_s.png", $a_option["type"], $a_obj_id));
		$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
		$this->iconList[] = "iconid_".$a_node_id;
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
		$tpl->parseCurrentBlock();
		
		$this->outputIcons(false);*/
		parent::formatObject($tpl, $a_node_id,$a_option,$a_obj_id);
	}
	
	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_obj_id = 0)
	{
		global $ilUser;
		return true;
	}
	
	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		switch($a_type)
		{
			// category
			case "scat":
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "listItems");
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// skill template reference
			case "sktr":
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatereferencegui", "listItems");
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// skill
			case "skll":
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass("ilbasicskillgui", "edit");
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// --------
				
			// template
			case "sktp":
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "edit");
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			// template category
			case "sctp":
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "listItems");
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
		}
	}

	/**
	 * standard implementation for title, may be overwritten by derived classes
	 */
	function buildTitle($a_title, $a_id, $a_type)
	{
		if ($a_type == "sktr")
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
			$tid = ilSkillTemplateReference::_lookupTemplateId($a_id);
			$a_title.= " (".ilSkillTreeNode::_lookupTitle($tid).")";
		}
		
		if (ilSkillTreeNode::_lookupSelfEvaluation($a_id))
		{
			$a_title = "<u>".$a_title."</u>";
		}
		
		return $a_title;
	}

	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		if (in_array($a_obj_id, $this->force_open_path))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get frame target
	 */
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return "";
	}
	
	/**
	 * get image path (may be overwritten by derived classes)
	 */
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		if (in_array($a_type, array("skll", "scat", "sctr", "sktr")))
		{
			return ilSkillTreeNode::getIconPath($a_obj_id, $a_type, "_s", $this->draft[$a_obj_id]);
		}
		return ilUtil::getImagePath($a_name);
	}

	/**
	 * Get childs of node
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_id)
	{
		$childs =  $this->tree->getChilds($a_parent_id, $this->order_column);
		
		foreach ($childs as $c)
		{
			$this->parent[$c["child"]] = $c["parent"];
			if ($this->draft[$c["parent"]])
			{
				$this->draft[$c["child"]] = true;
			}
			else
			{
				$this->draft[$c["child"]] = ilSkillTreeNode::_lookupDraft($c["child"]);
			}
		}
		return $childs;
	}

}
?>
