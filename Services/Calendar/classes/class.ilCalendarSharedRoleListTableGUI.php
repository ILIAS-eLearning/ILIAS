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
* @ingroup ServicesCalendar
*/

class ilCalendarSharedRoleListTableGUI extends ilTable2GUI
{
    protected $role_ids = array();
    
    /**
     * Constructor
     *
     * @access public
     * @param object gui object
     * @param string oparent command
     * @return
     */
    public function __construct($parent_obj, $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);
        
        $this->setRowTemplate('tpl.calendar_shared_role_list_row.html', 'Services/Calendar');
        
        $this->addColumn('', 'id', '1px');
        $this->addColumn($this->lng->txt('objs_role'), 'title', '75%');
        $this->addColumn($this->lng->txt('assigned_members'), 'num', '25%');
        
        $this->addMultiCommand('shareAssignRoles', $this->lng->txt('cal_share_cal'));
        $this->addMultiCommand('shareAssignRolesEditable', $this->lng->txt('cal_share_cal_editable'));
        $this->setSelectAllCheckbox('role_ids');
        $this->setPrefix('search');
    }
    
    /**
     * set users
     *
     * @access public
     * @param array array of user ids
     * @return bool
     */
    public function setRoles($a_role_ids)
    {
        $this->role_ids = $a_role_ids;
    }
    
    /**
     * fill row
     *
     * @access protected
     * @return
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        
        $this->tpl->setVariable('TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }
        $this->tpl->setVariable('NUM_USERS', $a_set['num']);
    }
    
    
    /**
     * parse
     *
     * @access public
     * @return
     */
    public function parse()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        $users = array();
        foreach ($this->role_ids as $id) {
            $tmp_data['title'] = ilObject::_lookupTitle($id);
            $tmp_data['description'] = ilObject::_lookupDescription($id);
            $tmp_data['id'] = $id;
            $tmp_data['num'] = count($rbacreview->assignedUsers($id));
            
            $roles[] = $tmp_data;
        }

        $this->setData($roles ? $roles : array());
    }
}
