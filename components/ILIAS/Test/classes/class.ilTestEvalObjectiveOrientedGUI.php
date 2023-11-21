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

declare(strict_types=1);

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilTestEvalObjectiveOrientedGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilTestEvalObjectiveOrientedGUI: ilTestResultsToolbarGUI
 */
class ilTestEvalObjectiveOrientedGUI extends ilTestServiceGUI
{
    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, "active_id");

        switch ($this->ctrl->getNextClass($this)) {
            case 'ilassquestionpagegui':
                $forwarder = new ilAssQuestionPageCommandForwarder();
                $forwarder->setTestObj($this->object);
                $forwarder->forward();
                break;

            default:
                $cmd = $this->ctrl->getCmd('showVirtualPass') . 'Cmd';
                $this->$cmd();
        }
    }

    public function showVirtualPassSetTableFilterCmd()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'showVirtualPass');
        $tableGUI->resetOffset();
        $tableGUI->writeFilterToSession();
        $this->showVirtualPassCmd();
    }

    public function showVirtualPassResetTableFilterCmd()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'showVirtualPass');
        $tableGUI->resetOffset();
        $tableGUI->resetFilter();
        $this->showVirtualPassCmd();
    }

    private function showVirtualPassCmd()
    {
        $test_session = $this->testSessionFactory->getSession();

        if (!$this->object->getShowPassDetails()) {
            $executable = $this->object->isExecutable($test_session, $test_session->getUserId());

            if ($executable["executable"]) {
                $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
            }
        }

        $toolbar = $this->buildUserTestResultsToolbarGUI();
        $toolbar->build();

        $virtual_sequence = $this->service->buildVirtualSequence($test_session);
        $user_results = $this->service->getVirtualSequenceUserResults($virtual_sequence);

        $objectives_adapter = ilLOTestQuestionAdapter::getInstance($test_session);

        $objectives_list = $this->buildQuestionRelatedObjectivesList($objectives_adapter, $virtual_sequence);
        $objectives_list->loadObjectivesTitles();

        $test_result_header_label_builder = new ilTestResultHeaderLabelBuilder($this->lng, $this->obj_cache);

        $test_result_header_label_builder->setObjectiveOrientedContainerId($test_session->getObjectiveOrientedContainerId());
        $test_result_header_label_builder->setUserId($test_session->getUserId());
        $test_result_header_label_builder->setTestObjId($this->object->getId());
        $test_result_header_label_builder->setTestRefId($this->object->getRefId());
        $test_result_header_label_builder->initObjectiveOrientedMode();

        $tpl = new ilTemplate('tpl.il_as_tst_virtual_pass_details.html', false, false, 'components/ILIAS/Test');

        foreach (array_keys($objectives_list->getObjectives()) as $lo_id) {
            $user_results_for_lo = $objectives_list->filterResultsByObjective($user_results, $lo_id);

            $overview_table_gui = $this->getPassDetailsOverviewTableGUI(
                $user_results_for_lo,
                $test_session->getActiveId(),
                $test_session->getPass(),
                $this,
                "showVirtualPass",
                $objectives_list,
                false
            );
            $overview_table_gui->setTitle(
                $test_result_header_label_builder->getVirtualPassDetailsHeaderLabel(
                    $objectives_list->getObjectiveTitleById($lo_id)
                )
            );

            $lo_status = new ilTestLearningObjectivesStatusGUI($this->lng, $this->ctrl, $this->testrequest);
            $lo_status->setCrsObjId($this->getObjectiveOrientedContainer()->getObjId());
            $lo_status->setUsrId($test_session->getUserId());
            $lo_status_html = $lo_status->getHTML($lo_id);

            $tpl->setCurrentBlock('pass_details');
            $tpl->setVariable("PASS_DETAILS", $overview_table_gui->getHTML());
            $tpl->setVariable("LO_STATUS", $lo_status_html);
            $tpl->parseCurrentBlock();
        }

        $this->populateContent($this->ctrl->getHTML($toolbar) . $tpl->get());
    }
}
