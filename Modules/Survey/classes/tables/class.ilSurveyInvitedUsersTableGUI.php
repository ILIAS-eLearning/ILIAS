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
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurvey
*/

class ilSurveyInvitedUsersTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->counter = 1;
        
        $this->setFormName('invitedusers');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt("login"), 'login', '');
        $this->addColumn($this->lng->txt("firstname"), 'firstname', '');
        $this->addColumn($this->lng->txt("lastname"), 'lastname', '');
    
        $this->setTitle($this->lng->txt('invited_users'), 'icon_usr.svg', $this->lng->txt('usr'));
    
        $this->setRowTemplate("tpl.il_svy_svy_invite_users_row.html", "Modules/Survey");

        $this->addMultiCommand('disinviteUserGroup', $this->lng->txt('disinvite'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");
        $this->setPrefix('user_select');
        $this->setSelectAllCheckbox('user_select');
        
        $this->enable('header');
        $this->disable('sort');
        $this->enable('select_all');
    }

    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        $this->tpl->setVariable("USER_ID", $data['usr_id']);
        $this->tpl->setVariable("LOGIN", $data['login']);
        $this->tpl->setVariable("FIRSTNAME", $data['firstname']);
        $this->tpl->setVariable("LASTNAME", $data['lastname']);
    }
}
