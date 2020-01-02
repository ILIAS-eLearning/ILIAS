<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclFieldListTableGUI
 *
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Oskar Truffer <ot@studer-raimann.ch>
 * @version      $Id:
 *
 * @extends      ilTable2GUI
 * @ilCtrl_Calls ilDateTime
 */
class ilDclFieldListTableGUI extends ilTable2GUI
{

    /**
     * @var ilDclTable
     */
    protected $table;

    /**
     * @param ilDclFieldListGUI $a_parent_obj
     * @param string            $a_parent_cmd
     * @param string            $table_id
     */
    public function __construct(ilDclFieldListGUI $a_parent_obj, $a_parent_cmd, $table_id)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = ilDclCache::getTableCache($table_id);

        $this->setId('dcl_field_list');
        $this->addColumn('', '', '1', true);
        $this->addColumn($lng->txt('dcl_order'), null, '30px');
        $this->addColumn($lng->txt('dcl_fieldtitle'), null, 'auto');
        $this->addColumn($lng->txt('dcl_locked'), null, '30px', false, "", $lng->txt('dcl_locked_tooltip'));
        $this->addColumn($lng->txt('dcl_in_export'), null, '30px');
        $this->addColumn($lng->txt('dcl_description'), null, 'auto');
        $this->addColumn($lng->txt('dcl_field_datatype'), null, 'auto');
        $this->addColumn($lng->txt('dcl_required'), null, 'auto');
        $this->addColumn($lng->txt('dcl_unique'), null, 'auto');
        $this->addColumn($lng->txt('actions'), null, '30px');
        // Only add mutli command for custom fields
        if (count($this->table->getRecordFields())) {
            $this->setSelectAllCheckbox('dcl_field_ids[]');
            $this->addMultiCommand('confirmDeleteFields', $lng->txt('dcl_delete_fields'));
        }

        $ilCtrl->setParameterByClass('ildclfieldeditgui', 'table_id', $this->parent_obj->table_id);
        $ilCtrl->setParameterByClass('ildclfieldlistgui', 'table_id', $this->parent_obj->table_id);

        $this->setFormAction($ilCtrl->getFormActionByClass('ildclfieldlistgui'));
        $this->addCommandButton('save', $lng->txt('dcl_save'));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setFormName('field_list');

        //those two are important as we get our data as objects not as arrays.
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');

        $this->setTitle($lng->txt('dcl_table_list_fields'));
        $this->setRowTemplate('tpl.field_list_row.html', 'Modules/DataCollection');
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');

        $this->setData($this->table->getFields());
    }


    /**
     * @param ilDclBaseFieldModel $a_set
     */
    public function fillRow($a_set)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if (!$a_set->isStandardField()) {
            $this->tpl->setVariable('FIELD_ID', $a_set->getId());
        }

        $this->tpl->setVariable('NAME', 'order[' . $a_set->getId() . ']');
        $this->tpl->setVariable('VALUE', $this->order);

        /* Don't enable setting filter for MOB fields or reference fields that reference a MOB field */
        $show_exportable = true;

        if ($a_set->getId() == 'comments') {
            $show_exportable = false;
        }

        if ($show_exportable) {
            $this->tpl->setVariable('CHECKBOX_EXPORTABLE', 'exportable[' . $a_set->getId() . ']');
            if ($a_set->getExportable()) {
                $this->tpl->setVariable('CHECKBOX_EXPORTABLE_CHECKED', 'checked');
            }
        } else {
            $this->tpl->setVariable('NO_FILTER_EXPORTABLE', '');
        }

        if (!$a_set->isStandardField()) {
            $this->tpl->setVariable('CHECKBOX_NAME_LOCKED', 'locked[' . $a_set->getId() . ']');
            if ($a_set->getLocked()) {
                $this->tpl->setVariable('CHECKBOX_CHECKED_LOCKED', 'checked');
            }
        } else {
            $this->tpl->setVariable('NOT_LOCKED', '');
        }

        $this->order = $this->order + 10;
        $this->tpl->setVariable('ORDER_NAME', 'order[' . $a_set->getId() . ']');
        $this->tpl->setVariable('ORDER_VALUE', $this->order);

        $this->tpl->setVariable('TITLE', $a_set->getTitle());
        $this->tpl->setVariable('DESCRIPTION', $a_set->getDescription());
        $this->tpl->setVariable('DATATYPE', $a_set->getDatatypeTitle());

        if (!$a_set->isStandardField()) {
            switch ($a_set->getRequired()) {
                case 0:
                    $required = ilUtil::getImagePath('icon_not_ok.svg');
                    break;
                case 1:
                    $required = ilUtil::getImagePath('icon_ok.svg');
                    break;
            }
            switch ($a_set->isUnique()) {
                case 0:
                    $uniq = ilUtil::getImagePath('icon_not_ok.svg');
                    break;
                case 1:
                    $uniq = ilUtil::getImagePath('icon_ok.svg');
                    break;
            }
            $this->tpl->setVariable('REQUIRED', $required);
            $this->tpl->setVariable('UNIQUE', $uniq);
        } else {
            $this->tpl->setVariable('NO_REQUIRED', '');
            $this->tpl->setVariable('NO_UNIQUE', '');
        }

        $ilCtrl->setParameterByClass('ildclfieldeditgui', 'field_id', $a_set->getId());

        if (!$a_set->isStandardField()) {
            include_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($a_set->getId());
            $alist->setListTitle($lng->txt('actions'));

            if (ilObjDataCollectionAccess::hasAccessToFields($this->parent_obj->getDataCollectionObject()->ref_id, $this->table->getId())) {
                $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass('ildclfieldeditgui', 'edit'));
                $alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass('ildclfieldeditgui', 'confirmDelete'));
            }

            $this->tpl->setVariable('ACTIONS', $alist->getHTML());
        }
    }
}
