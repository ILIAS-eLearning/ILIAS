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
     * @var ilLMNavigationStatus
     */
    protected $navigation_status;

    /**
     * Constructor
     */
    public function __construct(
        ilLMPresentationService $service,
        ilLMPresentationGUI $parent_gui,
        ilLanguage $lng,
        ilObjUser $user,
        ilGlobalPageTemplate $main_tpl,
        int $requested_obj_id,
        string $requested_back_pg,
        string $requested_frame
    ) {
        global $DIC;

        $this->user = $user;
        $this->lm_tree = $service->getLMTree();
        $this->current_page = $service->getNavigationStatus()->getCurrentPage();
        $this->lm = $service->getLearningModule();
        $this->lm_set = $service->getSettings();
        $this->lng = $lng;
        $this->offline = $service->getPresentationStatus()->offline();
        $this->tracker = $service->getTracker();
        $this->parent_gui = $parent_gui;
        $this->chapter_has_no_active_page = $service->getNavigationStatus()->isChapterWithoutActivePage();
        $this->deactivated_page = $service->getNavigationStatus()->isDeactivatedPage();
        $this->linker = $service->getLinker();
        $this->navigation_status = $service->getNavigationStatus();
        $this->requested_obj_id = $requested_obj_id;
        $back_pg = explode(":", $requested_back_pg);
        $this->requested_back_pg = (int) $back_pg[0];
        $this->requested_frame = $requested_frame;
        $this->main_tpl = $main_tpl;
        $this->lang = $service->getPresentationStatus()->getLang();
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

        // process navigation for free page
        $back_pg = $this->navigation_status->getBackPageId();
        if ($back_pg > 0) {
            $back_href =
                $this->linker->getLink(
                    "layout",
                    $back_pg,
                    "",
                    "",
                    "reduce"
                );
            $back_img =
                ilUtil::getImagePath("nav_arr2_L.png", false, "output", $this->offline);
            $tpl->setCurrentBlock("ilLMNavigation_Prev");
            $tpl->setVariable("IMG_PREV", $back_img);
            $tpl->setVariable("HREF_PREV", $back_href);
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
            $pre_id = $this->navigation_status->getPredecessorPageId();
            if ($pre_id > 0) {
                // get presentation title
                $prev_title = ilLMPageObject::_getPresentationTitle(
                    $pre_id,
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
                        $this->linker->getLink(
                            "layout",
                            $pre_id,
                            $this->requested_frame
                        );
                    $prev_target = "";
                } else {
                    if (!$this->offline) {
                        $prev_href =
                            $this->linker->getLink("layout", $pre_id);
                        $prev_target = 'target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
                    } else {
                        $prev_href =
                            $this->linker->getLink("layout", $pre_id);
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

            $succ_id = $this->navigation_status->getSuccessorPageId();
            if ($succ_id > 0) {
                // get presentation title
                $succ_title = ilLMPageObject::_getPresentationTitle(
                    $succ_id,
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
                        $this->linker->getLink(
                            "layout",
                            $succ_id,
                            $this->requested_frame
                        );
                    $succ_target = "";
                } else {
                    if (!$this->offline) {
                        $succ_href =
                            $this->linker->getLink("layout", $succ_id);
                        $succ_target = ' target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
                    } else {
                        $succ_href =
                            $this->linker->getLink("layout", $succ_id);
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
                        if ($this->tracker->hasPredIncorrectAnswers($succ_id)) {
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
