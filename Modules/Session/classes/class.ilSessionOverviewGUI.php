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

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/

class ilSessionOverviewGUI
{
    protected $course_ref_id = null;
    protected $course_id = null;

    protected $lng;
    protected $tpl;
    protected $ctrl;

    /**
     * constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_crs_ref_id, ilParticipants $a_members)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('event');
        $this->lng->loadLanguageModule('crs');
        
        $this->course_ref_id = $a_crs_ref_id;
        $this->course_id = ilObject::_lookupObjId($this->course_ref_id);
        $this->members_obj = $a_members;
    }
    
    /**
     * ecxecute command
     *
     * @access public
     * @param
     * @return
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "listSessions";
                }
                $this->$cmd();
                break;
        }
    }
    /**
     * list sessions of all user
     *
     * @access public
     * @param
     * @return
     */
    public function listSessions()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilErr = $DIC['ilErr'];

        if (!$GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $this->course_ref_id)) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
        }
        
        $ilToolbar->addButton(
            $this->lng->txt('event_csv_export'),
            $this->ctrl->getLinkTarget($this, 'exportCSV')
        );
        
        include_once 'Modules/Session/classes/class.ilSessionOverviewTableGUI.php';
        
        $part = $this->members_obj->getParticipants();
        $part = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->course_ref_id,
            $part
        );
        
        $tbl = new ilSessionOverviewTableGUI($this, 'listSessions', $this->course_ref_id, $part);
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Events List CSV Export
     *
     * @access public
     * @param
     *
     */
    public function exportCSV()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilAccess = $DIC['ilAccess'];
        
        include_once('Services/Utilities/classes/class.ilCSVWriter.php');
        include_once 'Modules/Session/classes/class.ilEventParticipants.php';
        
        $part = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->course_ref_id,
            $this->members_obj->getParticipants()
        );
        $members = ilUtil::_sortIds($part, 'usr_data', 'lastname', 'usr_id');

        $events = array();
        foreach ($tree->getSubtree($tree->getNodeData($this->course_ref_id), false, 'sess') as $event_id) {
            $tmp_event = ilObjectFactory::getInstanceByRefId($event_id, false);
            if (!is_object($tmp_event) or !$ilAccess->checkAccess('manage_members', '', $event_id)) {
                continue;
            }
            $events[] = $tmp_event;
        }
        
        $this->csv = new ilCSVWriter();
        $this->csv->addColumn($this->lng->txt("lastname"));
        $this->csv->addColumn($this->lng->txt("firstname"));
        $this->csv->addColumn($this->lng->txt("login"));
        
        foreach ($events as $event_obj) {
            // TODO: do not export relative dates
            $this->csv->addColumn($event_obj->getTitle() . ' (' . $event_obj->getFirstAppointment()->appointmentToString() . ')');
        }
        
        $this->csv->addRow();
        
        foreach ($members as $user_id) {
            $name = ilObjUser::_lookupName($user_id);
            
            $this->csv->addColumn($name['lastname']);
            $this->csv->addColumn($name['firstname']);
            $this->csv->addColumn(ilObjUser::_lookupLogin($user_id));
            
            foreach ($events as $event_obj) {
                $event_part = new ilEventParticipants((int) $event_obj->getId());
                
                $this->csv->addColumn($event_part->hasParticipated($user_id) ?
                                        $this->lng->txt('event_participated') :
                                        $this->lng->txt('event_not_participated'));
            }
            
            $this->csv->addRow();
        }
        $date = new ilDate(time(), IL_CAL_UNIX);
        ilUtil::deliverData($this->csv->getCSVString(), $date->get(IL_CAL_FKT_DATE, 'Y-m-d') . "_course_events.csv", "text/csv");
    }
}
