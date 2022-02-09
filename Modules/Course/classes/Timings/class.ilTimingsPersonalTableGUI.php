<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* TableGUI class for editing personal timings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilTimingsPersonalTableGUI extends ilTable2GUI
{
    private $container = null;
    private $main_container = null;
    private $user_id = null;
    private $failure = false;
    
    /**
     * Constructor
     */
    public function __construct($a_parent_class, $a_parent_cmd, ilObject $a_container_obj, ilObjCourse $a_main_container)
    {
        $this->container = $a_container_obj;
        $this->main_container = $a_main_container;
        $this->setId('personal_timings_' . $this->getContainerObject()->getRefId());
        
        parent::__construct($a_parent_class, $a_parent_cmd);
    }
    
    /**
     * @return ilObject
     */
    public function getContainerObject()
    {
        return $this->container;
    }
    
    /**
     * @return ilObjectCourse
     */
    public function getMainContainer()
    {
        return $this->main_container;
    }
    
    public function setUserId($a_usr_id)
    {
        $this->user_id = $a_usr_id;
    }
    
    /**
     * Get user id
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * Init table
     */
    public function init()
    {
        $this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.crs_personal_timings_row.html', 'Modules/Course');
        
        $this->setTitle($this->lng->txt('crs_timings_edit_personal'));
        
        $this->addColumn($this->lng->txt('title'), '', '40%');
        $this->addColumn($this->lng->txt('crs_timings_short_start_end'), '');
        $this->addColumn($this->lng->txt('crs_timings_short_end'), '');
        $this->addColumn($this->lng->txt('crs_timings_short_changeable'), '');
        $this->addCommandButton('updatePersonalTimings', $this->lng->txt('save'));
        $this->setShowRowsSelector(false);
    }
    
    /**
     * Set status
     * @param type $a_status
     */
    public function setFailureStatus($a_status)
    {
        $this->failure = $a_status;
    }
    
    /**
     * Get failure status
     * @return type
     */
    public function getFailureStatus()
    {
        return $this->failure;
    }
    
    /**
     * Fill table row
     * @param array $a_set
     */
    public function fillRow(array $a_set) : void
    {
        if ($a_set['error'] == true) {
            $this->tpl->setVariable('TD_CLASS', 'warning');
        } else {
            $this->tpl->setVariable('TD_CLASS', 'std');
        }
        
        // title
        if (strlen($a_set['title_link'])) {
            $this->tpl->setCurrentBlock('title_link');
            $this->tpl->setVariable('TITLE_LINK', $a_set['title_link']);
            $this->tpl->setVariable('TITLE_LINK_NAME', $a_set['title']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title_plain');
            $this->tpl->setVariable('TITLE', $a_set['title']);
            $this->tpl->parseCurrentBlock();
        }
        if (strlen($a_set['desc'])) {
            $this->tpl->setCurrentBlock('item_description');
            $this->tpl->setVariable('DESC', $a_set['desc']);
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set['failure']) {
            $this->tpl->setCurrentBlock('alert');
            $this->tpl->setVariable('IMG_ALERT', ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable('ALT_ALERT', $this->lng->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", $this->lng->txt($a_set['failure']));
            $this->tpl->parseCurrentBlock();
        }
        
        // active
        $this->tpl->setVariable('NAME_ACTIVE', 'item[' . $a_set['ref_id'] . '][active]');
        $this->tpl->setVariable('CHECKED_ACTIVE', ($a_set['item']['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) ? 'checked="checked"' : '');
        
        // start
        $dt_input = new ilDateTimeInputGUI('', 'item[' . $a_set['ref_id'] . '][sug_start]');
        $dt_input->setDate(new ilDate($a_set['item']['suggestion_start'], IL_CAL_UNIX));
        if ($this->getFailureStatus()) {
            $dt_input->setDate(new ilDate($_POST['item'][$a_set['ref_id']]['sug_start'], IL_CAL_DATE));
        }
        
        if (!$a_set['item']['changeable']) {
            $dt_input->setDisabled(true);
        }
        
        $this->tpl->setVariable('start_abs');
        $this->tpl->setVariable('SUG_START', $dt_input->render());
        $this->tpl->parseCurrentBlock();
        
        // end
        $dt_end = new ilDateTimeInputGUI('', 'item[' . $a_set['ref_id'] . '][sug_end]');
        $dt_end->setDate(new ilDate($a_set['item']['suggestion_end'], IL_CAL_UNIX));
        if ($this->getFailureStatus()) {
            $dt_end->setDate(new ilDate($_POST['item'][$a_set['ref_id']]['sug_end'], IL_CAL_DATE));
        }
        
        if (!$a_set['item']['changeable']) {
            $dt_end->setDisabled(true);
        }
        
        $this->tpl->setVariable('end_abs');
        $this->tpl->setVariable('SUG_END', $dt_end->render());
        $this->tpl->parseCurrentBlock();
        
        
        // changeable
        $this->tpl->setVariable('TXT_CHANGEABLE', $a_set['item']['changeable'] ? $this->lng->txt('yes') : $this->lng->txt('no'));
    }
    
    
    /**
     * Parse table content
     */
    public function parse($a_item_data, $failed = array())
    {
        $rows = array();
        foreach ($a_item_data as $item) {
            // hide objects without timings
            if ($item['timing_type'] != ilObjectActivation::TIMINGS_PRESETTING) {
                continue;
            }
            
            $current_row = array();
            
            // no item groups
            if ($item['type'] == 'itgr') {
                continue;
            }
            $current_row['ref_id'] = $item['ref_id'];
            
            $current_row = $this->parseTitle($current_row, $item);
            
            $item = $this->parseUserTimings($item);
            $current_row['start'] = $item['suggestion_start'];

            if (array_key_exists($item['ref_id'], $failed)) {
                $current_row['failed'] = true;
                $current_row['failure'] = $failed[$item['ref_id']];
            }
            $current_row['item'] = $item;
            $rows[] = $current_row;
        }
        // stable sort first title, second start
        $rows = ilArrayUtil::sortArray($rows, 'title', 'asc', false);
        $rows = ilArrayUtil::sortArray($rows, 'start', 'asc', true);
        $this->setData($rows);
    }
    
    /**
     * Parse/read individual timings
     */
    protected function parseUserTimings($a_item)
    {
        $tu = new ilTimingUser($a_item['child'], $this->getUserId());
        
        if ($a_item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            if ($tu->getStart()->get(IL_CAL_UNIX)) {
                $a_item['suggestion_start'] = $tu->getStart()->get(IL_CAL_UNIX);
            }
            if ($tu->getEnd()->get(IL_CAL_UNIX)) {
                $a_item['suggestion_end'] = $tu->getEnd()->get(IL_CAL_UNIX);
            }
        }
        return $a_item;
    }


    
    
    /**
     * Parse title
     */
    protected function parseTitle($current_row, $item)
    {
        switch ($item['type']) {
            case 'fold':
            case 'grp':
                $current_row['title'] = $item['title'];
                $current_row['title_link'] = ilLink::_getLink($item['ref_id'], $item['type']);
                break;
            
            case 'sess':
                if (strlen($item['title'])) {
                    $current_row['title'] = $item['title'];
                } else {
                    $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item['ref_id']));
                    $current_row['title'] = ilSessionAppointment::_appointmentToString(
                        $app_info['start'],
                        $app_info['end'],
                        $app_info['fullday']
                    );
                }
                $current_row['title_link'] = ilLink::_getLink($item['ref_id'], $item['type']);
                break;
                
            default:
                $current_row['title'] = $item['title'];
                $current_row['title_link'] = '';
                break;
                
        }
        $current_row['desc'] = $item['desc'];
        
        return $current_row;
    }
}
