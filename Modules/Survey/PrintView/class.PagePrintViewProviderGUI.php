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

namespace ILIAS\Survey\PrintView;

use ILIAS\Export;
use ilPropertyFormGUI;
use ILIAS\Survey\Page\PageRenderer;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PagePrintViewProviderGUI extends Export\AbstractPrintViewProvider
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
            static function (\ilGlobalTemplate $tpl) : void {
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
        if ($this->request->getPrintSelection() === "page") {
            $pg = $this->request->getPage();
            if ($pg === 0) {
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
