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
class ResultsOverviewPrintViewProviderGUI extends Export\AbstractPrintViewProvider
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

    public function getTemplateInjectors(): array
    {
        return [
            static function (\ilGlobalTemplate $tpl): void {
                //$tpl add js/css
            }
        ];
    }

    public function getSelectionForm(): ?ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new \ilPropertyFormGUI();

        $form->addCommandButton("printView", $lng->txt("print_view"));

        //$form->setTitle($lng->txt("svy_print_selection"));
        $form->setFormAction("#");

        return $form;
    }

    public function getOnSubmitCode(): string
    {
        return "event.preventDefault(); if(il.Accordion) { il.Accordion.preparePrint(); } " .
            "window.setTimeout(() => { window.print();}, 500);";
    }
}
