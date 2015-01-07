<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/LearningModule/classes/class.ilLMExplorerGUI.php");

/**
 * LM presentation (left frame) explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMTOCExplorerGUI extends ilLMExplorerGUI
{
	protected $lang;
	protected $highlight_node;
	protected $tracker;

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent cmd
	 * @param ilLMPresentationGUI $a_lm_pres learning module presentation gui object
	 * @param string $a_lang language
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilLMPresentationGUI $a_lm_pres, $a_lang = "-",
		$a_focus_id = 0)
	{
		$this->lm_pres = $a_lm_pres;
		$this->lm = $this->lm_pres->lm;
		$exp_id = (!$this->getOfflineMode() && $this->lm->getProgressIcons())
			? "ilLMProgressTree"
			: "";
		parent::__construct($a_parent_obj, $a_parent_cmd, $this->lm, $exp_id);
		$this->lm_set = new ilSetting("lm");
		$this->lang = $a_lang;
		if ($a_focus_id > 0)
		{
			$this->setSecondaryHighlightedNodes(array($a_focus_id));
		}
		if ($this->lm->getTOCMode() != "pages")
		{
			$this->setTypeWhiteList(array("st", "du"));
		}
		$this->focus_id = $a_focus_id;
	}

	/**
	 * Get root node
	 */
	function getRootNode()
	{
		$root_id = $this->getTree()->readRootId();
		if ($this->focus_id > 0 && $this->getTree()->isInTree($this->focus_id) &&
			ilLMObject::_lookupType($this->focus_id) == "st")
		{
//			$root_id = $this->focus_id;
		}
		return $this->getTree()->getNodeData($root_id);
	}

	/**
	 * Set tracker
	 *
	 * @param ilLMTracker $a_val tracker object
	 */
	function setTracker($a_val)
	{
		$this->tracker = $a_val;
	}

	/**
	 * Get tracker
	 *
	 * @return ilLMTracker tracker object
	 */
	function getTracker()
	{
		return $this->tracker;
	}

	/**
	 * Set highlighted node
	 *
	 * @param int $a_val node id
	 */
	function setHighlightNode($a_val)
	{
		$this->highlight_node = $a_val;
	}

	/**
	 * Get highlighted node
	 *
	 * @return int node id
	 */
	function getHighlightNode()
	{
		return $this->highlight_node;
	}

	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $this->getHighlightNode())
		{
			return true;
		}
		return false;
	}

	/**
	 * Get node content
	 *
	 * @param array $a_node node array
	 * @return string node content
	 */
	function getNodeContent($a_node)
	{
		if ($a_node["type"] == "st")
		{
			return ilStructureObject::_getPresentationTitle($a_node["child"],
				$this->lm->isActiveNumbering(), false, $this->lm->getId(), $this->lang);
		}
		else if ($a_node["type"] == "pg")
		{
			return ilLMPageObject::_getPresentationTitle($a_node["child"],
				$this->lm->getPageHeader(), $this->lm->isActiveNumbering(),
				$this->lm_set->get("time_scheduled_page_activation"), true, $this->lm->getId(), $this->lang);
		}
		else if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			return $this->lm->getTitle();
		}

		if ($a_node["type"] == "pg")
		{
			return ilLMPageObject::_getPresentationTitle($a_node["child"],
				$this->lm->getPageHeader(), $this->lm->isActiveNumbering(),
				$this->lm_set->get("time_scheduled_page_activation"), true, $this->lm->getId(), $this->lang);
		}

		return $a_node["title"];
	}


	/**
	 * Get node icon
	 *
	 * @param array $a_node node array
	 * @return string icon path
	 */
	function getNodeIcon($a_node)
	{
		// overwrite chapter icons with lp info?
		if(!$this->getOfflineMode() && $a_node["type"] == "st")
		{
			$icon = $this->checkLPIcon($a_node["child"]);
			if ($icon != "")
			{
				return $icon;
			}
		}

		// use progress icons (does not depend on lp mode)
		if(!$this->getOfflineMode() && $this->lm->getProgressIcons())
		{
			return $this->tracker->getIconForLMObject($a_node, $this->highlight_node);
		}

		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");

		if ($a_node["type"] == "du")
		{
			$a_node["type"] = "lm";
		}
		$a_name = "icon_".$a_node["type"].".svg";
		if ($a_node["type"] == "pg")
		{
			include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
			$lm_set = new ilSetting("lm");
			$active = ilLMPage::_lookupActive($a_node["child"], $this->lm->getType(),
				$lm_set->get("time_scheduled_page_activation"));

			// is page scheduled?
			$img_sc = ($lm_set->get("time_scheduled_page_activation") &&
				ilLMPage::_isScheduledActivation($a_node["child"], $this->lm->getType()) && !$active
				&& !$this->getOfflineMode())
				? "_sc"
				: "";

			$a_name = "icon_pg".$img_sc.".svg";

			if (!$active && !$this->getOfflineMode())
			{
				$a_name = "icon_pg_d".$img_sc.".svg";
			}
		}

		return ilUtil::getImagePath($a_name, false, "output", $this->getOfflineMode());
	}

	/**
	 * Is node clickable
	 *
	 * @param array $a_node node array
	 * @return bool clickable?
	 */
	function isNodeClickable($a_node)
	{
		global $ilUser;

		$orig_node_id = $a_node["child"];

		// if navigation is restricted based on correct answered questions
		// check if we have preceeding pages including unsanswered/incorrect answered questions
		if (!$this->getOfflineMode())
		{
			if ($this->lm->getRestrictForwardNavigation())
			{
				if ($this->getTracker()->hasPredIncorrectAnswers($orig_node_id))
				{
					return false;
				}
			}
		}

		if ($a_node["type"] == "st")
		{
			if (!$this->getOfflineMode())
			{
				if ($this->lm->getTOCMode() != "pages")
				{
					$a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
				}
				else
				{
					// faster, but needs pages to be in explorer
					$a_node = $this->getSuccessorNode($a_node["child"], "pg");
				}
				if ($a_node["child"] == 0)
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
					if ($this->lm->getTOCMode() != "pages")
					{
						$a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
					}
					else
					{
						$a_node = $this->getSuccessorNode($a_node["child"], "pg");
					}
					include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
					$active = ilLMPage::_lookupActive($a_node["child"], $this->lm->getType(),
						$this->lm_set->get("time_scheduled_page_activation"));

					if ($a_node["child"] > 0 && !$active)
					{
						$found = false;
					}
					else
					{
						$found = true;
					}
				}
				if ($a_node["child"] <= 0)
				{
					return false;
				}
				else
				{
					$path = $this->getTree()->getPathId($a_node["child"]);
					if (!in_array($orig_node_id, $path))
					{
						return false;
					}
				}
			}
		}

		if ($a_node["type"] == "pg")
		{
			// check public area mode
			include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
			include_once 'Services/Payment/classes/class.ilPaymentObject.php';
			if (($ilUser->getId() == ANONYMOUS_USER_ID ||
					ilPaymentObject::_requiresPurchaseToAccess((int)$this->lm->getRefId())) &&
				!ilLMObject::_isPagePublic($a_node["child"], true))
			{
				return false;
			}
		}

		return true;

	}


	/**
	 * Get node icon alt text
	 *
	 * @param array $a_node node array
	 * @return string alt text
	 */
	function getNodeIconAlt($a_node)
	{
	}
	
	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		if (!$this->getOfflineMode())
		{
			return $this->lm_pres->getLink($this->lm->getRefId(), "", $a_node["child"]);
			//return parent::buildLinkTarget($a_node_id, $a_type);
		}
		else
		{
			if ($a_node["type"] != "pg")
			{
				// get next activated page
				$found = false;
				while (!$found)
				{
					$a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
					include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
					$active = ilLMPage::_lookupActive($a_node["child"], $this->lm->getType(),
						$this->lm_set->get("time_scheduled_page_activation"));

					if ($a_node["child"] > 0 && !$active)
					{
						$found = false;
					}
					else
					{
						$found = true;
					}
				}
			}
			include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
			if ($nid = ilLMPageObject::getExportId($this->lm->getId(), $a_node["child"]))
			{
				return "lm_pg_".$nid.".html";
			}
			return "lm_pg_".$a_node["child"].".html";
		}

	}

	/**
	 * Is node visible?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeVisible($a_node)
	{
		include_once("./Modules/LearningModule/classes/class.ilLMTracker.php");
		return ilLMTracker::_isNodeVisible($a_node);
	}
}

?>
