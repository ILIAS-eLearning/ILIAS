<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table presentation for session overview
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @Id: $Id$
 */
class ilSessionOverviewTableGUI extends ilTable2GUI
{
    protected $events; // [array]
    
    public function __construct($a_parent_obj, $a_parent_cmd, $a_crs_ref_id, array $a_members)
    {
        $this->setId('sessov');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($this->lng->txt('event_overview'));
        
        $this->addColumn($this->lng->txt('name'), 'name');
        $this->addColumn($this->lng->txt('login'), 'login');
        
        $this->events = $this->gatherEvents($a_crs_ref_id);
        foreach ($this->events as $idx => $event_obj) {
            // tooltip properties
            $tt = array();
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
                    $tt = array();
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
    
    protected function gatherEvents($a_crs_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilAccess = $DIC['ilAccess'];
                
        $events = array();
        foreach ($tree->getSubtree($tree->getNodeData($a_crs_ref_id), false, 'sess') as $event_id) {
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
    
    protected function getItems(array $a_events, array $a_members)
    {
        $data = array();
        
        foreach ($a_members as $user_id) {
            $name = ilObjUser::_lookupName($user_id);
            $data[$user_id] = array(
                'name' => $name['lastname'] . ', ' . $name['firstname'],
                'login' => $name['login']
            );
        }
            include_once 'Modules/Session/classes/class.ilEventParticipants.php';
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
        
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('NAME', $a_set['name']);
        $this->tpl->setVariable('LOGIN', $a_set['login']);
        
        $this->tpl->setCurrentBlock('eventcols');
        foreach ($this->events as $event_obj) {
            if ((bool) $a_set['event_' . $event_obj->getId()]) {
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
