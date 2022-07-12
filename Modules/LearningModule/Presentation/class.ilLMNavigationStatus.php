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
 * Checks current navigation request status
 * - determines the current page (request may e.g. pass a chapter id, in this case the next page is searched)
 * - determines if all pages of a chapter are deactivated
 * - determines if the current page is deactivated
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMNavigationStatus
{
    protected ?int $current_page_id = null;
    protected bool $chapter_has_no_active_page = false;
    protected bool $deactivated_page = false;
    protected ilObjLearningModule $lm;
    protected ilLMTree $lm_tree;
    protected ilObjUser $user;
    protected ilSetting $lm_set;
    protected int $requested_back_page;
    protected string $cmd;
    protected int $focus_id;
    protected int $requested_obj_id;

    public function __construct(
        ilObjUser $user,
        int $request_obj_id,
        ilLMTree $lm_tree,
        ilObjLearningModule $lm,
        ilSetting $lm_set,
        string $requested_back_page,
        string $cmd,
        int $focus_id
    ) {
        $this->user = $user;
        $this->requested_obj_id = $request_obj_id;
        $this->lm_tree = $lm_tree;
        $this->lm = $lm;
        $this->lm_set = $lm_set;
        $this->requested_back_page = (int) $requested_back_page;
        $this->cmd = $cmd;
        $this->focus_id = $focus_id;

        $this->determineStatus();
    }

    /**
     * Has current chapter no active page?
     */
    public function isChapterWithoutActivePage() : bool
    {
        return $this->chapter_has_no_active_page;
    }

    public function isDeactivatedPage() : bool
    {
        return $this->deactivated_page;
    }

    public function getCurrentPage() : int
    {
        return $this->current_page_id;
    }

    protected function determineStatus() : void
    {
        $user = $this->user;

        $this->chapter_has_no_active_page = false;
        $this->deactivated_page = false;
        // determine object id
        if ($this->requested_obj_id == 0) {
            $obj_id = $this->lm_tree->getRootId();

            if ($this->cmd == "resume") {
                if ($user->getId() != ANONYMOUS_USER_ID && ($this->focus_id == 0)) {
                    $last_accessed_page = ilObjLearningModuleAccess::_getLastAccessedPage($this->lm->getRefId(), $user->getId());
                    // if last accessed page was final page do nothing, start over
                    if ($last_accessed_page &&
                        $last_accessed_page != $this->lm_tree->getLastActivePage()) {
                        $obj_id = $last_accessed_page;
                    }
                }
            }
        } else {
            $obj_id = $this->requested_obj_id;
            $active = ilLMPage::_lookupActive(
                $obj_id,
                $this->lm->getType(),
                $this->lm_set->get("time_scheduled_page_activation")
            );

            if (!$active &&
                ilLMPageObject::_lookupType($obj_id) == "pg") {
                $this->deactivated_page = true;
            }
        }
        // obj_id not in tree -> it is a unassigned page -> return page id
        if (!$this->lm_tree->isInTree($obj_id)) {
            $this->current_page_id = $obj_id;
            return;
        }

        $curr_node = $this->lm_tree->getNodeData($obj_id);

        $active = ilLMPage::_lookupActive(
            $obj_id,
            $this->lm->getType(),
            $this->lm_set->get("time_scheduled_page_activation")
        );

        if ($curr_node["type"] == "pg" &&
            $active) {		// page in tree -> return page id
            $page_id = $curr_node["obj_id"];
        } else { 		// no page -> search for next page and return its id
            $succ_node = true;
            $active = false;
            $page_id = $obj_id;
            while ($succ_node && !$active) {
                $succ_node = $this->lm_tree->fetchSuccessorNode($page_id, "pg");
                $page_id = $succ_node["obj_id"];
                $active = ilLMPage::_lookupActive(
                    $page_id,
                    $this->lm->getType(),
                    $this->lm_set->get("time_scheduled_page_activation")
                );
            }

            if ($succ_node["type"] != "pg") {
                $this->chapter_has_no_active_page = true;
                $this->current_page_id = 0;
                return;
            }

            // if public access get first public page in chapter
            if ($user->getId() == ANONYMOUS_USER_ID &&
                $this->lm->getPublicAccessMode() == 'selected') {
                $public = ilLMObject::_isPagePublic($page_id);

                while ($public === false && $page_id > 0) {
                    $succ_node = $this->lm_tree->fetchSuccessorNode($page_id, 'pg');
                    $page_id = $succ_node['obj_id'];
                    $public = ilLMObject::_isPagePublic($page_id);
                }
            }

            // check whether page found is within "clicked" chapter
            if ($this->lm_tree->isInTree($page_id)) {
                $path = $this->lm_tree->getPathId($page_id);
                if (!in_array($this->requested_obj_id, $path)) {
                    $this->chapter_has_no_active_page = true;
                }
            }
        }

        $this->current_page_id = $page_id;
    }

    public function getBackPageId() : int
    {
        $page_id = $this->current_page_id;

        if (empty($page_id)) {
            return 0;
        }

        $back_pg = $this->requested_back_page;

        // process navigation for free page
        return $back_pg;
    }

    public function getSuccessorPageId() : int
    {
        $page_id = $this->current_page_id;
        $user_id = $this->user->getId();
        $succ_node = null;

        // determine successor page_id
        $found = false;

        // empty chapter
        if ($this->chapter_has_no_active_page &&
            ilLMObject::_lookupType($this->requested_obj_id) == "st") {
            $c_id = $this->requested_obj_id;
        } else {
            if ($this->deactivated_page) {
                $c_id = $this->requested_obj_id;
            } else {
                $c_id = $page_id;
            }
        }
        while (!$found) {
            $succ_node = $this->lm_tree->fetchSuccessorNode($c_id, "pg");
            if (is_array($succ_node)) {
                $c_id = $succ_node["obj_id"];

                $active = ilLMPage::_lookupActive(
                    $c_id,
                    $this->lm->getType(),
                    $this->lm_set->get("time_scheduled_page_activation")
                );
            }

            if (is_array($succ_node) && $succ_node["obj_id"] > 0 &&
                $user_id == ANONYMOUS_USER_ID &&
                ($this->lm->getPublicAccessMode() == "selected" &&
                    !ilLMObject::_isPagePublic($succ_node["obj_id"]))) {
                $found = false;
            } else {
                if (is_array($succ_node) && $succ_node["obj_id"] > 0 && !$active) {
                    // look, whether activation data should be shown
                    $act_data = ilLMPage::_lookupActivationData((int) $succ_node["obj_id"], $this->lm->getType());
                    if ($act_data["show_activation_info"] &&
                        (ilUtil::now() < $act_data["activation_start"])) {
                        $found = true;
                    } else {
                        $found = false;
                    }
                } else {
                    $found = true;
                }
            }
        }
        if (is_array($succ_node)) {
            return (int) $succ_node["obj_id"];
        }

        return 0;
    }

    public function getPredecessorPageId() : int
    {
        $page_id = $this->current_page_id;
        $user_id = $this->user->getId();
        $pre_node = null;

        // determine predecessor page id
        $found = false;
        if ($this->deactivated_page) {
            $c_id = $this->requested_obj_id;
        } else {
            $c_id = $page_id;
        }
        while (!$found) {
            $pre_node = $this->lm_tree->fetchPredecessorNode($c_id, "pg");
            if (is_array($pre_node)) {
                $c_id = $pre_node["obj_id"];
                $active = ilLMPage::_lookupActive(
                    $c_id,
                    $this->lm->getType(),
                    $this->lm_set->get("time_scheduled_page_activation")
                );
            }
            if (is_array($pre_node) && $pre_node["obj_id"] > 0 &&
                $user_id == ANONYMOUS_USER_ID &&
                ($this->lm->getPublicAccessMode() == "selected" &&
                    !ilLMObject::_isPagePublic($pre_node["obj_id"]))) {
                $found = false;
            } else {
                if (is_array($pre_node) && $pre_node["obj_id"] > 0 && !$active) {
                    // look, whether activation data should be shown
                    $act_data = ilLMPage::_lookupActivationData((int) $pre_node["obj_id"], $this->lm->getType());
                    if ($act_data["show_activation_info"] &&
                        (ilUtil::now() < $act_data["activation_start"])) {
                        $found = true;
                    } else {
                        $found = false;
                    }
                } else {
                    $found = true;
                }
            }
        }
        if (is_array($pre_node)) {
            return (int) $pre_node["obj_id"];
        }

        return 0;
    }
}
