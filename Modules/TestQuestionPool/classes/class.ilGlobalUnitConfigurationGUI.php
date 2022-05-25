<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilUnitConfigurationGUI.php';

/**
 * Class ilGlobalUnitConfigurationGUI
 */
class ilGlobalUnitConfigurationGUI extends ilUnitConfigurationGUI
{
    const REQUEST_PARAM_SUB_CONTEXT = 'context';

    /**
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return 'showGlobalUnitCategories';
    }

    /**
     * @return string
     */
    public function getUnitCategoryOverviewCommand() : string
    {
        return 'showGlobalUnitCategories';
    }

    /**
     * @return boolean
     */
    public function isCRUDContext() : bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getUniqueId() : string
    {
        return $this->repository->getConsumerId() . '_global';
    }

    protected function showGlobalUnitCategories() : void
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        $rbacsystem = $DIC->rbac()->system();

        if ($rbacsystem->checkAccess('write', $this->request->getRefId())) {
            $ilToolbar->addButton($this->lng->txt('un_add_category'), $this->ctrl->getLinkTarget($this, 'showUnitCategoryCreationForm'));
        }

        parent::showGlobalUnitCategories();
    }

    /**
     * @param array $categories
     */
    protected function showUnitCategories(array $categories) : void
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilGlobalUnitCategoryTableGUI.php';
        $table = new ilGlobalUnitCategoryTableGUI($this, $this->getUnitCategoryOverviewCommand());
        $table->setData($categories);

        $this->tpl->setContent($table->getHTML());
    }
}
