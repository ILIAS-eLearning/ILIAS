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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSImportedContentTableGUI extends ilTable2GUI
{
    private ilTree $tree;
    private ilObjectDataCache $objDataCache;

    public function __construct(?object $a_parent_obj, string $a_parent_cmd = '')
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $this->tree = $DIC->repositoryTree();
        $this->objDataCache = $DIC['ilObjDataCache'];

        $this->addColumn($this->lng->txt('title'), 'title', '25%');
        $this->addColumn($this->lng->txt('res_links_short'), 'link', '25%');
        $this->addColumn($this->lng->txt('ecs_imported_from'), 'from', '15%');
        $this->addColumn($this->lng->txt('ecs_meta_data'), 'md', '25%');
        $this->addColumn($this->lng->txt('last_update'), 'last_update', '10%');
        $this->setRowTemplate('tpl.content_row.html', 'Services/WebServices/ECS');
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');
        if ($a_parent_obj) {
            $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        }
    }

    /**
     * Fill row
     *
     * @access public
     * @param array row data
     *
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        #$this->tpl->setVariable('VAL_LINK',ilLink::_getLink($a_set['ref_id'],'rcrs'));
        $this->tpl->setVariable('VAL_DESC', $a_set['desc']);
        $this->tpl->setVariable('VAL_REMOTE', $a_set['from']);
        $this->tpl->setVariable('VAL_REMOTE_INFO', $a_set['from_info']);
        $this->tpl->setVariable('TXT_EMAIL', $this->lng->txt('ecs_email'));
        $this->tpl->setVariable('TXT_DNS', $this->lng->txt('ecs_dns'));
        $this->tpl->setVariable('TXT_ABR', $this->lng->txt('ecs_abr'));
        $this->tpl->setVariable(
            'VAL_LAST_UPDATE',
            ilDatePresentation::formatDate(new ilDateTime($a_set['last_update'], IL_CAL_DATETIME))
        );

        // Links
        foreach (ilObject::_getAllReferences($a_set['obj_id']) as $ref_id) {
            $parent = $this->tree->getParentId($ref_id);
            $p_obj_id = ilObject::_lookupObjId($parent);
            $p_title = ilObject::_lookupTitle($p_obj_id);
            $p_type = ilObject::_lookupType($p_obj_id);
            $this->tpl->setCurrentBlock('link');
            $this->tpl->setVariable('LINK_IMG', ilObject::_getIcon($p_obj_id, 'tiny', $p_type));
            $this->tpl->setVariable('LINK_CONTAINER', $p_title);
            $this->tpl->setVariable('LINK_LINK', ilLink::_getLink($parent, $p_type));
            $this->tpl->parseCurrentBlock();
        }

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

        $settings = ilECSDataMappingSettings::getInstanceByServerId((int) $a_set['sid']);

        $values = ilECSUtils::getAdvancedMDValuesForObjId($a_set['obj_id']);

        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'lecturer')) {
            $this->tpl->setVariable('VAL_LECTURER', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'term')) {
            $this->tpl->setVariable('VAL_TERM', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'courseID')) {
            $this->tpl->setVariable('VAL_CRS_ID', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'courseType')) {
            $this->tpl->setVariable('VAL_CRS_TYPE', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'credits')) {
            $this->tpl->setVariable('VAL_CREDITS', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'semester_hours')) {
            $this->tpl->setVariable('VAL_SWS', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'room')) {
            $this->tpl->setVariable('VAL_ROOM', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'cycle')) {
            $this->tpl->setVariable('VAL_CYCLE', $values[$field] ?? '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'begin')) {
            $this->tpl->setVariable('VAL_START', isset($values[$field]) ? ilDatePresentation::formatDate(new ilDateTime($values[$field], IL_CAL_UNIX)) : '--');
        }
        if ($field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'end')) {
            $this->tpl->setVariable('VAL_END', isset($values[$field]) ? ilDatePresentation::formatDate(new ilDateTime($values[$field], IL_CAL_UNIX)) : '--');
        }
    }

    /**
     * Parse
     *
     * @access public
     * @param array array of remote course ids
     *
     */
    public function parse($a_rcrs): void
    {
        // Preload object data
        $this->objDataCache->preloadReferenceCache($a_rcrs);

        // Read participants

        // read obj_ids
        $obj_ids = array();
        foreach ($a_rcrs as $rcrs_ref_id) {
            $obj_id = $this->objDataCache->lookupObjId((int) $rcrs_ref_id);
            $obj_ids[$obj_id] = $this->objDataCache->lookupObjId((int) $rcrs_ref_id);
        }
        $content = array();
        foreach ($obj_ids as $obj_id => $obj_id) {
            $rcourse = new ilObjRemoteCourse($obj_id, false);
            $tmp_arr['obj_id'] = $obj_id;
            $tmp_arr['sid'] = ilECSImportManager::getInstance()->lookupServerId($obj_id);
            $tmp_arr['title'] = $rcourse->getTitle();
            $tmp_arr['desc'] = $rcourse->getDescription();
            $tmp_arr['md'] = '';

            $mid = $rcourse->getMID();

            if ($tmp_arr['sid']) {
                try {
                    $reader = ilECSCommunityReader::getInstanceByServerId($tmp_arr['sid']);
                } catch (ilECSConnectorException $e) {
                    $reader = null;
                }

                if ($reader && ($participant = $reader->getParticipantByMID($mid))) {
                    $tmp_arr['from'] = $participant->getParticipantName();
                    $tmp_arr['from_info'] = $participant->getDescription();
                }
            } else {
                $tmp_arr['from'] = $this->lng->txt("ecs_server_deleted");
                $tmp_arr['from_info'] = "";
            }

            $tmp_arr['last_update'] = $this->objDataCache->lookupLastUpdate((int) $obj_id);
            $content[] = $tmp_arr;
        }

        $this->setData($content);
    }
}
