<?php declare(strict_types=1);

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

class ilStudyProgrammeExpandableProgressListGUI extends ilStudyProgrammeProgressListGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ilSetting $setting;
    protected ilAccess $access;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected int $indent = 0;
    protected bool $js_added = false;
    protected bool $css_added = false;

    public function __construct(ilStudyProgrammeProgress $progress)
    {
        parent::__construct($progress);

        global $DIC;
        $this->tpl = $DIC['tpl'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->setting = $DIC['ilSetting'];
        $this->access = $DIC['ilAccess'];
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
    }

    protected function getIndent() : int
    {
        return $this->indent;
    }

    public function setIndent(int $indent) : void
    {
        assert($indent >= 0);
        $this->indent = $indent;
    }

    public function getHTML() : string
    {
        $this->addJavaScript();
        $this->addCSS();
        return parent::getHTML();
    }

    protected function fillTemplate(ilTemplate $tpl) : void
    {
        parent::fillTemplate($tpl);

        if ($this->showMyProgress()) {
            $tpl->setVariable("ACTIVE_HEAD", "il_PrgAccordionHeadActive");
        }

        $tpl->setVariable("ACCORDION_ID", 'id="' . $this->getAccordionId() . '"');
        $tpl->setVariable("HREF_TITLE");

        $content = $this->getAccordionContentHTML();

        if (trim($content)) {
            $tpl->setCurrentBlock("expand");
            $tpl->setVariable("EXP_ALT", $this->lng->txt("expand"));
            $tpl->setVariable("EXP_IMG", $this->getExpandedImageURL());
            $tpl->setVariable("NOT_EXP_ALT", $this->lng->txt("expanded"));
            $tpl->setVariable("NOT_EXP_IMG", $this->getNotExpandedImageURL());
            $tpl->parseCurrentBlock();
        } else {
            $tpl->touchBlock("indent");
        }

        for ($i = 0; $i < $this->getIndent(); $i++) {
            $tpl->touchBlock("indent");
        }

        $tpl->setCurrentBlock("accordion");
        if ($this->showMyProgress()) {
            $tpl->setVariable("ACCORDION_HIDE_CONTENT");
        } else {
            $tpl->setVariable("ACCORDION_HIDE_CONTENT", "ilAccHideContent");
        }
        $tpl->setVariable("ACCORDION_CONTENT", $content);
        $this->tpl->addOnloadCode("il.Accordion.add(" . json_encode($this->getAccordionOptions(), JSON_THROW_ON_ERROR) . ");");
        $tpl->parseCurrentBlock();
    }

    protected function getAccordionContentHTML() : string
    {
        $programme = ilObjStudyProgramme::getInstanceByObjId($this->progress->getNodeId());

        if (!$programme->hasLPChildren()) {
            return $this->getAccordionContentProgressesHTML();
        }

        return $this->getAccordionContentCoursesHTML();
    }

    protected function getAccordionContentProgressesHTML() : string
    {
        // Make shouldShowSubProgress and newSubItem protected again afterwards, do
        // the same in the derived class ilStudyProgrammeIndividualPlanProgressListGUI.
        $programme = ilObjStudyProgramme::getInstanceByObjId($this->progress->getNodeId());
        $child_progresses = $programme->getChildrenProgress($this->progress);

        return implode("\n", array_map(function (ilStudyProgrammeProgress $progress) {
            if (!$this->shouldShowSubProgress($progress)) {
                return "";
            }
            $gui = $this->newSubItem($progress);
            $gui->setIndent($this->getIndent() + 1);
            return $gui->getHTML();
        }, $child_progresses));
    }

    protected function shouldShowSubProgress(ilStudyProgrammeProgress $progress) : bool
    {
        if ($progress->isRelevant()) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());

            $can_read = $this->access->checkAccess("read", "", $prg->getRefId(), "prg", $prg->getId());
            if ($this->visible_on_pd_mode === ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ && !$can_read) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function newSubItem(ilStudyProgrammeProgress $progress) : ilStudyProgrammeExpandableProgressListGUI
    {
        return new ilStudyProgrammeExpandableProgressListGUI($progress);
    }

    protected function getAccordionContentCoursesHTML() : string
    {
        $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP);

        $crs = array();
        $prg = ilObjStudyProgramme::getInstanceByObjId($this->progress->getNodeId());
        foreach ($prg->getLPChildren() as $il_obj_crs_ref) {
            if (ilObject::_exists($il_obj_crs_ref->getRefId(), true) &&
                is_null(ilObject::_lookupDeletedDate($il_obj_crs_ref->getRefId()))
            ) {
                continue;
            }

            $course = ilObjectFactory::getInstanceByRefId($il_obj_crs_ref->getTargetRefId());
            $preloader->addItem($course->getId(), $course->getType(), $course->getRefId());
            $crs[] = $course;
        }
        $preloader->preload();

        return implode("\n", array_map(function (ilObjCourse $course) {
            $item_gui = new ilStudyProgrammeCourseListGUI();
            $this->configureItemGUI($item_gui);
            $item_gui->setContainerObject(new ilStudyProgrammeContainerObjectMock($course));
            return $item_gui->getListItemHTML(
                $course->getRefId(),
                $course->getId(),
                $course->getTitle(),
                $course->getDescription()
            );
        }, $crs));
    }
    
    protected function configureItemGUI(ilStudyProgrammeCourseListGUI $item_gui) : void
    {
        $item_gui->enableComments(false);
        $item_gui->enableTags(false);
        $item_gui->enableIcon(true);
        $item_gui->enableDelete(false);
        $item_gui->enableCut(false);
        $item_gui->enableCopy(false);
        $item_gui->enableLink(false);
        $item_gui->enableInfoScreen(true);
        $item_gui->enableSubscribe(true);
        $item_gui->enableCheckbox(false);
        $item_gui->enableDescription(true);
        $item_gui->enableProperties(true);
        $item_gui->enablePreconditions(true);
        $item_gui->enableNoticeProperties(true);
        $item_gui->enableCommands(true, true);
        $item_gui->enableProgressInfo(true);
        $item_gui->setIndent($this->getIndent() + 2);
    }

    protected function getAccordionOptions() : array
    {
        return [
            "orientation" => "horizontal",
            // Most propably we don't need this. Or do we want to call ilAccordion.initById?
            "int_id" => "prg_progress_" . $this->progress->getId(),
            "initial_opened" => null,
            "behaviour" => "AllClosed", // or "FirstOpen"
            "toggle_class" => 'il_PrgAccordionToggle',
            "toggle_act_class" => 'foo',
            "content_class" => 'il_PrgAccordionContent',
            "width" => "auto",
            "active_head_class" => "il_PrgAccordionHeadActive",
            "height" => "auto",
            "id" => $this->getAccordionId(),
            "multi" => true,
            "show_all_element" => null,
            "hide_all_element" => null,
            "reset_width" => true,
        ];
    }

    protected function getAccordionId() : string
    {
        return "prg_progress_" . $this->progress->getId() . "_" . $this->getIndent();
    }

    protected function getExpandedImageURL() : string
    {
        return ilUtil::getImagePath("tree_exp.svg");
    }

    protected function getNotExpandedImageURL() : string
    {
        return ilUtil::getImagePath("tree_col.svg");
    }

    protected function getTitleAndIconTarget(ilStudyProgrammeProgress $progress) : ?string
    {
        return null;
    }

    protected function showMyProgress() : bool
    {
        $prg_progress_id = $this->request_wrapper->retrieve("prg_progress_id", $this->refinery->kindlyTo()->int());
        return  $prg_progress_id === $this->progress->getId();
    }

    /**
     * @return false|void
     */
    protected function addJavaScript()
    {
        if ($this->js_added) {
            return false;
        }

        iljQueryUtil::initjQueryUI();
        $this->tpl->addJavaScript("./Services/Accordion/js/accordion.js", true, 3);
        $this->js_added = true;
    }

    /**
     * @return false|void
     */
    protected function addCSS()
    {
        if ($this->css_added) {
            return false;
        }

        $this->tpl->addCSS("Modules/StudyProgramme/templates/css/ilStudyProgramme.css");
        $this->css_added = true;
    }
}
