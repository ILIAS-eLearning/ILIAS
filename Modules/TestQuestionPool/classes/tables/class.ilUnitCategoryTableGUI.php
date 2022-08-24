<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilUnitCategoryTableGUI
 * @abstract
 */
abstract class ilUnitCategoryTableGUI extends ilTable2GUI
{
    /**
     * @param ilUnitConfigurationGUI $controller
     * @param string                 $cmd
     */
    public function __construct(ilUnitConfigurationGUI $controller, $cmd)
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setId('ucats_' . $controller->getUniqueId());

        parent::__construct($controller, $cmd);

        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('title'), 'category', '99%');
        $this->addColumn('', '', '1%', true);

        $this->setDefaultOrderDirection('category');
        $this->setDefaultOrderDirection('ASC');
        $ref_id = $DIC->testQuestionPool()->internal()->request()->getRefId();
        $type = ilObject::_lookupType($ref_id, true);
        if ($type === 'assf') {
            $hasAccess = $DIC->rbac()->system()->checkAccess('edit', $ref_id);
        } else {
            $hasAccess = $DIC->access()->checkAccess('edit', $cmd, $ref_id);
        }
        if ($hasAccess) {
            if ($this->getParentObject()->isCRUDContext()) {
                $this->addMultiCommand('confirmDeleteCategories', $this->lng->txt('delete'));
            } else {
                $this->addMultiCommand('confirmImportGlobalCategories', $this->lng->txt('import'));
            }
        }

        $this->populateTitle();

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $cmd));
        $this->setSelectAllCheckbox('category_ids[]');
        $this->setRowTemplate('tpl.unit_category_row.html', 'Modules/TestQuestionPool');
    }

    abstract protected function populateTitle(): void;

    /**
     * @param array $row
     */
    public function fillRow(array $row): void
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $row['chb'] = ilLegacyFormElementsUtil::formCheckbox(false, 'category_ids[]', $row['category_id']);

        $action = new ilAdvancedSelectionListGUI();
        $action->setId('asl_content_' . $row['category_id']);
        $action->setAsynch(false);
        $action->setListTitle($this->lng->txt('actions'));
        $ilCtrl->setParameter($this->getParentObject(), 'category_id', $row['category_id']);
        $action->addItem($this->lng->txt('un_show_units'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'showUnitsOfCategory'));
        $ref_id = $DIC->testQuestionPool()->internal()->request()->getRefId();
        $type = ilObject::_lookupType($ref_id, true);
        if ($type === 'assf') {
            $hasAccess = $DIC->rbac()->system()->checkAccess('edit', $ref_id);
        } else {
            $hasAccess = $DIC->access()->checkAccess('edit', 'showUnitCategoryModificationForm', $ref_id) &&
            $DIC->access()->checkAccess('edit', 'confirmDeleteCategory', $ref_id);
        }
        if ($this->getParentObject()->isCRUDContext()) {
            if ($hasAccess) {
                $action->addItem($this->lng->txt('edit'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'showUnitCategoryModificationForm'));
                $action->addItem($this->lng->txt('delete'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'confirmDeleteCategory'));
            }
        } else {
            $action->addItem($this->lng->txt('import'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'confirmImportGlobalCategory'));
        }
        $row['title_href'] = $ilCtrl->getLinkTarget($this->getParentObject(), 'showUnitsOfCategory');
        $ilCtrl->setParameter($this->getParentObject(), 'category_id', '');
        $row['actions'] = $action->getHtml();

        parent::fillRow($row);
    }
}
