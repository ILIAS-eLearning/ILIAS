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
 * Table presentation for session overview
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @Id: $Id$
 */
class ilSessionOverviewTableGUI extends ilTable2GUI
{
    protected ilTree $tree;
    protected ilAccessHandler $access;
    protected array $events = [];
    
    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_crs_ref_id, array $a_members)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();

        $this->setId('sessov');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($this->lng->txt('event_overview'));
        
        $this->addColumn($this->lng->txt('name'), 'name');
        $this->addColumn($this->lng->txt('login'), 'login');
        
        $this->events = $this->gatherEvents($a_crs_ref_id);
        foreach ($this->events as $idx => $event_obj) {
            // tooltip properties
            $tt = [];
            if (trim($event_obj->getTitle())) {
                $tt[] = $event_obj->getTitle();
            }
            if (trim($event_obj->getDescription())) {
                $tt[] = $event_obj->getDescription();
            }
            if (trim($event_obj->getLocation())) {
                $tt[] = $this->lng->txt("event_location") . ': ' . $event_obj->getLocation();
            }
            $tt[] = $this->lng->txt("event_date_time") . ': ' . $event_obj->getFirstAppointment()->appointmentToString();
            
            // use title/datetime
            if (sizeof($this->events) <= 4) {
                $caption = $event_obj->getFirstAppointment()->appointmentToString();
                if (sizeof($tt) == 1) {
                    $tt = [];
                }
            }
            // use sequence
            else {
                $caption = $idx + 1;
            }
            $tt = implode("<br />\n", $tt);
            
            $this->addColumn($caption, 'event_' . $event_obj->getId(), '', false, '', $tt, true);
        }
        
        $this->setDefaultOrderField('name');
        $this->setDefaultOrderDirection('asc');
                
        $this->setRowTemplate('tpl.sess_list_row.html', 'Modules/Session');
                
        $this->getItems($this->events, $a_members);
    }
    
    protected function gatherEvents(int $a_crs_ref_id) : array
    {
        $tree = $this->tree;
        $ilAccess = $this->access;
                
        $events = [];
        foreach ($tree->getSubtree($tree->getNodeData($a_crs_ref_id), false, ['sess']) as $event_id) {
            $tmp_event = ilObjectFactory::getInstanceByRefId($event_id, false);
            if (!is_object($tmp_event) ||
                !$ilAccess->checkAccess('manage_members', '', $event_id)) {
                continue;
            }
            // sort by date of 1st appointment
            $events[$tmp_event->getFirstAppointment()->getStartingTime() . '_' . $tmp_event->getId()] = $tmp_event;
        }
        
        ksort($events);
        return array_values($events);
    }
    
    protected function getItems(array $a_events, array $a_members) : void
    {
        $data = [];
        foreach ($a_members as $user_id) {
            $name = ilObjUser::_lookupName($user_id);
            $data[$user_id] = array(
                'name' => $name['lastname'] . ', ' . $name['firstname'],
                'login' => $name['login']
            );
        }
        foreach ($a_events as $event_obj) {
            $users_of_event = ilEventParticipants::_getParticipated($event_obj->getID());
            foreach ($a_members as $user_id) {
                if (array_key_exists($user_id, $users_of_event)) {
                    $data[$user_id]['event_' . $event_obj->getId()] = true;
                } else {
                    $data[$user_id]['event_' . $event_obj->getId()] = false;
                }
            }
        }
        $this->setData($data);
    }
        
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('NAME', $a_set['name']);
        $this->tpl->setVariable('LOGIN', $a_set['login']);
        
        $this->tpl->setCurrentBlock('eventcols');
        foreach ($this->events as $event_obj) {
            if ($a_set['event_' . $event_obj->getId()]) {
                $this->tpl->setVariable("IMAGE_PARTICIPATED", ilUtil::getImagePath('icon_ok.svg'));
                $this->tpl->setVariable("PARTICIPATED", $this->lng->txt('event_participated'));
            } else {
                $this->tpl->setVariable("IMAGE_PARTICIPATED", ilUtil::getImagePath('icon_not_ok.svg'));
                $this->tpl->setVariable("PARTICIPATED", $this->lng->txt('event_not_participated'));
            }
            $this->tpl->parseCurrentBlock();
        }
    }
}
