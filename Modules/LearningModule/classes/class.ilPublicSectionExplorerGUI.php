<?php
/*
 * Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE
 * Date: 23.10.14
 * Time: 10:47
 */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * tree explorer lm public area
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilPublicSectionExplorerGUI extends ilTreeExplorerGUI
{
	var $exp_id = "public_section";

	/**
	 * constructor
	 *
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param ilObjLearningModule $a_lm
	 */
	public function __construct($a_parent_obj,$a_parent_cmd, $a_lm)
	{
		$this->lm = $a_lm;

		include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
		$tree = ilLMTree::getInstance($this->lm->getId());

		parent::__construct("lm_public_section_".$this->lm->getId(),$a_parent_obj,$a_parent_cmd,$tree);
	}

	/**
	 * get node content
	 *
	 * @param mixed $a_node
	 * @return string note name
	 */
	public function getNodeContent($a_node)
	{
		$lang = ($_GET["transl"] != "")
			? $_GET["transl"]
			: "-";
		return ilLMObject::_getPresentationTitle($a_node, IL_PAGE_TITLE,
			$this->lm->isActiveNumbering(), false, false, $this->lm->getId(), $lang);
	}

	/**
	 * Get node icon
	 *
	 * @param array $a_node node array
	 * @return string icon path
	 */
	function getNodeIcon($a_node)
	{
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			$icon = ilUtil::getImagePath("icon_lm.svg");
		}
		else
		{
			$a_name = "icon_".$a_node["type"].".svg";
			if ($a_node["type"] == "pg")
			{
				include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
				$lm_set = new ilSetting("lm");
				$active = ilLMPage::_lookupActive($a_node["child"], $this->lm->getType(),
					$lm_set->get("time_scheduled_page_activation"));

				// is page scheduled?
				$img_sc = ($lm_set->get("time_scheduled_page_activation") &&
					ilLMPage::_isScheduledActivation($a_node["child"], $this->lm->getType()))
					? "_sc"
					: "";

				$a_name = "icon_pg".$img_sc.".svg";

				if (!$active)
				{
					$a_name = "icon_pg_d".$img_sc.".svg";
				}
				else
				{
					include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
					$contains_dis = ilLMPage::_lookupContainsDeactivatedElements($a_node["child"],
						$this->lm->getType());
					if ($contains_dis)
					{
						$a_name = "icon_pg_del".$img_sc.".svg";
					}
				}
			}
			$icon = ilUtil::getImagePath($a_name);
		}

		return $icon;
	}

	/**
	 * select public pages and open public chapter
	 */
	public function beforeRendering()
	{
		//select public pages and open public chapters
		foreach($this->getAllNodes() as $node)
		{
			if($node["public_access"] == "y" && $node["type"] == "pg")
			{
				$this->setNodeSelected($node["obj_id"]);
			}
			if($node["public_access"] == "y" && $node["type"]== "st")
			{
				$this->setNodeOpen($node["obj_id"]);
			}
		}
	}

	/**
	 * Returns all nodes from tree recursive
	 *
	 * @param mixed $from_id
	 * @return array nodes
	 */
	protected function getAllNodes($from_id = null)
	{
		$nodes = array();

		if($from_id === null)
		{
			$from_id = $this->getNodeId($this->getRootNode());
		}

		foreach($this->getChildsOfNode($from_id) as $node)
		{
			$nodes[] = $node;

			if($node["type"] == "st")
			{
				$nodes = array_merge($nodes, $this->getAllNodes($node["obj_id"]));
			}
		}
		return $nodes;
	}

	/**
	 * Is not clickable?
	 *
	 * @param array $a_node node array
	 * @return bool
	 */
	function isNodeClickable($a_node)
	{
		if ($a_node["type"] == "pg")
		{
			return true;
		}
		return false;
	}

}