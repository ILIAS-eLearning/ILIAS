<?php

declare(strict_types=1);

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilLearningSequenceEditParticipantsTableGUI extends ilTable2GUI
{
    /**
     * @var ilLearningSequenceMembershipGUI
     */
    protected $parent_gui;

    /**
     * @var ilObjLearningSequence
     */
    protected $ls_object;

    /**
     * @var ilLearningSequenceParticipants
     */
    protected $ls_participants;

    /**
     * @var ilPrivacySettings
     */
    protected $privacy_settings;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public function __construct(
        ilLearningSequenceMembershipGUI $parent_gui,
        ilObjLearningSequence $ls_object,
        ilLearningSequenceParticipants $ls_participants,
        ilPrivacySettings $privacy_settings,
        ilLanguage $lng,
        ilCtrl $ctrl
    ) {
        parent::__construct($parent_gui, 'editMembers');

        $this->parent_gui = $parent_gui;
        $this->ls_object = $ls_object;
        $this->ls_participants = $ls_participants;
        $this->privacy_settings = $privacy_settings;
        $this->lng = $lng;
        $this->ctrl = $ctrl;

        $this->setFormName('participants');
        $this->setFormAction($this->ctrl->getFormAction($parent_gui));
        $this->setRowTemplate("tpl.edit_participants_row.html", "Modules/LearningSequence");

        $this->addColumn($this->lng->txt('name'), 'name', '20%');
        $this->addColumn($this->lng->txt('login'), 'login', '25%');
        $this->addColumn($this->lng->txt('lso_notification'), 'notification');
        $this->addColumn($this->lng->txt('objs_role'), 'roles');

        if ($this->privacy_settings->enabledLearningSequenceAccessTimes()) {
            $this->addColumn($this->lng->txt('last_access'), 'access_time');
        }

        $this->addCommandButton('updateParticipants', $this->lng->txt('save'));
        $this->addCommandButton('participants', $this->lng->txt('cancel'));

        $this->disable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->disable('select_all');
    }

    public function fillRow($set)
    {
        $this->tpl->setVariable('VAL_ID', $set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $set['lastname'] . ', ' . $set['firstname']);
        $this->tpl->setVariable('VAL_LOGIN', $set['login']);
        $this->tpl->setVariable('VAL_NOTIFICATION_ID', $set['usr_id']);
        $this->tpl->setVariable('VAL_NOTIFICATION_CHECKED', $set['notification'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('NUM_ROLES', count($this->ls_participants->getRoles()));

        if ($this->privacy_settings->enabledLearningSequenceAccessTimes()) {
            $this->tpl->setVariable('VAL_ACCESS', $set['access_time']);
        }

        $assigned = $this->ls_participants->getAssignedRoles($set['usr_id']);
        foreach ($this->ls_object->getLocalLearningSequenceRoles(true) as $name => $role_id) {
            $this->tpl->setCurrentBlock('roles');
            $this->tpl->setVariable('ROLE_ID', $role_id);
            $this->tpl->setVariable('ROLE_NAME', $name);

            if (in_array($role_id, $assigned)) {
                $this->tpl->setVariable('ROLE_CHECKED', 'selected="selected"');
            }

            $this->tpl->parseCurrentBlock();
        }
    }
}
