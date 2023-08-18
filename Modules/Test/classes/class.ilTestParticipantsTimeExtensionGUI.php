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
    public const CMD_SHOW_LIST = 'showList';
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SET_TIMING = 'setTiming';

    public function __construct(
        private ilObjTest $test_obj,
        private ilObjUser $user,
        private ilCtrl $ctrl,
        private illanguage $lng,
        private ilDBInterface $db,
        private ilGlobalTemplateInterface $main_tpl,
        private ilTestParticipantAccessFilterFactory $participant_access_filter
    ) {
    }

    public function getTestObj(): ilObjTest
    {
        return $this->test_obj;
    }

    public function setTestObj(ilObjTest $test_obj): void
    {
        $this->test_obj = $test_obj;
    }

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

        switch ($this->ctrl->getNextClass($this)) {
            default:

                $command = $this->ctrl->getCmd(self::CMD_SHOW_LIST) . 'Cmd';

                $this->{$command}();
        }
    }

    public function showListCmd(): void
    {
        $tabel_gui = new ilTimingOverviewTableGUI($this, self::CMD_SHOW_LIST);
        $tabel_gui->addCommandButton(self::CMD_SHOW_FORM, $this->lng->txt('timing'));

        $participant_list = new ilTestParticipantList($this->getTestObj(), $this->user, $this->lng, $this->db);
        $participant_list->initializeFromDbRows($this->getTestObj()->getTestParticipants());

        $filtered_participant_list = $participant_list->getAccessFilteredList(
            $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId())
        );

        $addons = $this->getTestObj()->getTimeExtensionsOfParticipants();

        $table_data = array();
        foreach ($filtered_participant_list as $participant) {
            $table_row = [
                'started' => '',
                'extratime' => 0,
                'login' => $participant->getLogin(),
                'name' => $this->lng->txt("anonymous")
            ];

            $time = $this->getTestObj()->getStartingTimeOfUser($participant->getActiveId());
            if ($time) {
                $started = $this->lng->txt('tst_started') . ': ' . ilDatePresentation::formatDate(
                    new ilDateTime($time, IL_CAL_UNIX)
                );

                $table_row['started'] = $started;
            }

            $participant_id = $participant->getActiveId();
            if (array_key_exists($participant_id, $addons) && $addons[$participant_id] > 0) {
                $table_row['extratime'] = $addons[$participant_id];
            }

            if (! $this->getTestObj()->getAnonymity()) {
                $table_row['name'] = $participant->getLastname() . ', ' . $participant->getFirstname();
            }

            $table_data[] = $table_row;
        }

        $tabel_gui->setData($table_data);

        $this->main_tpl->setContent($this->ctrl->getHTML($tabel_gui));
    }

    protected function showFormCmd()
    {
        $this->main_tpl->setContent($this->buildTimingForm()->getHTML());
    }


    protected function buildTimingForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("tst_change_workingtime");
        $form->setTitle($this->lng->txt("tst_change_workingtime"));

        $participant_list = new ilTestParticipantList($this->getTestObj(), $this->user, $this->lng, $this->db);
        $participant_list->initializeFromDbRows($this->getTestObj()->getTestParticipants());

        $filtered_participants_list = $participant_list->getAccessFilteredList(
            $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId())
        );

        $addons = $this->getTestObj()->getTimeExtensionsOfParticipants();

        $participantslist_input = new ilSelectInputGUI($this->lng->txt('participants'), "participant");

        $options = [
            '' => $this->lng->txt('please_select'),
            '0' => $this->lng->txt('all_participants')
        ];

        foreach ($filtered_participants_list as $participant) {
            $started = "";

            if ($this->getTestObj()->getAnonymity()) {
                $name = $this->lng->txt("anonymous");
            } else {
                $name = $participant->getLastname() . ', ' . $participant->getFirstname();
            }

            $time = $this->getTestObj()->getStartingTimeOfUser($participant->getActiveId());
            if ($time) {
                $started = ", " . $this->lng->txt('tst_started') . ': ' . ilDatePresentation::formatDate(new ilDateTime($time, IL_CAL_UNIX));
            }

            $participant_id = $participant->getActiveId();
            if (array_key_exists($participant_id, $addons) && $addons[$participant_id] > 0) {
                $started .= ", " . $this->lng->txt('extratime') . ': ' . $addons[$participant_id] . ' ' . $this->lng->txt('minutes');
            }

            $options[$participant->getActiveId()] = $participant->getLogin() . ' (' . $name . ')' . $started;
        }

        $participantslist_input->setRequired(true);
        $participantslist_input->setOptions($options);
        $form->addItem($participantslist_input);

        // extra time
        $extratime = new ilNumberInputGUI($this->lng->txt("extratime"), "extratime");
        $extratime->setInfo($this->lng->txt('tst_extratime_info'));
        $extratime->setRequired(true);
        $extratime->setMinValue(0);
        $extratime->setMinvalueShouldBeGreater(false);
        $extratime->setSuffix($this->lng->txt('minutes'));
        $extratime->setSize(5);
        $form->addItem($extratime);

        if (is_array($_POST) && isset($_POST['cmd']['timing']) && $_POST['cmd']['timing'] != '') {
            $form->setValuesByArray($_POST);
        }

        $form->addCommandButton(self::CMD_SET_TIMING, $this->lng->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_LIST, $this->lng->txt("cancel"));

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

            $this->main_tpl->setOnScreenMessage('success', sprintf($this->lng->txt('tst_extratime_added'), $form->getInput('extratime')), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
        }

        $this->main_tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }
}
