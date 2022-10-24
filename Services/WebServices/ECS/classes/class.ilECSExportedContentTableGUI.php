<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSExportedContentTableGUI extends ilTable2GUI
{
    private ilObjectDataCache $ilObjDataCache;

    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;
        $this->ilObjDataCache = $DIC['ilObjDataCache'];

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn($this->lng->txt('title'), 'title', '40%');
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
     * @param array row data
     *
     */
    protected function fillRow(array $a_set): void
    {
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
        $settings = ilECSDataMappingSettings::getInstanceByServerId($sid);

        $values = ilECSUtils::getAdvancedMDValuesForObjId($a_set['obj_id']);

        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'lecturer')) {
            $this->tpl->setVariable('VAL_LECTURER', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'term')) {
            $this->tpl->setVariable('VAL_TERM', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'courseID')) {
            $this->tpl->setVariable('VAL_CRS_ID', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'courseType')) {
            $this->tpl->setVariable('VAL_CRS_TYPE', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'credits')) {
            $this->tpl->setVariable('VAL_CREDITS', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'semester_hours')) {
            $this->tpl->setVariable('VAL_SWS', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'room')) {
            $this->tpl->setVariable('VAL_ROOM', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, 'cycle')) {
            $this->tpl->setVariable('VAL_CYCLE', $values[$field] ?? '--');
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
     * @param array array of released content obj_ids
     *
     */
    public function parse($a_obj_ids): void
    {
        $this->ilObjDataCache->preloadObjectCache($a_obj_ids);

        // read obj_ids
        $obj_ids = array();
        foreach ($a_obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
            $ref_id = current($ref_ids);

            $obj_ids[$ref_id] = $obj_id;
        }

        $content = array();
        foreach ($obj_ids as $ref_id => $obj_id) {
            $tmp_arr['sids'] = ilECSExportManager::getInstance()->lookupServerIds($obj_id);
            $tmp_arr['ref_id'] = $ref_id;
            $tmp_arr['obj_id'] = $obj_id;
            $tmp_arr['title'] = $this->ilObjDataCache->lookupTitle((int) $obj_id);
            $tmp_arr['desc'] = $this->ilObjDataCache->lookupDescription((int) $obj_id);
            $tmp_arr['md'] = '';
            $tmp_arr['last_update'] = $this->ilObjDataCache->lookupLastUpdate((int) $obj_id);
            $content[] = $tmp_arr;
        }
        $this->setData($content);
    }
}
