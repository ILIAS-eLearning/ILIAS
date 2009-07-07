<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilLMExplorer.php");
require_once("./Modules/LearningModule/classes/class.ilStructureObject.php");

/*
* Explorer View for Learning Module Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMTOCExplorer extends ilLMExplorer
{
	var	$offline;
	
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilLMTOCExplorer($a_target,&$a_lm_obj)
	{
		$this->offline = false;
		$this->force_open_path = array();
		parent::ilLMExplorer($a_target, $a_lm_obj);
		$this->lm_set = new ilSetting("lm");
	}
	
	/**
	* set offline mode
	*/
	function setOfflineMode($a_offline = true)
	{
		$this->offline = $a_offline;
	}

	/**
	* get offline mode
	*/
	function offlineMode()
	{
		return $this->offline;
	}
	
	
	/**
	* set force open path
	*/
	function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
	}
	
	/**
	* standard implementation for title, maybe overwritten by derived classes
	*/
	function buildTitle($a_title, $a_id, $a_type)
	{
//echo "<br>-$a_title-$a_type-$a_id-";
		if ($a_type == "st")
		{
			return ilStructureObject::_getPresentationTitle($a_id,
				$this->lm_obj->isActiveNumbering());
		}

		if ($this->lm_obj->getTOCMode() == "chapters" || $a_type != "pg")
		{
			return $a_title;
		}
		else
		{
			if ($a_type == "pg")
			{
				return ilLMPageObject::_getPresentationTitle($a_id,
					$this->lm_obj->getPageHeader(), $this->lm_obj->isActiveNumbering(),
					$this->lm_set->get("time_scheduled_page_activation"));
			}
		}
	}
	
	
	/**
	* get image path (may be overwritten by derived classes)
	*/
	function getImage($a_name)
	{
		return ilUtil::getImagePath($a_name, false, "output", $this->offlineMode());
	}

	
	function isClickable($a_type, $a_node_id)
	{
		global $ilUser;
		
		$orig_node_id = $a_node_id;
		
		if ($a_type == "st")
		{
			if (!$this->offlineMode())
			{
				$a_node = $this->tree->fetchSuccessorNode($a_node_id, "pg");
				$a_node_id = $a_node["child"];
				if ($a_node_id == 0)
				{
					return false;
				}
			}
			else
			{
				// get next activated page
				$found = false;
				while (!$found)
				{
					$a_node = $this->tree->fetchSuccessorNode($a_node_id, "pg");
					$a_node_id = $a_node["child"];
					include_once("./Services/COPage/classes/class.ilPageObject.php");
					$active = ilPageObject::_lookupActive($a_node_id, $this->lm_obj->getType(),
						$this->lm_set->get("time_scheduled_page_activation"));

					if ($a_node_id > 0 && !$active)
					{
						$found = false;
					}
					else
					{
						$found = true;
					}
				}
				if ($a_node_id <= 0)
				{
					return false;
				}
				else
				{
					$path = $this->tree->getPathId($a_node_id);
					if (!in_array($orig_node_id, $path))
					{
						return false;
					}
				}
			}
		}
		
		if ($a_type == "pg")
		{
			// check public area mode
			include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
			include_once 'payment/classes/class.ilPaymentObject.php';
			if (($ilUser->getId() == ANONYMOUS_USER_ID || 
				ilPaymentObject::_requiresPurchaseToAccess((int)$this->lm_obj->getRefId())) &&
			    !ilLMObject::_isPagePublic($a_node_id, true))
			{
				return false;
			}
		}

		return true;
	}

	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		if (!$this->offlineMode())
		{
			return parent::buildLinkTarget($a_node_id, $a_type);
		}
		else
		{
			if ($a_node_id < 1)
			{
				$a_node_id = $this->tree->getRootId();
			}
			if ($a_type != "pg")
			{
				// get next activated page
				$found = false;
				while (!$found)
				{
					$a_node = $this->tree->fetchSuccessorNode($a_node_id, "pg");
					$a_node_id = $a_node["child"];
					include_once("./Services/COPage/classes/class.ilPageObject.php");
					$active = ilPageObject::_lookupActive($a_node_id, $this->lm_obj->getType(),
						$this->lm_set->get("time_scheduled_page_activation"));

					if ($a_node_id > 0 && !$active)
					{
						$found = false;
					}
					else
					{
						$found = true;
					}
				}
			}
			if (!$this->lm_obj->cleanFrames())
			{
				return "frame_".$a_node_id."_maincontent.html";
			}
			else
			{
				return "lm_pg_".$a_node_id.".html";
			}
		}
	}
	
	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		if ($this->offlineMode())
		{
			return true;
		}
		else
		{
			if (in_array($a_obj_id, $this->force_open_path))
			{
				return true;
			}
			return false;
		}
	}

	function isVisible($a_id, $a_type)
	{
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		$active = ilPageObject::_lookupActive($a_id, $this->lm_obj->getType(),
			$this->lm_set->get("time_scheduled_page_activation"));

		if(!$active && $a_type == "pg")
		{
			return false;
		}
		else
		{
			return true;
		}
	}

} // END class.ilLMTOCExplorer
?>
