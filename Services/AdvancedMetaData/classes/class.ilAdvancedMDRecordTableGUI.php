<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAdvancedMetaData
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

class ilAdvancedMDRecordTableGUI extends ilTable2GUI
{
    protected $lng = null;
    protected $ctrl;
    protected $permissions; // [ilAdvancedMDPermissionHelper]
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', ilAdvancedMDPermissionHelper $a_permissions, $a_in_object_context = false)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->permissions = $a_permissions;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', '', 1);
        $this->addColumn($this->lng->txt('md_adv_col_presentation_ordering'), 'sort');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('md_fields'), 'fields');
        $this->addColumn($this->lng->txt('md_adv_scope'), 'scope');
        $this->addColumn($this->lng->txt('md_obj_types'), 'obj_types');
        $this->addColumn($this->lng->txt('md_adv_active'), 'active');
        
        $this->addColumn($this->lng->txt('actions'));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_records_row.html", "Services/AdvancedMetaData");
        $this->setDefaultOrderField('position');
        $this->setDefaultOrderDirection('asc');
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param
     *
     */
    public function fillRow($a_set)
    {
        // assigned object types
        $disabled = !$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES];
        $options = array(
            0 => $this->lng->txt("meta_obj_type_inactive"),
            1 => $this->lng->txt("meta_obj_type_mandatory"),
            2 => $this->lng->txt("meta_obj_type_optional")
        );
        
        $do_select = (!$a_set["readonly"] && !$a_set["local"]);
        foreach (ilAdvancedMDRecord::_getAssignableObjectTypes(true) as $obj_type) {
            $value = 0;
            foreach ($a_set['obj_types'] as $t) {
                if ($obj_type["obj_type"] == $t["obj_type"] &&
                    $obj_type["sub_type"] == $t["sub_type"]) {
                    if ($t["context"] &&
                        !$a_set["local"]) {
                        $obj_type["text"] = '<span class="il_ItemAlertProperty">' . $obj_type["text"] . '</span>';
                    }
                    
                    $value = $t["optional"]
                        ? 2
                        : 1;
                    break;
                }
            }
                
            if (!$do_select && !$value) {
                continue;
            }
            

            if ($do_select) {
                $this->tpl->setCurrentBlock('ass_obj_types');
                $this->tpl->setVariable('VAL_OBJ_TYPE', $obj_type["text"]);

                $type_options = $options;
                switch ($obj_type["obj_type"]) {
                    case "orgu":
                        // currently only optional records for org unit (types)
                        unset($type_options[1]);
                        break;
                    case "prg":
                        // currently only optional records for study programme (types)
                        unset($type_options[1]);
                        break;
                    case "rcrs":
                        // optional makes no sense for ecs-courses
                        unset($type_options[2]);
                        break;
                }
                $select = ilUtil::formSelect(
                    $value,
                    "obj_types[" . $a_set['id'] . "][" . $obj_type["obj_type"] . ":" . $obj_type["sub_type"] . "]",
                    $type_options,
                    false,
                    true,
                    0,
                    "",
                    array("style"=>"min-width:125px"),
                    $disabled
                );
                $this->tpl->setVariable('VAL_OBJ_TYPE_STATUS', $select);
                $this->tpl->parseCurrentBlock();
            } else {
                // only show object type
                $this->tpl->setCurrentBlock('ass_obj_only');
                $this->tpl->setVariable('VAL_OBJ_TYPE', $obj_type["text"]);
                $this->tpl->parseCurrentBlock();
            }
        }
        
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($a_set['id']);
        if (!$a_set['local'] && count($record->getScopeRefIds())) {
            $this->tpl->setCurrentBlock('scope_txt');
            $this->tpl->setVariable('LOCAL_OR_GLOBAL', $this->lng->txt('md_adv_scope_list_header'));
            $this->tpl->parseCurrentBlock();
            
            foreach ($record->getScopeRefIds() as $ref_id) {
                $this->tpl->setCurrentBlock('scope_entry');
                $this->tpl->setVariable('LINK_HREF', ilLink::_getLink($ref_id));
                $this->tpl->setVariable('LINK_NAME', ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)));
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setCurrentBlock('scope_txt');
            $this->tpl->setVariable('LOCAL_OR_GLOBAL', $a_set['local'] ? $this->lng->txt('meta_local') : $this->lng->txt('meta_global'));
            $this->tpl->parseCurrentBlock();
        }
        
        if (!$a_set["readonly"] || $a_set["local"]) {
            $this->tpl->setCurrentBlock('check_bl');
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->parseCurrentBlock();
        }


        $this->tpl->setVariable('R_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_POS', $a_set['position']);
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESCRIPTION', $a_set['description']);
        }
        $defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($a_set['id']);
        if (!count($defs)) {
            $this->tpl->setVariable('TXT_FIELDS', $this->lng->txt('md_adv_no_fields'));
        }
        foreach ($defs as $definition_obj) {
            $this->tpl->setCurrentBlock('field_entry');
            $this->tpl->setVariable('FIELD_NAME', $definition_obj->getTitle() .
                ": " . $this->lng->txt($definition_obj->getTypeTitle()));
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable('ACTIVE_CHECKED', $a_set['active'] ? ' checked="checked" ' : '');
        $this->tpl->setVariable('ACTIVE_ID', $a_set['id']);
        
        if (($a_set["readonly"] && !$a_set["optional"]) ||
            !$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
            $this->tpl->setVariable('ACTIVE_DISABLED', 'disabled="disabled"');
        }

        if (!$a_set["readonly"]) {
            $this->ctrl->setParameter($this->parent_obj, 'record_id', $a_set['id']);

            if ($a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT]) {
                $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'editRecord'));
                $this->tpl->setVariable('TXT_EDIT_RECORD', $this->lng->txt('edit'));
            }
            if ($a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_FIELDS]) {
                $this->tpl->setVariable('EDIT_FIELDS_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'editFields'));
                $this->tpl->setVariable('TXT_EDIT_FIELDS', $this->lng->txt('md_adv_field_table'));
            }
        }
    }
}
