<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * LM editor explorer GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMExplorerGUI extends ilTreeExplorerGUI
{
    protected ilObjContentObject $lm;
    protected ilObjUser $user;
    protected array $lp_cache = [];
    protected int $cnt_lmobj = 0;
    protected string $obj_id = "";
    protected string $transl = "";

    /**
     * @param object|string $a_parent_obj
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilObjContentObject $a_lm,
        string $a_id = ""
    ) {
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

        $params = $DIC->http()->request()->getQueryParams();
        $this->obj_id = (string) ($params["obj_id"] ?? "");
        $this->transl = (string) ($params["transl"] ?? "");

        if ($this->obj_id > 0) {
            $this->setPathOpen($this->obj_id);
        }
    }

    public function beforeRendering(): void
    {
        if ($this->cnt_lmobj > 200 && !$this->getOfflineMode()) {
            $class = (is_object($this->parent_obj))
                ? get_class($this->parent_obj)
                : $this->parent_obj;
            $this->ctrl->setParameterByClass($class, "obj_id", $this->obj_id);
            $this->setAjax(true);
        }
    }


    /**
     * @param object|array $a_node
     */
    public function getNodeContent($a_node): string
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->lm->getTitle();
        }

        $lang = ($this->transl != "")
            ? $this->transl
            : "-";
        return ilLMObject::_getNodePresentationTitle(
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
     * @param object|array $a_node
     */
    public function isNodeHighlighted($a_node): bool
    {
        if ($a_node["child"] == $this->obj_id ||
            ($this->obj_id == "" && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }

    /**
     * @param int $a_id lm tree node id
     * @throws ilInvalidLPStatusException
     */
    protected function checkLPIcon(int $a_id): string
    {
        $ilUser = $this->user;

        // do it once for all chapters
        if (!isset($this->lp_cache[$this->lm->getId()])) {
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
                if (isset($info["items"])) {
                    foreach ($info["items"] as $item_id) {
                        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                        if (isset($info["in_progress"][$item_id]) &&
                            in_array($ilUser->getId(), $info["in_progress"][$item_id])) {
                            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                        } elseif (isset($info["completed"][$item_id]) &&
                            in_array($ilUser->getId(), $info["completed"][$item_id])) {
                            $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                        }
                        $this->lp_cache[$this->lm->getId()][$item_id] = $status;
                    }
                }
            }
        }

        if (isset($this->lp_cache[$this->lm->getId()]) &&
            isset($this->lp_cache[$this->lm->getId()][$a_id])) {
            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
            return $icons->getImagePathForStatus($this->lp_cache[$this->lm->getId()][$a_id]);
        }

        return "";
    }
}
