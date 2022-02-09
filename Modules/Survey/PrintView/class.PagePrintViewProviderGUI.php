<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use \ILIAS\Export;
use ilPropertyFormGUI;
use ILIAS\Survey\Page\PageRenderer;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PagePrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    protected Editing\EditingGUIRequest $request;
    protected \ilObjSurvey $survey;
    protected int $ref_id;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct(
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        int $ref_id
    ) {
        global $DIC;

        $this->request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->ref_id = $ref_id;
        $this->survey = new \ilObjSurvey($this->ref_id);
    }

    public function getTemplateInjectors() : array
    {
        return [
            function ($tpl) {
                //$tpl add js/css
            }
        ];
    }

    public function getSelectionForm() : ?ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new \ilPropertyFormGUI();

        $radg = new \ilRadioGroupInputGUI($lng->txt("svy_selection"), "print_selection");
        $radg->setValue("page");
        $op1 = new \ilRadioOption($lng->txt("svy_current_page"), "page");
        $radg->addOption($op1);
        $op2 = new \ilRadioOption($lng->txt("svy_all_pages"), "all");
        $radg->addOption($op2);

        $form->addItem($radg);

        $form->addCommandButton("printView", $lng->txt("print_view"));

        $form->setTitle($lng->txt("svy_print_selection"));
        $ilCtrl->setParameterByClass("ilSurveyEditorGUI", "pg", $this->request->getPage());
        $form->setFormAction(
            $ilCtrl->getFormActionByClass(
                "ilSurveyEditorGUI",
                "printView"
            )
        );

        return $form;
    }

    public function getPages() : array
    {
        $print_pages = [];


        $pages = $this->survey->getSurveyPages();
        if ($this->request->getPrintSelection() == "page") {
            $pg = $this->request->getPage();
            if ($pg == 0) {
                $pg = 1;
            }
            $pages = [$pages[$pg - 1]];
        }

        foreach ($pages as $page) {
            $page_renderer = new PageRenderer(
                $this->survey,
                $page
            );
            $print_pages[] = $page_renderer->render();
        }
        return $print_pages;
    }
}
