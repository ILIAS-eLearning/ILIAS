<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Basic GUI class for skill tree nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillTreeNodeGUI
{
	var $node_object;

	/**
	* constructor
	*
	* @param	object		$a_content_obj		node object
	*/
	function ilSkillTreeNodeGUI($a_node_id = 0)
	{
		$this->node_object = null;

		if ($a_node_id > 0)
		{
			$this->readNodeObject($a_node_id);
		}
	}

	/**
	* Set Parent GUI class
	*
	* @param	object	$a_parentgui	Parent GUI class
	*/
	function setParentGUI($a_parentgui)
	{
		$this->parentgui = $a_parentgui;
	}

	/**
	* Get Parent GUI class (ilObjSCORM2004LearningModuleGUI).
	*
	* @return	object	Parent GUI class
	*/
	function getParentGUI()
	{
		return $this->parentgui;
	}

	/**
	 * Get node object instance
	 */
	function readNodeObject($a_node_id)
	{
		include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
		$this->node_object = ilSkillTreeNodeFactory::getInstance($a_node_id);
	}
	
	
	/**
	 * Insert Chapter
	 */
/*	function insertChapter()
	{
		global $ilCtrl;
		
		$res = $this->getParentGUI()->insertChapter(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}
*/
	/**
	* Insert Sco
	*/
/*	function insertSco()
	{
		global $ilCtrl;
		
		$res = $this->getParentGUI()->insertSco(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}
*/

	/**
	 * Collapse all
	 */
	function collapseAll()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->collapseAll(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Expand all
	 */
	function ExpandAll()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->expandAll(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Save Titles
	 */
	function saveAllTitles()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->saveAllTitles(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Delete nodes in the hierarchy
	 */
	function deleteNodes()
	{
		global $ilCtrl;

		$ilCtrl->setParameter($this, "backcmd", $_GET["backcmd"]);
		$this->getParentGUI()->deleteNodes($ilCtrl->getFormAction($this));
	}

	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * confirmed delete
	 */
	function confirmedDelete()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->confirmedDelete(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Set Locator Items
	 */
	function setLocator()
	{
		global $ilLocator, $tpl, $ilCtrl;
		
		$ilLocator->addRepositoryItems($_GET["ref_id"]);
		$this->getParentGUI()->addLocatorItems();
		
		if ($_GET["obj_id"] > 0)
		{
			include_once("./Services/Skill/classes/class.ilSkillTree.php");
			$tree = new ilSkillTree();
			$path = $tree->getPathFull($_GET["obj_id"]);
			for( $i =  1; $i < count($path); $i++)
			{
				switch($path[$i]["type"])
				{
					case "scat":
						$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilskillmanagementgui",
							"ilskillcategorygui"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_skmg_s.gif"));
						break;

					case "skll":
						$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilskillmanagementgui",
							"ilbasicskillgui"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_skmg_s.gif"));
						break;
						
				}
			}
		}
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		
		$tpl->setLocator();
	}

}
?>
