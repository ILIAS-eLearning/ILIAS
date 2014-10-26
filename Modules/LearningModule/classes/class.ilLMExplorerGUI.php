<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");
include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
include_once("./Modules/LearningModule/classes/class.ilLMObject.php");

/**
 * LM editor explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMExplorerGUI extends ilTreeExplorerGUI
{
	protected $lp_cache; // [array]
	protected $cnt_lmobj; // number of items (chapters and pages) in the explorer

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent cmd
	 * @param ilObjContentObject $a_lm learning module
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, ilObjContentObject $a_lm, $a_id = "")
	{
		$this->lm = $a_lm;

		include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
		$tree = ilLMTree::getInstance($this->lm->getId());

//echo "+".$tree->isCacheUsed()."+";

//		$tree = new ilTree($this->lm->getId());
//		$tree->setTableNames('lm_tree','lm_data');
//		$tree->setTreeTablePK("lm_id");

		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		$this->cnt_lmobj = ilLMObject::preloadDataByLM($this->lm->getId());

		include_once("./Services/COPage/classes/class.ilPageObject.php");
		ilPageObject::preloadActivationDataByParentId($this->lm->getId());

		if ($a_id == "")
		{
			$a_id = "lm_exp";

			// this does not work, since it is not set yet
			if ($this->getOfflineMode())
			{
				$a_id = "lm_exp_off";
			}
		}

		parent::__construct($a_id, $a_parent_obj, $a_parent_cmd, $tree);
		
		$this->setSkipRootNode(false);
		$this->setAjax(false);
		$this->setPreloadChilds(true);

		if ((int) $_GET["obj_id"] > 0)
		{
			$this->setPathOpen((int) $_GET["obj_id"]);
		}
	}

	/**
	 * Before rendering
	 */
	function beforeRendering()
	{
		if ($this->cnt_lmobj > 200 && !$this->getOfflineMode())
		{
			$this->setAjax(true);
		}
	}


	/**
	 * Get node content
	 *
	 * @param array $a_node node array
	 * @return string node content
	 */
	function getNodeContent($a_node)
	{
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			return $this->lm->getTitle();
		}

		$lang = ($_GET["transl"] != "")
			? $_GET["transl"]
			: "-";
		return ilLMObject::_getPresentationTitle($a_node, IL_PAGE_TITLE,
			$this->lm->isActiveNumbering(), false, false, $this->lm->getId(), $lang);
	}
	
	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $_GET["obj_id"] ||
			($_GET["obj_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode())))
		{
			return true;
		}
		return false;
	}

	/**
	 * Check learning progress icon
	 *
	 * @param int $a_id lm tree node id
	 * @return string image path
	 */
	protected function checkLPIcon($a_id)
	{
		global $ilUser;

		// do it once for all chapters
		if($this->lp_cache[$this->lm->getId()] === null)
		{
			$this->lp_cache[$this->lm->getId()] = false;

			include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
			if(ilLearningProgressAccess::checkAccess($this->lm->getRefId()))
			{
				$info = null;

				include_once './Services/Object/classes/class.ilObjectLP.php';
				$olp = ilObjectLP::getInstance($this->lm->getId());
				if($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL ||
					$olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT)
				{
					include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
					$class = ilLPStatusFactory::_getClassById($this->lm->getId(), $olp->getCurrentMode());
					$info = $class::_getStatusInfo($this->lm->getId());
				}

				// parse collection items
				if(is_array($info["items"]))
				{
					foreach($info["items"] as $item_id)
					{
						$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
						if(is_array($info["in_progress"][$item_id]) &&
							in_array($ilUser->getId(), $info["in_progress"][$item_id]))
						{
							$status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
						}
						else if(is_array($info["completed"][$item_id]) &&
							in_array($ilUser->getId(), $info["completed"][$item_id]))
						{
							$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
						}
						$this->lp_cache[$this->lm->getId()][$item_id] =$status;
					}
				}
			}

			include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
		}

		if(is_array($this->lp_cache[$this->lm->getId()]) &&
			isset($this->lp_cache[$this->lm->getId()][$a_id]))
		{
			return ilLearningProgressBaseGUI::_getImagePathForStatus($this->lp_cache[$this->lm->getId()][$a_id]);
		}

		return "";
	}

}

?>
