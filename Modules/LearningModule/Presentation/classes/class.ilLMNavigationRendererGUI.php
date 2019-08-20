<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 * @ingroup
 */
class ilLMNavigationRendererGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var int
     */
    protected $current_page;

    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var bool
     */
    protected $offline;

    /**
     * @var ilLMTracker
     */
    protected $tracker;

    /**
     * @var ilLMTree
     */
    protected $lm_tree;

    /**
     * @var ilLMPresentationGUI
     */
    protected $parent_gui;

    /**
     * @var ilSetting
     */
    protected $lm_set;

    /**
     * @var ilGlobalPageTemplate
     */
    protected $main_tpl;

    /**
     * @var string
     */
    protected $lang;

    /**
     * Constructor
     */
    public function __construct(
        int $current_page,
        ilObjLearningModule $lm,
        bool $offline,
        bool $chapter_has_no_active_page,
        bool $deactivated_page,
        string $lang,
        ilSetting $lm_set,
        ilLMTree $lm_tree,
        ilLMPresentationGUI $parent_gui,
        ilLMTracker $tracker,
        ilLanguage $lng,
        ilObjUser $user,
        ilGlobalPageTemplate $main_tpl
    ) {
        global $DIC;

        $this->user = $user;
        $this->lm_tree = $lm_tree;
        $this->current_page = $current_page;
        $this->lm = $lm;
        $this->lm_set = $lm_set;
        $this->lng = $lng;
        $this->offline = $offline;
        $this->tracker = $tracker;
        $this->parent_gui = $parent_gui;
        $this->chapter_has_no_active_page = $chapter_has_no_active_page;
        $this->deactivated_page = $deactivated_page;

        $this->requested_obj_id = (int) $_GET["obj_id"];
        $back_pg = explode(":", $_GET["back_pg"]);
        $this->requested_back_pg = (int) $back_pg[0];
        $this->requested_frame = $_GET["frame"];
        $this->main_tpl = $main_tpl;
        $this->lang = $lang;
    }

    /**
     * Render top
     *
     * @return string
     */
    public function renderTop()
    {
        return $this->render();
    }

    /**
     * Render bottom
     *
     * @return string
     */
    public function renderBottom()
    {
        return $this->render(false);
    }


    /**
     * Render
     *
     * @return string
     */
    protected function render($top = true)
    {
        $ilUser = $this->user;

        $page_id = $this->current_page;

        $tpl = new ilTemplate("tpl.lm_navigation.html", true, true, "Modules/LearningModule/Presentation");

        if (empty($page_id)) {
            return "";
        }

        $back_pg = $this->requested_back_pg;
        $frame = $this->requested_frame;

        // process navigation for free page
        if (!$this->lm_tree->isInTree($page_id)) {
            if ($this->offline || $back_pg == 0) {
                return "";
            }

            if (!$this->lm->cleanFrames()) {
                // @todo 6.0 (move link stuff to separate class)
                $back_href =
                    $this->parent_gui->getLink(
                        $this->lm->getRefId(),
                        "layout",
                        $back_pg,
                        $frame,
                        "",
                        "reduce"
                    );
                $back_target = "";
            } else {
                $back_href =
                    $this->parent_gui->getLink(
                        $this->lm->getRefId(),
                        "layout",
                        $back_pg,
                        "",
                        "",
                        "reduce"
                    );
                $back_target = 'target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
            }
            $back_img =
                ilUtil::getImagePath("nav_arr2_L.png", false, "output", $this->offline);
            $tpl->setCurrentBlock("ilLMNavigation_Prev");
            $tpl->setVariable("IMG_PREV", $back_img);
            $tpl->setVariable("HREF_PREV", $back_href);
            $tpl->setVariable("FRAME_PREV", $back_target);
            $tpl->setVariable("TXT_PREV", $this->lng->txt("back"));
            $tpl->setVariable("ALT_PREV", $this->lng->txt("back"));
            $tpl->setVariable(
                "PREV_ACC_KEY",
                ilAccessKeyGUI::getAttribute(ilAccessKey::PREVIOUS)
            );
            $tpl->setVariable("SPACER_PREV", $this->offline
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $tpl->parseCurrentBlock();
        } else {
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
                $c_id = $succ_node["obj_id"];

                $active = ilLMPage::_lookupActive(
                    $c_id,
                    $this->lm->getType(),
                    $this->lm_set->get("time_scheduled_page_activation")
                );

                if ($succ_node["obj_id"] > 0 &&
                    $ilUser->getId() == ANONYMOUS_USER_ID &&
                    ($this->lm->getPublicAccessMode() == "selected" &&
                        !ilLMObject::_isPagePublic($succ_node["obj_id"]))) {
                    $found = false;
                } else {
                    if ($succ_node["obj_id"] > 0 && !$active) {
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

            // determine predecessor page id
            $found = false;
            if ($this->deactivated_page) {
                $c_id = $this->requested_obj_id;
            } else {
                $c_id = $page_id;
            }
            while (!$found) {
                $pre_node = $this->lm_tree->fetchPredecessorNode($c_id, "pg");
                $c_id = $pre_node["obj_id"];
                $active = ilLMPage::_lookupActive(
                    $c_id,
                    $this->lm->getType(),
                    $this->lm_set->get("time_scheduled_page_activation")
                );
                if ($pre_node["obj_id"] > 0 &&
                    $ilUser->getId() == ANONYMOUS_USER_ID &&
                    ($this->lm->getPublicAccessMode() == "selected" &&
                        !ilLMObject::_isPagePublic($pre_node["obj_id"]))) {
                    $found = false;
                } else {
                    if ($pre_node["obj_id"] > 0 && !$active) {
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


            // Determine whether the view of a learning resource should
            // be shown in the frameset of ilias, or in a separate window.
            $showViewInFrameset = true;

            if ($pre_node != "") {
                // get presentation title
                $prev_title = ilLMPageObject::_getPresentationTitle(
                    $pre_node["obj_id"],
                    $this->lm->getPageHeader(),
                    $this->lm->isActiveNumbering(),
                    $this->lm_set->get("time_scheduled_page_activation"),
                    false,
                    0,
                    $this->lang,
                    true
                );
                $prev_title = ilUtil::shortenText($prev_title, 50, true);
                $prev_img =
                    ilUtil::getImagePath("nav_arr_L.png", false, "output", $this->offline);

                if (!$this->lm->cleanFrames()) {
                    $prev_href =
                        $this->parent_gui->getLink(
                            $this->lm->getRefId(),
                            "layout",
                            $pre_node["obj_id"],
                            $this->requested_frame
                        );
                    $prev_target = "";
                } else {
                    if ($showViewInFrameset && !$this->offline) {
                        $prev_href =
                            $this->parent_gui->getLink($this->lm->getRefId(), "layout", $pre_node["obj_id"]);
                        $prev_target = 'target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
                    } else {
                        $prev_href =
                            $this->parent_gui->getLink($this->lm->getRefId(), "layout", $pre_node["obj_id"]);
                        $prev_target = 'target="_top" ';
                    }
                }

                $tpl->setCurrentBlock("ilLMNavigation_Prev");
                $tpl->setVariable("IMG_PREV", $prev_img);
                $tpl->setVariable("HREF_PREV", $prev_href);
                $tpl->setVariable("FRAME_PREV", $prev_target);
                $tpl->setVariable("TXT_PREV", $prev_title);
                $tpl->setVariable("ALT_PREV", $this->lng->txt("previous"));
                $tpl->setVariable("SPACER_PREV", $this->offline
                    ? "images/spacer.png"
                    : ilUtil::getImagePath("spacer.png"));
                $tpl->setVariable(
                    "PREV_ACC_KEY",
                    ilAccessKeyGUI::getAttribute(ilAccessKey::PREVIOUS)
                );
            }
            if ($succ_node != "") {
                // get presentation title
                $succ_title = ilLMPageObject::_getPresentationTitle(
                    $succ_node["obj_id"],
                    $this->lm->getPageHeader(),
                    $this->lm->isActiveNumbering(),
                    $this->lm_set->get("time_scheduled_page_activation"),
                    false,
                    0,
                    $this->lang,
                    true
                );
                $succ_title = ilUtil::shortenText($succ_title, 50, true);
                $succ_img =
                    ilUtil::getImagePath("nav_arr_R.png", false, "output", $this->offline);
                if (!$this->lm->cleanFrames()) {
                    $succ_href =
                        $this->parent_gui->getLink(
                            $this->lm->getRefId(),
                            "layout",
                            $succ_node["obj_id"],
                            $this->requested_frame
                        );
                    $succ_target = "";
                } else {
                    if (!$this->offline) {
                        $succ_href =
                            $this->parent_gui->getLink($this->lm->getRefId(), "layout", $succ_node["obj_id"]);
                        $succ_target = ' target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
                    } else {
                        $succ_href =
                            $this->parent_gui->getLink($this->lm->getRefId(), "layout", $succ_node["obj_id"]);
                        $succ_target = ' target="_top" ';
                    }
                }

                $tpl->setCurrentBlock("ilLMNavigation_Next");
                $tpl->setVariable("IMG_SUCC", $succ_img);
                $tpl->setVariable("HREF_SUCC", $succ_href);
                $tpl->setVariable("FRAME_SUCC", $succ_target);
                $tpl->setVariable("TXT_SUCC", $succ_title);
                $tpl->setVariable("ALT_SUCC", $this->lng->txt("next"));
                $tpl->setVariable("SPACER_SUCC", $this->offline
                    ? "images/spacer.png"
                    : ilUtil::getImagePath("spacer.png"));
                $tpl->setVariable(
                    "NEXT_ACC_KEY",
                    ilAccessKeyGUI::getAttribute(ilAccessKey::NEXT)
                );
                $tpl->parseCurrentBlock();

                // check if successor page is not restricted
                if (!$this->offline) {
                    if ($this->lm->getRestrictForwardNavigation()) {
                        if ($this->tracker->hasPredIncorrectAnswers($succ_node["obj_id"])) {
                            $this->main_tpl->addOnLoadCode("$('.ilc_page_rnav_RightNavigation').addClass('ilNoDisplay');");
                        }
                    }
                }
            }
        }

        $tpl->setVariable("CLASS", ($top) ? "tnav_Top": "bnav_Bottom");

        return $tpl->get();
    }
}
