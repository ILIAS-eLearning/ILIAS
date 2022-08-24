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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMNavigationRendererGUI
{
    protected string $requested_frame;
    protected int $requested_back_pg;
    protected int $requested_obj_id;
    protected ilLMPresentationLinker $linker;
    protected bool $deactivated_page;
    protected bool $chapter_has_no_active_page;
    protected ilObjUser $user;
    protected ?int $current_page;
    protected ilObjLearningModule $lm;
    protected ilLanguage $lng;
    protected bool $offline;
    protected ilLMTracker $tracker;
    protected ilLMTree $lm_tree;
    protected ilLMPresentationGUI $parent_gui;
    protected ilSetting $lm_set;
    protected ilGlobalTemplateInterface $main_tpl;
    protected string $lang;
    protected ilLMNavigationStatus $navigation_status;

    public function __construct(
        ilLMPresentationService $service,
        ilLMPresentationGUI $parent_gui,
        ilLanguage $lng,
        ilObjUser $user,
        ilGlobalTemplateInterface $main_tpl,
        int $requested_obj_id,
        string $requested_back_pg,
        string $requested_frame
    ) {
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

    public function renderTop(): string
    {
        return $this->render();
    }

    public function renderBottom(): string
    {
        return $this->render(false);
    }

    protected function render(bool $top = true): string
    {
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
                $prev_title = ilStr::shortenTextExtended($prev_title, 50, true);
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
                $succ_title = ilStr::shortenTextExtended($succ_title, 50, true);
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

        $tpl->setVariable("CLASS", ($top) ? "tnav_Top" : "bnav_Bottom");

        return $tpl->get();
    }
}
