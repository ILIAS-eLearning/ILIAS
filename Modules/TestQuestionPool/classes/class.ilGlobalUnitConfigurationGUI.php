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
    protected function getDefaultCommand()
    {
        return 'showGlobalUnitCategories';
    }

    /**
     * @return string
     */
    public function getUnitCategoryOverviewCommand()
    {
        return 'showGlobalUnitCategories';
    }

    /**
     * @return boolean
     */
    public function isCRUDContext()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->repository->getConsumerId() . '_global';
    }

    /**
     *
     */
    protected function showGlobalUnitCategories()
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];

        $ilToolbar->addButton($this->lng->txt('un_add_category'), $this->ctrl->getLinkTarget($this, 'showUnitCategoryCreationForm'));

        parent::showGlobalUnitCategories();
    }

    /**
     * @param array $categories
     */
    protected function showUnitCategories(array $categories)
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilGlobalUnitCategoryTableGUI.php';
        $table = new ilGlobalUnitCategoryTableGUI($this, $this->getUnitCategoryOverviewCommand());
        $table->setData($categories);

        $this->tpl->setContent($table->getHTML());
    }
}
