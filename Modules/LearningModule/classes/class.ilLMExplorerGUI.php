<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    /**
     * @var ilObjUser
     */
    protected $user;

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
        global $DIC;

        $this->user = $DIC->user();
        $this->lm = $a_lm;

        $tree = ilLMTree::getInstance($this->lm->getId());

        $this->cnt_lmobj = ilLMObject::preloadDataByLM($this->lm->getId());

        ilPageObject::preloadActivationDataByParentId($this->lm->getId());

        if ($a_id == "") {
            $a_id = "lm_exp";

            // this does not work, since it is not set yet
            if ($this->getOfflineMode()) {
                $a_id = "lm_exp_off";
            }
        }

        parent::__construct($a_id, $a_parent_obj, $a_parent_cmd, $tree);
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setPreloadChilds(true);

        $this->setPathOpen($tree->readRootId());

        if ((int) $_GET["obj_id"] > 0) {
            $this->setPathOpen((int) $_GET["obj_id"]);
        }
    }

    /**
     * Before rendering
     */
    public function beforeRendering()
    {
        if ($this->cnt_lmobj > 200 && !$this->getOfflineMode()) {
            $class = (is_object($this->parent_obj))
                ? get_class($this->parent_obj)
                : $this->parent_obj;
            $this->ctrl->setParameterByClass($class, "obj_id", $_GET["obj_id"]);
            $this->setAjax(true);
        }
    }


    /**
     * Get node content
     *
     * @param array $a_node node array
     * @return string node content
     */
    public function getNodeContent($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->lm->getTitle();
        }

        $lang = ($_GET["transl"] != "")
            ? $_GET["transl"]
            : "-";
        return ilLMObject::_getPresentationTitle(
            $a_node,
            ilLMObject::PAGE_TITLE,
            $this->lm->isActiveNumbering(),
            false,
            false,
            $this->lm->getId(),
            $lang
        );
    }
    
    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeHighlighted($a_node)
    {
        if ($a_node["child"] == $_GET["obj_id"] ||
            ($_GET["obj_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
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
        $ilUser = $this->user;

        // do it once for all chapters
        if ($this->lp_cache[$this->lm->getId()] === null) {
            $this->lp_cache[$this->lm->getId()] = false;

            if (ilLearningProgressAccess::checkAccess($this->lm->getRefId())) {
                $info = null;

                $olp = ilObjectLP::getInstance($this->lm->getId());
                if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL ||
                    $olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
                    $class = ilLPStatusFactory::_getClassById($this->lm->getId(), $olp->getCurrentMode());
                    $info = $class::_getStatusInfo($this->lm->getId());
                }

                // parse collection items
                if (is_array($info["items"])) {
                    foreach ($info["items"] as $item_id) {
                        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                        if (is_array($info["in_progress"][$item_id]) &&
                            in_array($ilUser->getId(), $info["in_progress"][$item_id])) {
                            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                        } elseif (is_array($info["completed"][$item_id]) &&
                            in_array($ilUser->getId(), $info["completed"][$item_id])) {
                            $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                        }
                        $this->lp_cache[$this->lm->getId()][$item_id] = $status;
                    }
                }
            }
        }

        if (is_array($this->lp_cache[$this->lm->getId()]) &&
            isset($this->lp_cache[$this->lm->getId()][$a_id])) {
            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
            return $icons->getImagePathForStatus($this->lp_cache[$this->lm->getId()][$a_id]);
        }

        return "";
    }
}
