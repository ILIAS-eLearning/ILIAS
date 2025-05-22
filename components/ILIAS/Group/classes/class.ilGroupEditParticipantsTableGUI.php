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
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup components\ILIASGroup
 */
class ilGroupEditParticipantsTableGUI extends ilTable2GUI
{
    protected ilObject $rep_object;
    protected ilPrivacySettings $privacy;
    protected ilParticipants $participants;

    /**
     * Constructor
     *
     * @access public
     * @param object parent gui object
     * @return void
     */
    public function __construct(object $a_parent_obj, ilObject $rep_object)
    {
        global $DIC;

        $this->rep_object = $rep_object;

        $this->privacy = ilPrivacySettings::getInstance();
        $this->participants = ilGroupParticipants::_getInstanceByObjId($this->rep_object->getId());
        parent::__construct($a_parent_obj, 'editMembers');
        $this->lng->loadLanguageModule('grp');
        $this->setFormName('participants');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));

        $this->addColumn($this->lng->txt('name'), 'name', '20%');
        $this->addColumn($this->lng->txt('login'), 'login', '25%');

        if ($this->privacy->enabledGroupAccessTimes()) {
            $this->addColumn($this->lng->txt('last_access'), 'access_time');
        }
        $this->addColumn($this->lng->txt('grp_mem_contacts'), 'contact');
        $this->addColumn($this->lng->txt('grp_notification'), 'notification');
        $this->addColumn($this->lng->txt('objs_role'), 'roles');

        $this->addCommandButton('updateParticipants', $this->lng->txt('save'));
        $this->addCommandButton('participants', $this->lng->txt('cancel'));

        $this->setRowTemplate("tpl.edit_participants_row.html", "components/ILIAS/Group");

        $this->disable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->disable('select_all');
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['lastname'] . ', ' . $a_set['firstname']);

        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);

        if ($this->privacy->enabledGroupAccessTimes()) {
            $this->tpl->setVariable('VAL_ACCESS', $a_set['access_time']);
        }
        $this->tpl->setVariable('VAL_CONTACT_CHECKED', $a_set['contact'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('VAL_NOTIFICATION_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NOTIFICATION_CHECKED', $a_set['notification'] ? 'checked="checked"' : '');

        $this->tpl->setVariable('NUM_ROLES', count($this->participants->getRoles()));

        $assigned = $this->participants->getAssignedRoles((int) $a_set['usr_id']);
        foreach ($this->rep_object->getLocalGroupRoles(true) as $name => $role_id) {
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
