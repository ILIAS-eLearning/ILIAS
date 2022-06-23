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
class ResultsPerUserPrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    protected \ILIAS\Survey\Evaluation\EvaluationManager $evaluation_manager;
    protected \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request;
    protected \ILIAS\Survey\Access\AccessManager $access_manager;
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

        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->ref_id = $ref_id;
        $this->survey = new \ilObjSurvey($this->ref_id);
        $this->access_manager = $DIC->survey()
            ->internal()
            ->domain()
            ->access($this->ref_id, $DIC->user()->getId());
        $this->request = $DIC->survey()
                             ->internal()
                             ->gui()
                             ->evaluation($this->survey)
                             ->request();
        $this->evaluation_manager = $DIC->survey()
                                         ->internal()
                                         ->domain()
                                         ->evaluation(
                                             $this->survey,
                                             $DIC->user()->getId(),
                                             $this->request->getAppraiseeId(),
                                             $this->request->getRaterId()
                                         );
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
        $radg->setValue("all");
        $op1 = new \ilRadioOption($lng->txt("svy_all_participants"), "all");
        $radg->addOption($op1);
        $op2 = new \ilRadioOption($lng->txt("svy_selected_participants"), "selected");
        $radg->addOption($op2);

        $nl = new \ilNestedListInputGUI("", "active_ids");
        $op2->addSubItem($nl);

        foreach ($this->access_manager->canReadResultOfParticipants() as $participant) {
            $nl->addListNode(
                $participant["active_id"],
                $participant["fullname"],
                0,
                false,
                false
            );
        }

        $form->addItem($radg);
        $form->addCommandButton("printResultsPerUser", $lng->txt("print_view"));

        $form->setTitle($lng->txt("svy_print_selection"));
        $form->setFormAction($ilCtrl->getFormActionByClass(
            "ilSurveyEvaluationGUI",
            "printResultsPerUser"
        ));

        return $form;
    }

    public function getPages() : array
    {
        $print_pages = [];

        $data = $this->evaluation_manager->getUserSpecificResults();

        $selection = $this->request->getPrintSelection();
        $active_ids = $this->request->getActiveIds();

        $table_gui = new \ilSurveyResultsUserTableGUI(null, '');
        $filtered_data = [];
        foreach ($data as $active_id => $d) {
            if ($selection === "all" || in_array($active_id, $active_ids)) {
                $filtered_data[$active_id] = $d;
            }
        }

        $table_gui->setData($filtered_data);

        $print_pages[] = $table_gui->getHTML();

        return $print_pages;
    }
}
