<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\PrintView;

use \ILIAS\Export;
use ilPropertyFormGUI;
use ILIAS\Survey\Page\PageRenderer;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ResultsPerUserPrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    protected \ILIAS\Survey\Editing\EditingGUIRequest $request;
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

        $form->addCommandButton("printView", $lng->txt("print_view"));

        //$form->setTitle($lng->txt("svy_print_selection"));
        $form->setFormAction("#");

        return $form;
    }

    public function getOnSubmitCode() : string
    {
        return "event.preventDefault(); if(il.Accordion) { il.Accordion.preparePrint(); } " .
            "window.setTimeout(() => { window.print();}, 500);";
    }
}
