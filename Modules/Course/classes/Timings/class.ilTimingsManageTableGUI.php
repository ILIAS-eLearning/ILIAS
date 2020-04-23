<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for timings administration
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ingroup ModulesCourse
 */
class ilTimingsManageTableGUI extends ilTable2GUI
{
    /**
     * @var \ilLogger
     */
    private $logger = null;

    private $container = null;
    private $main_container = null;
    private $failure = false;
    
    /**
     * Constructor
     */
    public function __construct($a_parent_class, $a_parent_cmd, ilObject $a_container_obj, ilObjCourse $a_main_container)
    {
        global $DIC;

        $this->logger = $DIC->logger()->obj();

        $this->container = $a_container_obj;
        $this->main_container = $a_main_container;
        $this->setId('manage_timings_' . $this->getContainerObject()->getRefId());
        
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
    
    /**
     * Init table
     */
    public function init()
    {
        $this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.crs_manage_timings_row.html', 'Modules/Course');
        
        $this->setTitle($this->lng->txt('edit_timings_list'));
        
        $this->addColumn($this->lng->txt('title'), '', '40%');
        $this->addColumn($this->lng->txt('crs_timings_short_active'), '', '', false);

        if ($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE) {
            $this->addColumn($this->lng->txt('crs_timings_short_start_end_rel'), '', '', false);
            $this->addColumn($this->lng->txt('crs_timings_time_frame'), '', '', false);
        } else {
            $this->addColumn($this->lng->txt('crs_timings_short_start_end'), '', '', false);
            $this->addColumn($this->lng->txt('crs_timings_short_end'), '');
        }
        $this->addColumn($this->lng->txt('crs_timings_short_changeable'), '', '', false);
        
        
        
        
        $this->addCommandButton('updateManagedTimings', $this->lng->txt('save'));
        #$this->addCommandButton('timingsOff', $this->lng->txt('cancel'));
        
        
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
     * @param type $set
     */
    public function fillRow($set)
    {
        if ($set['error'] == true) {
            $this->tpl->setVariable('TD_CLASS', 'warning');
        } else {
            $this->tpl->setVariable('TD_CLASS', 'std');
        }
        
        // title
        if (strlen($set['title_link'])) {
            $this->tpl->setCurrentBlock('title_link');
            $this->tpl->setVariable('TITLE_LINK', $set['title_link']);
            $this->tpl->setVariable('TITLE_LINK_NAME', $set['title']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title_plain');
            $this->tpl->setVariable('TITLE', $set['title']);
            $this->tpl->parseCurrentBlock();
        }
        if (strlen($set['desc'])) {
            $this->tpl->setCurrentBlock('item_description');
            $this->tpl->setVariable('DESC', $set['desc']);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($set['failure']) {
            $this->tpl->setCurrentBlock('alert');
            $this->tpl->setVariable('IMG_ALERT', ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable('ALT_ALERT', $this->lng->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", $this->lng->txt($set['failure']));
            $this->tpl->parseCurrentBlock();
        }
        
        // active
        $this->tpl->setVariable('NAME_ACTIVE', 'item[' . $set['ref_id'] . '][active]');
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . print_r($_POST, true));
        if ($this->getFailureStatus()) {
            $this->tpl->setVariable('CHECKED_ACTIVE', $_POST['item'][$set['ref_id']]['active'] ? 'checked="checked"' : '');
        } else {
            $this->tpl->setVariable('CHECKED_ACTIVE', ($set['item']['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) ? 'checked="checked"' : '');
        }
        
        // start
        if ($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE) {
            include_once './Services/Form/classes/class.ilDateTimeInputGUI.php';
            $dt_input = new ilDateTimeInputGUI('', 'item[' . $set['ref_id'] . '][sug_start]');
            $dt_input->setDate(new ilDate($set['item']['suggestion_start'], IL_CAL_UNIX));
            if ($this->getFailureStatus()) {
                $dt_input->setDate(new ilDate($_POST['item'][$set['ref_id']]['sug_start'], IL_CAL_DATE));
            }
            
            $this->tpl->setVariable('start_abs');
            $this->tpl->setVariable('SUG_START', $dt_input->render());
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('start_rel');
            $this->tpl->setVariable('START_REL_VAL', (int) $set['item']['suggestion_start_rel']);
            if ($this->getFailureStatus()) {
                $this->tpl->setVariable('START_REL_VAL', $_POST['item'][$set['ref_id']]['sug_start_rel']);
            } else {
                $this->tpl->setVariable('START_REL_VAL', (int) $set['item']['suggestion_start_rel']);
            }
            $this->tpl->setVariable('START_REL_NAME', 'item[' . $set['ref_id'] . '][sug_start_rel]');
            $this->tpl->parseCurrentBlock();
        }
            
        if ($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE) {
            if ($this->getFailureStatus()) {
                $this->tpl->setVariable('VAL_DURATION_A', $_POST['item'][$set['ref_id']]['duration_a']);
            } else {
                $duration = $set['item']['suggestion_end_rel'] - $set['item']['suggestion_start_rel'];
                $this->tpl->setVariable('VAL_DURATION_A', (int) $duration);
            }
            $this->tpl->setVariable('NAME_DURATION_A', 'item[' . $set['ref_id'] . '][duration_a]');
        } else {
            include_once './Services/Form/classes/class.ilDateTimeInputGUI.php';
            $dt_end = new ilDateTimeInputGUI('', 'item[' . $set['ref_id'] . '][sug_end]');
            $dt_end->setDate(new ilDate($set['item']['suggestion_end'], IL_CAL_UNIX));
            if ($this->getFailureStatus()) {
                $dt_end->setDate(new ilDate($_POST['item'][$set['ref_id']]['sug_end'], IL_CAL_DATE));
            }
            
            $this->tpl->setVariable('end_abs');
            $this->tpl->setVariable('SUG_END', $dt_end->render());
            $this->tpl->parseCurrentBlock();
        }
        
        // changeable
        $this->tpl->setVariable('NAME_CHANGE', 'item[' . $set['ref_id'] . '][change]');
        $this->tpl->setVariable('CHECKED_CHANGE', $set['item']['changeable'] ? 'checked="checked"' : '');
        if ($this->getFailureStatus()) {
            $this->tpl->setVariable('CHECKED_CHANGE', $_POST['item'][$set['ref_id']]['change'] ? 'checked="checked"' : '');
        } else {
            $this->tpl->setVariable('CHECKED_CHANGE', $set['item']['changeable'] ? 'checked="checked"' : '');
        }
    }
    
    
    /**
     * Parse table content
     */
    public function parse($a_item_data, $a_failed_update = array())
    {
        $rows = array();
        foreach ($a_item_data as $item) {
            $current_row = array();
            
            // no item groups
            if ($item['type'] == 'itgr') {
                continue;
            }
            $current_row['ref_id'] = $item['ref_id'];
            $current_row = $this->parseTitle($current_row, $item);
            
            // dubios error handling
            if (array_key_exists($item['ref_id'], $a_failed_update)) {
                $current_row['failed'] = true;
                $current_row['failure'] = $a_failed_update[$item['ref_id']];
            }
            $current_row['item'] = $item;
            
            $rows[] = $current_row;
        }
        $this->setData($rows);
    }
    
    
    
    /**
     * Parse title
     */
    protected function parseTitle($current_row, $item)
    {
        include_once './Services/Link/classes/class.ilLink.php';
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
                    include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
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
