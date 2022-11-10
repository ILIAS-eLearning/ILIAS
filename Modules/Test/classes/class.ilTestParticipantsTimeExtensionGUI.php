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
 * Class ilTestParticipantsTimeExtensionGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilTestParticipantsTimeExtensionGUI: ilTimingOverviewTableGUI
 */
class ilTestParticipantsTimeExtensionGUI
{
    /**
     * Command Constants
     */
    public const CMD_SHOW_LIST = 'showList';
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SET_TIMING = 'setTiming';

    /**
     * @var ilObjTest
     */
    protected $testObj;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilObjTest $testObj)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->main_tpl = $this->dic->ui()->mainTemplate();
        $this->testObj = $testObj;
    }

    /**
     * @return ilObjTest
     */
    public function getTestObj(): ilObjTest
    {
        return $this->testObj;
    }

    /**
     * @param ilObjTest $testObj
     */
    public function setTestObj($testObj)
    {
        $this->testObj = $testObj;
    }

    /**
     * @return bool
     */
    protected function isExtraTimeFeatureAvailable(): bool
    {
        return (
            $this->getTestObj()->getProcessingTimeInSeconds() > 0
            && $this->getTestObj()->getNrOfTries() == 1
        );
    }

    public function executeCommand()
    {
        if (!$this->isExtraTimeFeatureAvailable()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        switch ($this->dic->ctrl()->getNextClass($this)) {
            default:

                $command = $this->dic->ctrl()->getCmd(self::CMD_SHOW_LIST) . 'Cmd';

                $this->{$command}();
        }
    }

    public function showListCmd()
    {
        $tableGUI = new ilTimingOverviewTableGUI($this, self::CMD_SHOW_LIST);
        $tableGUI->addCommandButton(self::CMD_SHOW_FORM, $this->dic->language()->txt('timing'));

        $participantList = new ilTestParticipantList($this->getTestObj());
        $participantList->initializeFromDbRows($this->getTestObj()->getTestParticipants());

        $participantList = $participantList->getAccessFilteredList(
            ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
        );

        $addons = $this->getTestObj()->getTimeExtensionsOfParticipants();

        $tableData = array();
        foreach ($participantList as $participant) {
            $tblRow = [
                'started' => '',
                'extratime' => 0,
                'login' => $participant->getLogin(),
                'name' => $this->dic->language()->txt("anonymous")
            ];

            $time = $this->getTestObj()->getStartingTimeOfUser($participant->getActiveId());
            if ($time) {
                $started = $this->dic->language()->txt('tst_started') . ': ' . ilDatePresentation::formatDate(
                    new ilDateTime($time, IL_CAL_UNIX)
                );

                $tblRow['started'] = $started;
            }

            $participant_id = $participant->getActiveId();
            if (array_key_exists($participant_id, $addons) && $addons[$participant_id] > 0) {
                $tblRow['extratime'] = $addons[$participant_id];
            }

            if (! $this->getTestObj()->getAnonymity()) {
                $tblRow['name'] = $participant->getLastname() . ', ' . $participant->getFirstname();
            }

            $tableData[] = $tblRow;
        }

        $tableGUI->setData($tableData);

        $this->dic->ui()->mainTemplate()->setContent($this->dic->ctrl()->getHTML($tableGUI));
    }

    protected function showFormCmd()
    {
        $this->dic->ui()->mainTemplate()->setContent($this->buildTimingForm()->getHTML());
    }


    protected function buildTimingForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("tst_change_workingtime");
        $form->setTitle($this->dic->language()->txt("tst_change_workingtime"));

        // test users
        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $participantList = new ilTestParticipantList($this->getTestObj());
        $participantList->initializeFromDbRows($this->getTestObj()->getTestParticipants());

        $participantList = $participantList->getAccessFilteredList(
            ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
        );

        $addons = $this->getTestObj()->getTimeExtensionsOfParticipants();

        $participantslist = new ilSelectInputGUI($this->dic->language()->txt('participants'), "participant");

        $options = array(
            '' => $this->dic->language()->txt('please_select'),
            '0' => $this->dic->language()->txt('all_participants')
        );

        foreach ($participantList as $participant) {
            $started = "";

            if ($this->getTestObj()->getAnonymity()) {
                $name = $this->dic->language()->txt("anonymous");
            } else {
                $name = $participant->getLastname() . ', ' . $participant->getFirstname();
            }

            $time = $this->getTestObj()->getStartingTimeOfUser($participant->getActiveId());
            if ($time) {
                $started = ", " . $this->dic->language()->txt('tst_started') . ': ' . ilDatePresentation::formatDate(new ilDateTime($time, IL_CAL_UNIX));
            }

            $participant_id = $participant->getActiveId();
            if (array_key_exists($participant_id, $addons) && $addons[$participant_id] > 0) {
                $started .= ", " . $this->dic->language()->txt('extratime') . ': ' . $addons[$participant_id] . ' ' . $this->dic->language()->txt('minutes');
            }

            $options[$participant->getActiveId()] = $participant->getLogin() . ' (' . $name . ')' . $started;
        }

        $participantslist->setRequired(true);
        $participantslist->setOptions($options);
        $form->addItem($participantslist);

        // extra time
        $extratime = new ilNumberInputGUI($this->dic->language()->txt("extratime"), "extratime");
        $extratime->setInfo($this->dic->language()->txt('tst_extratime_info'));
        $extratime->setRequired(true);
        $extratime->setMinValue(0);
        $extratime->setMinvalueShouldBeGreater(false);
        $extratime->setSuffix($this->dic->language()->txt('minutes'));
        $extratime->setSize(5);
        $form->addItem($extratime);

        if (is_array($_POST) && strlen($_POST['cmd']['timing'])) {
            $form->setValuesByArray($_POST);
        }

        $form->addCommandButton(self::CMD_SET_TIMING, $this->dic->language()->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_LIST, $this->dic->language()->txt("cancel"));

        return $form;
    }

    protected function setTimingCmd()
    {
        $form = $this->buildTimingForm();

        if ($form->checkInput()) {
            $this->getTestObj()->addExtraTime(
                $form->getInput('participant'),
                $form->getInput('extratime')
            );

            $this->main_tpl->setOnScreenMessage('success', sprintf($this->dic->language()->txt('tst_extratime_added'), $form->getInput('extratime')), true);
            $this->dic->ctrl()->redirect($this, self::CMD_SHOW_LIST);
        }

        $this->dic->ui()->mainTemplate()->setVariable("ADM_CONTENT", $form->getHTML());
    }
}
