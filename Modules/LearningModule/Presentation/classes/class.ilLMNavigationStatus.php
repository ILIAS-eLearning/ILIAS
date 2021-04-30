<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Checks current navigation request status
 * - determines the current page (request may e.g. pass a chapter id, in this case the next page is searched)
 * - determines if all pages of a chapter are deactivated
 * - determines if the current page is deactivated
 *
 * @author killing@leifos.de
 */
class ilLMNavigationStatus
{

    /**
     * @var int?
     */
    protected $current_page_id = null;

    /**
     * @var bool
     */
    protected $chapter_has_no_active_page = false;

    /**
     * @var bool
     */
    protected $deactivated_page = false;

    /**
     * @var ilLMTree
     */
    protected $lm;

    /**
     * @var ilLMTree
     */
    protected $lm_tree;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilSetting
     */
    protected $lm_set;

    /**
     * @var string
     */
    protected $cmd;

    /**
     * @var int
     */
    protected $focus_id;

    /**
     * @var int
     */
    protected $requested_obj_id;

    /**
     * Constructor
     * @param ilObjUser           $user
     * @param                     $request_obj_id
     * @param ilLMTree            $lm_tree
     * @param ilObjLearningModule $lm
     * @param ilSetting           $lm_set
     * @param string              $cmd
     * @param int                 $focus_id
     */
    public function __construct(
        ilObjUser $user,
        int $request_obj_id,
        ilLMTree $lm_tree,
        ilObjLearningModule $lm,
        ilSetting $lm_set,
        string $cmd,
        int $focus_id
    ) {
        $this->user = $user;
        $this->requested_obj_id = (int) $request_obj_id;
        $this->lm_tree = $lm_tree;
        $this->lm = $lm;
        $this->lm_set = $lm_set;
        $this->cmd = $cmd;
        $this->focus_id = $focus_id;

        $this->determineStatus();
    }

    /**
     * Has current chapter no avtive page?
     *
     * @return bool
     */
    public function isChapterWithoutActivePage()
    {
        return $this->chapter_has_no_active_page;
    }

    /**
     * Has current chapter no avtive page?
     *
     * @return bool
     */
    public function isDeactivatedPage()
    {
        return $this->deactivated_page;
    }

    /**
     * Has current chapter no avtive page?
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page_id;
    }


    /**
     * Determine status
     */
    protected function determineStatus()
    {
        $user = $this->user;

        $this->chapter_has_no_active_page = false;
        $this->deactivated_page = false;

        // determine object id
        if ($this->requested_obj_id == 0) {

            $obj_id = $this->lm_tree->getRootId();

            if ($this->cmd == "resume") {
                if ($user->getId() != ANONYMOUS_USER_ID && ((int) $this->focus_id == 0)) {
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
            return null;
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
                return null;
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
}
