<?php declare(strict_types=1);

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
 ********************************************************************
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
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilErrorHandling $error;
    protected ilTree $tree;
    protected ilAccessHandler $access;
    protected int $course_ref_id = 0;
    protected int $course_id = 0;
    protected ilParticipants $members_obj;
    protected ilCSVWriter $csv;

    public function __construct(int $a_crs_ref_id, ilParticipants $a_members)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->error = $DIC['ilErr'];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();

        $this->lng->loadLanguageModule('event');
        $this->lng->loadLanguageModule('crs');
        
        $this->course_ref_id = $a_crs_ref_id;
        $this->course_id = ilObject::_lookupObjId($this->course_ref_id);
        $this->members_obj = $a_members;
    }

    public function executeCommand() : void
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
     */
    public function listSessions()
    {
        $ilToolbar = $this->toolbar;
        $ilErr = $this->error;
        $ilAccess = $this->access;

        if (!$ilAccess->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $this->course_ref_id)) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
        }
        
        $ilToolbar->addButton(
            $this->lng->txt('event_csv_export'),
            $this->ctrl->getLinkTarget($this, 'exportCSV')
        );
        
        $part = $this->members_obj->getParticipants();
        $part = $ilAccess->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->course_ref_id,
            $part
        );
        
        $tbl = new ilSessionOverviewTableGUI($this, 'listSessions', $this->course_ref_id, $part);
        $this->tpl->setContent($tbl->getHTML());
    }

    public function exportCSV() : void
    {
        $tree = $this->tree;
        $ilAccess = $this->access;
        
        $part = $ilAccess->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->course_ref_id,
            $this->members_obj->getParticipants()
        );
        $members = ilUtil::_sortIds($part, 'usr_data', 'lastname', 'usr_id');

        $events = [];
        foreach ($tree->getSubtree($tree->getNodeData($this->course_ref_id), false, ['sess']) as $event_id) {
            $tmp_event = ilObjectFactory::getInstanceByRefId($event_id, false);
            if (!is_object($tmp_event) || !$ilAccess->checkAccess('manage_members', '', $event_id)) {
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
                $event_part = new ilEventParticipants($event_obj->getId());
                
                $this->csv->addColumn($event_part->hasParticipated($user_id) ?
                                        $this->lng->txt('event_participated') :
                                        $this->lng->txt('event_not_participated'));
            }
            
            $this->csv->addRow();
        }
        $date = new ilDate(time(), IL_CAL_UNIX);
        ilUtil::deliverData(
            $this->csv->getCSVString(),
            $date->get(IL_CAL_FKT_DATE, 'Y-m-d') . "_course_events.csv",
            "text/csv"
        );
    }
}
