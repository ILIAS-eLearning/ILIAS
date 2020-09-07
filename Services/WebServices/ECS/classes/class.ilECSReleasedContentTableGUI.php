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

include_once('Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSReleasedContentTableGUI extends ilTable2GUI
{
    protected $lng;
    protected $ctrl;
    
    /**
     * constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn($this->lng->txt('title'), 'title', '40%');
        #$this->addColumn($this->lng->txt('ecs_released_for'),'for','30%');
        $this->addColumn($this->lng->txt('ecs_meta_data'), 'md', '40%');
        $this->addColumn($this->lng->txt('last_update'), 'last_update', '10%');
        $this->setRowTemplate('tpl.released_content_row.html', 'Services/WebServices/ECS');
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param array row data
     *
     */
    public function fillRow($a_set)
    {
        include_once('./Services/Link/classes/class.ilLink.php');
        
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        $this->tpl->setVariable('VAL_LINK', ilLink::_getLink($a_set['ref_id'], 'rcrs'));
        $this->tpl->setVariable('VAL_DESC', $a_set['desc']);
        $this->tpl->setVariable('VAL_REMOTE', $a_set['from']);
        $this->tpl->setVariable('VAL_REMOTE_INFO', $a_set['from_info']);
        $this->tpl->setVariable('TXT_EMAIL', $this->lng->txt('ecs_email'));
        $this->tpl->setVariable('TXT_DNS', $this->lng->txt('ecs_dns'));
        $this->tpl->setVariable('TXT_ABR', $this->lng->txt('ecs_abr'));
        $this->tpl->setVariable('VAL_LAST_UPDATE', $a_set['last_update']);
        
        
        $this->tpl->setVariable('TXT_TERM', $this->lng->txt('ecs_field_term'));
        $this->tpl->setVariable('TXT_CRS_TYPE', $this->lng->txt('ecs_field_courseType'));
        $this->tpl->setVariable('TXT_CRS_ID', $this->lng->txt('ecs_field_courseID'));
        $this->tpl->setVariable('TXT_CREDITS', $this->lng->txt('ecs_field_credits'));
        $this->tpl->setVariable('TXT_ROOM', $this->lng->txt('ecs_field_room'));
        $this->tpl->setVariable('TXT_CYCLE', $this->lng->txt('ecs_field_cycle'));
        $this->tpl->setVariable('TXT_SWS', $this->lng->txt('ecs_field_semester_hours'));
        $this->tpl->setVariable('TXT_START', $this->lng->txt('ecs_field_begin'));
        $this->tpl->setVariable('TXT_END', $this->lng->txt('ecs_field_end'));
        $this->tpl->setVariable('TXT_LECTURER', $this->lng->txt('ecs_field_lecturer'));


        $sid = array_pop($a_set['sids']);
        include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
        $settings = ilECSDataMappingSettings::getInstanceByServerId($sid);
                
        include_once "Services/WebServices/ECS/classes/class.ilECSUtils.php";
        $values = ilECSUtils::getAdvancedMDValuesForObjId($a_set['obj_id']);
                
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'lecturer')) {
            $this->tpl->setVariable('VAL_LECTURER', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'term')) {
            $this->tpl->setVariable('VAL_TERM', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'courseID')) {
            $this->tpl->setVariable('VAL_CRS_ID', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'courseType')) {
            $this->tpl->setVariable('VAL_CRS_TYPE', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'credits')) {
            $this->tpl->setVariable('VAL_CREDITS', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'semester_hours')) {
            $this->tpl->setVariable('VAL_SWS', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'room')) {
            $this->tpl->setVariable('VAL_ROOM', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'cycle')) {
            $this->tpl->setVariable('VAL_CYCLE', isset($values[$field]) ? $values[$field] : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'begin')) {
            $this->tpl->setVariable('VAL_START', isset($values[$field]) ? ilDatePresentation::formatDate(new ilDateTime($values[$field], IL_CAL_UNIX)) : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'end')) {
            $this->tpl->setVariable('VAL_END', isset($values[$field]) ? ilDatePresentation::formatDate(new ilDateTime($values[$field], IL_CAL_UNIX)) : '--');
        }
    }
    
    /**
     * Parse
     *
     * @access public
     * @param array array of released content obj_ids
     *
     */
    public function parse($a_obj_ids)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $ilObjDataCache->preloadObjectCache($a_obj_ids);
        
        // read obj_ids
        $obj_ids = array();
        foreach ($a_obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
            $ref_id = current($ref_ids);

            $obj_ids[$ref_id] = $obj_id;
        }
        
        foreach ($obj_ids as $ref_id => $obj_id) {
            include_once './Services/WebServices/ECS/classes/class.ilECSExport.php';

            $tmp_arr['sids'] = ilECSExport::lookupServerIds($obj_id);
            $tmp_arr['ref_id'] = $ref_id;
            $tmp_arr['obj_id'] = $obj_id;
            $tmp_arr['title'] = $ilObjDataCache->lookupTitle($obj_id);
            $tmp_arr['desc'] = $ilObjDataCache->lookupDescription($obj_id);
            $tmp_arr['md'] = '';
            $tmp_arr['last_update'] = $ilObjDataCache->lookupLastUpdate($obj_id);
            $content[] = $tmp_arr;
        }
        $this->setData($content ? $content : array());
    }
}
