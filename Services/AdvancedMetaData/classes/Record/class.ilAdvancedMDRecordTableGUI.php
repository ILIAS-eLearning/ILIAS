<?php

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
 *********************************************************************/

declare(strict_types=1);

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordTableGUI extends ilTable2GUI
{
    public const ID = 'adv_md_records_tbl';

    protected ilAdvancedMDPermissionHelper $permissions;
    protected string $in_object_type_context = "";  // repo object type, if settings are not global

    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        ilAdvancedMDPermissionHelper $a_permissions,
        $a_in_object_type_context = ""
    ) {
        $this->permissions = $a_permissions;
        $this->in_object_type_context = $a_in_object_type_context;

        $this->setId(self::ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('md_adv_col_presentation_ordering'), 'position');
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
        $this->setShowRowsSelector(true);
    }

    public function numericOrdering(string $a_field): bool
    {
        if ($a_field == 'position') {
            return true;
        }
        return parent::numericOrdering($a_field);
    }

    protected function fillRow(array $a_set): void
    {
        // assigned object types
        $disabled = !$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES];

        if ($this->in_object_type_context === "") {
            // options for global administration
            $options = array(
                0 => $this->lng->txt("meta_obj_type_inactive"),
                1 => $this->lng->txt("meta_obj_type_mandatory"),
                2 => $this->lng->txt("meta_obj_type_optional")
            );
        } else {
            // options for local administration
            $options = array(
                0 => $this->lng->txt("meta_obj_type_inactive"),
                1 => $this->lng->txt("meta_obj_type_active")
            );
        }

        $do_select = true;

        foreach (ilAdvancedMDRecord::_getAssignableObjectTypes(true) as $obj_type) {
            $value = 0;
            $do_disable = false;

            // workaround for hiding portfolio pages in portfolios, since they only get
            // data from portfolio templates
            // @todo define interface for configuration of behaviour
            $hidden = false;
            if ($obj_type["obj_type"] == "prtf" && $obj_type["sub_type"] == "pfpg") {
                $hidden = true;
            }
            // EmployeeTalks get their md from templates
            if ($obj_type["obj_type"] == "tals" && $obj_type["sub_type"] == "etal") {
                $hidden = true;
            }

            foreach ($a_set['obj_types'] as $t) {
                if ($obj_type["obj_type"] == $t["obj_type"] &&
                    $obj_type["sub_type"] == $t["sub_type"]) {
                    $value = $t["optional"]
                        ? 2
                        : 1;

                    if (!$a_set["local"] && $a_set["readonly"]) {
                        // globally mandatory options should be disabled locally
                        if ($value === 1) {
                            $do_disable = true;
                            break;
                        }

                        // globally optional, locally selected global records
                        $value = (isset($a_set["local_selected"][$obj_type["obj_type"]]) &&
                            in_array($obj_type["sub_type"], $a_set["local_selected"][$obj_type["obj_type"]]))
                            ? 1
                            : 0;
                    }

                    break;
                }
            }

            // do only list context types that match the current object type
            if ($this->in_object_type_context !== "" && $this->in_object_type_context !== $obj_type["obj_type"]) {
                continue;
            }

            if (!$do_select && !$value) {
                continue;
            }

            if ($do_select) {
                $this->tpl->setCurrentBlock('ass_obj_types');
                $this->tpl->setVariable('VAL_OBJ_TYPE', $obj_type["text"]);

                $type_options = $options;
                switch ($obj_type["obj_type"]) {
                    case "talt":
                        // currently only optional records for talk templates (types)
                        unset($type_options[1]);
                        break;
                    case "rcrs":
                        // optional makes no sense for ecs-courses
                        unset($type_options[2]);
                        break;
                }
                $select = ilLegacyFormElementsUtil::formSelect(
                    $value,
                    "obj_types[" . $a_set['id'] . "][" . $obj_type["obj_type"] . ":" . $obj_type["sub_type"] . "]",
                    $type_options,
                    false,
                    true,
                    0,
                    "",
                    array("style" => "min-width:125px"),
                    $disabled || $do_disable
                );
                $this->tpl->setVariable('VAL_OBJ_TYPE_CLASS', $hidden ? 'hidden' : 'std');
                $this->tpl->setVariable('VAL_OBJ_TYPE_STATUS', $select);
                $this->tpl->parseCurrentBlock();
            } else {
                // only show object type
                $this->tpl->setCurrentBlock('ass_obj_only');
                $this->tpl->setVariable('VAL_OBJ_TYPE', $obj_type["text"]);
                $this->tpl->parseCurrentBlock();
            }
        }

        $record = ilAdvancedMDRecord::_getInstanceByRecordId((int) $a_set['id']);
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
            $this->tpl->setVariable(
                'LOCAL_OR_GLOBAL',
                $a_set['local'] ? $this->lng->txt('meta_local') : $this->lng->txt('meta_global')
            );
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

        if (($a_set["readonly"]) ||
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
                $this->tpl->setVariable(
                    'EDIT_FIELDS_LINK',
                    $this->ctrl->getLinkTarget($this->parent_obj, 'editFields')
                );
                $this->tpl->setVariable('TXT_EDIT_FIELDS', $this->lng->txt('md_adv_field_table'));
            }
        }
    }
}
