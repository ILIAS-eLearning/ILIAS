<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/class.ilTestServiceGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
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
                require_once 'Modules/Test/classes/class.ilAssQuestionPageCommandForwarder.php';
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
        $tableGUI->initFilter();
        $tableGUI->resetOffset();
        $tableGUI->writeFilterToSession();
        $this->showVirtualPassCmd();
    }

    public function showVirtualPassResetTableFilterCmd()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'showVirtualPass');
        $tableGUI->initFilter();
        $tableGUI->resetOffset();
        $tableGUI->resetFilter();
        $this->showVirtualPassCmd();
    }
    
    private function showVirtualPassCmd()
    {
        $testSession = $this->testSessionFactory->getSession();

        if (!$this->object->getShowPassDetails()) {
            $executable = $this->object->isExecutable($testSession, $testSession->getUserId());

            if ($executable["executable"]) {
                $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
            }
        }

        // prepare generation before contents are processed (for mathjax)
        if ($this->isPdfDeliveryRequest()) {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);
        }

        $toolbar = $this->buildUserTestResultsToolbarGUI();
        $this->ctrl->setParameter($this, 'pdf', '1');
        $toolbar->setPdfExportLinkTarget($this->ctrl->getLinkTarget($this, 'showVirtualPass'));
        $this->ctrl->setParameter($this, 'pdf', '');
        $toolbar->build();
        
        $virtualSequence = $this->service->buildVirtualSequence($testSession);
        $userResults = $this->service->getVirtualSequenceUserResults($virtualSequence);
        
        require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
        $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($testSession);

        $objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $virtualSequence);
        $objectivesList->loadObjectivesTitles();

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $this->objCache);

        $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
        $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
        $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
        $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
        $testResultHeaderLabelBuilder->initObjectiveOrientedMode();

        $tpl = new ilTemplate('tpl.il_as_tst_virtual_pass_details.html', false, false, 'Modules/Test');
        
        $command_solution_details = "";
        if ($this->object->getShowSolutionDetails()) {
            $command_solution_details = "outCorrectSolution";
        }

        $questionAnchorNav = $listOfAnswers = $this->object->canShowSolutionPrintview();
        
        if ($listOfAnswers) {
            $list_of_answers = $this->getPassListOfAnswers(
                $userResults,
                $testSession->getActiveId(),
                null,
                $this->object->getShowSolutionListComparison(),
                false,
                false,
                false,
                true,
                $objectivesList,
                $testResultHeaderLabelBuilder
            );
            $tpl->setVariable("LIST_OF_ANSWERS", $list_of_answers);
        }

        foreach ($objectivesList->getObjectives() as $loId => $loTitle) {
            $userResultsForLO = $objectivesList->filterResultsByObjective($userResults, $loId);
            
            $overviewTableGUI = $this->getPassDetailsOverviewTableGUI(
                $userResultsForLO,
                $testSession->getActiveId(),
                null,
                $this,
                "showVirtualPass",
                $command_solution_details,
                $questionAnchorNav,
                $objectivesList,
                false
            );
            $overviewTableGUI->setTitle($testResultHeaderLabelBuilder->getVirtualPassDetailsHeaderLabel(
                $objectivesList->getObjectiveTitleById($loId)
            ));

            require_once 'Modules/Test/classes/class.ilTestLearningObjectivesStatusGUI.php';
            $loStatus = new ilTestLearningObjectivesStatusGUI($this->lng);
            $loStatus->setCrsObjId($this->getObjectiveOrientedContainer()->getObjId());
            $loStatus->setUsrId($testSession->getUserId());
            $lostatus = $loStatus->getHTML($loId);
            
            $tpl->setCurrentBlock('pass_details');
            $tpl->setVariable("PASS_DETAILS", $overviewTableGUI->getHTML());
            $tpl->setVariable("LO_STATUS", $lostatus);
            $tpl->parseCurrentBlock();
        }

        $this->populateContent($this->ctrl->getHTML($toolbar) . $tpl->get());
    }
}
