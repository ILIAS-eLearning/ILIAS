<?php

declare(strict_types=1);

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

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldTableGUI extends ilTable2GUI
{
    protected ilClaimingPermissionHelper $permissions;
    protected bool $may_edit_pos;

    /**
     * @var string
     */
    protected string $active_language;

    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        ilAdvancedMDPermissionHelper $a_permissions,
        bool $a_may_edit_pos,
        string $active_language
    ) {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->permissions = $a_permissions;
        $this->may_edit_pos = $a_may_edit_pos;
        $this->active_language = $active_language;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'f', '1');
        $this->addColumn($this->lng->txt('position'), 'position', "5%");
        $this->addColumn($this->lng->txt('title'), 'title', "30%");
        $this->addColumn($this->lng->txt('md_adv_field_fields'), 'type', "35%");
        $this->addColumn($this->lng->txt('options'), 'searchable', "30%");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.edit_fields_row.html", "Services/AdvancedMetaData");
        $this->setDefaultOrderField("position");
        /*
         * BT 35830: disable pagination to prevent that and similar issues due to
         * this object being a sort of table/form hybrid. There should rarely be more
         * than 10 rows anyways, and the fix is only temporary until the switch to KS.
         */
        $this->setLimit(9999);
    }

    public function numericOrdering(string $a_field): bool
    {
        if ($a_field === 'position') {
            return true;
        }
        return false;
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('TXT_SEARCHABLE', $this->lng->txt('md_adv_searchable'));
        $this->tpl->setVariable('ASS_ID', $a_set['id']);
        if ($a_set['searchable']) {
            $this->tpl->setVariable('ASS_CHECKED', 'checked="checked"');
        }
        if (!$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE] ||
            !(bool) $a_set['supports_search']) {
            $this->tpl->setVariable('ASS_DISABLED', ' disabled="disabled"');
        }

        $this->tpl->setVariable('VAL_POS', $a_set['position']);
        if (!$this->may_edit_pos) {
            $this->tpl->setVariable('POS_DISABLED', ' disabled="disabled"');
        }

        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESCRIPTION', $a_set['description']);
        }

        $this->tpl->setVariable('FIELD_TYPE', $a_set['type']);

        foreach ((array) $a_set['properties'] as $key => $value) {
            $this->tpl->setCurrentBlock('field_value');
            $this->tpl->setVariable('FIELD_KEY', $key);
            $this->tpl->setVariable('FIELD_VAL', $value);
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT]) {
            $this->ctrl->setParameter($this->parent_obj, 'field_id', $a_set['id']);
            $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'editField'));
            $this->tpl->setVariable('TXT_EDIT_RECORD', $this->lng->txt('edit'));
        }
    }

    public function parseDefinitions(array $a_definitions): void
    {
        $counter = 0;
        $defs_arr = [];
        foreach ($a_definitions as $definition) {
            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($definition->getRecordId());

            $tmp_arr['position'] = ++$counter * 10;
            $tmp_arr['id'] = $definition->getFieldId();
            $tmp_arr['title'] = $field_translations->getTitleForLanguage(
                $definition->getFieldId(),
                $this->active_language
            );
            $tmp_arr['description'] = $field_translations->getDescriptionForLanguage(
                $definition->getFieldId(),
                $this->active_language
            );
            $tmp_arr['searchable'] = $definition->isSearchable();
            $tmp_arr['type'] = $this->lng->txt($definition->getTypeTitle());
            $tmp_arr['properties'] = $definition->getFieldDefinitionForTableGUI($this->active_language);
            $tmp_arr['supports_search'] = $definition->isSearchSupported();

            $tmp_arr['perm'] = $this->permissions->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                (string) $definition->getFieldId(),
                array(
                    ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE
                    )
                )
            );

            $defs_arr[] = $tmp_arr;
        }
        $this->setData($defs_arr);
    }
}
