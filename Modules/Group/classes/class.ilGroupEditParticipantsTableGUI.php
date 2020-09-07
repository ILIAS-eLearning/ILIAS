<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGroup
 */
class ilGroupEditParticipantsTableGUI extends ilTable2GUI
{
    public $container = null;
    
    /**
     * @var ilObject
     */
    protected $rep_object = null;

    /**
     * Constructor
     *
     * @access public
     * @param object parent gui object
     * @return void
     */
    public function __construct($a_parent_obj, $rep_object)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule('grp');
        $this->ctrl = $ilCtrl;
        
        $this->container = $a_parent_obj;
        
        $this->rep_object = $rep_object;
        
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $this->privacy = ilPrivacySettings::_getInstance();
        
        $this->participants = ilGroupParticipants::_getInstanceByObjId($this->rep_object->getId());
        
        parent::__construct($a_parent_obj, 'editMembers');
        $this->setFormName('participants');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        
        $this->addColumn($this->lng->txt('name'), 'name', '20%');
        $this->addColumn($this->lng->txt('login'), 'login', '25%');

        if ($this->privacy->enabledGroupAccessTimes()) {
            $this->addColumn($this->lng->txt('last_access'), 'access_time');
        }
        $this->addColumn($this->lng->txt('grp_contact'), 'contact');
        $this->addColumn($this->lng->txt('grp_notification'), 'notification');
        $this->addColumn($this->lng->txt('objs_role'), 'roles');

        $this->addCommandButton('updateParticipants', $this->lng->txt('save'));
        $this->addCommandButton('participants', $this->lng->txt('cancel'));
        
        $this->setRowTemplate("tpl.edit_participants_row.html", "Modules/Group");
        
        $this->disable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->disable('select_all');
    }
    
    /**
     * fill row
     *
     * @access public
     * @param array usr_data
     */
    public function fillRow($a_set)
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
        
        $assigned = $this->participants->getAssignedRoles($a_set['usr_id']);
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
