<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilUnitConfigurationGUI.php';

/**
 * Class ilLocalUnitConfigurationGUI
 */
class ilLocalUnitConfigurationGUI extends ilUnitConfigurationGUI
{
    const REQUEST_PARAM_SUB_CONTEXT_ID = 'question_fi';
    
    /**
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return 'showLocalUnitCategories';
    }

    /**
     * @return string
     */
    public function getUnitCategoryOverviewCommand() : string
    {
        if ($this->isCRUDContext()) {
            return 'showLocalUnitCategories';
        } else {
            return 'showGlobalUnitCategories';
        }
    }

    /**
     * @return boolean
     */
    public function isCRUDContext() : bool
    {
        if (!$this->request->isset(self::REQUEST_PARAM_SUB_CONTEXT_ID) || $this->request->raw(self::REQUEST_PARAM_SUB_CONTEXT_ID) == $this->repository->getConsumerId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getUniqueId() : string
    {
        $id = $this->repository->getConsumerId();
        if ($this->isCRUDContext()) {
            $id .= '_local';
        } else {
            $id .= '_global';
        }

        return $id;
    }


    /**
     *
     */
    public function executeCommand() : void
    {
        /**
         * @var $ilHelp ilHelpGUI
         */
        global $DIC;
        $ilHelp = $DIC['ilHelp'];

        $this->ctrl->saveParameter($this, self::REQUEST_PARAM_SUB_CONTEXT_ID);

        $ilHelp->setScreenIdComponent('qpl');
        parent::executeCommand();
    }

    /**
     *
     */
    protected function handleSubtabs() : void
    {
        /**
         * @var $ilTabs ilTabsGUI
         */
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $this->ctrl->setParameter($this, self::REQUEST_PARAM_SUB_CONTEXT_ID, $this->repository->getConsumerId());
        $ilTabs->addSubTab('view_unit_ctx_local', $this->lng->txt('un_local_units'), $this->ctrl->getLinkTarget($this, 'showLocalUnitCategories'));
        $this->ctrl->setParameter($this, self::REQUEST_PARAM_SUB_CONTEXT_ID, 0);
        $ilTabs->addSubTab('view_unit_ctx_global', $this->lng->txt('un_global_units'), $this->ctrl->getLinkTarget($this, 'showGlobalUnitCategories'));
        $this->ctrl->setParameter($this, self::REQUEST_PARAM_SUB_CONTEXT_ID, '');

        if ($this->isCRUDContext()) {
            $ilTabs->activateSubTab('view_unit_ctx_local');
        } else {
            $ilTabs->activateSubTab('view_unit_ctx_global');
        }
    }

    /**
     *
     */
    protected function showLocalUnitCategories() : void
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];

        $ilToolbar->addButton($this->lng->txt('un_add_category'), $this->ctrl->getLinkTarget($this, 'showUnitCategoryCreationForm'));

        $repo = $this->repository;
        $categories = array_filter(
            $this->repository->getAllUnitCategories(),
            function (assFormulaQuestionUnitCategory $category) use ($repo) {
                return $category->getQuestionFi() == $repo->getConsumerId() ? true : false;
            }
        );
        $data = array();
        foreach ($categories as $category) {
            /**
             * @var $category assFormulaQuestionUnitCategory
             */
            $data[] = array(
                'category_id' => $category->getId(),
                'category' => $category->getDisplayString()
            );
        }

        $this->showUnitCategories($data);
    }

    /**
     * @param array $categories
     */
    protected function showUnitCategories(array $categories) : void
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilLocalUnitCategoryTableGUI.php';
        $table = new ilLocalUnitCategoryTableGUI($this, $this->getUnitCategoryOverviewCommand());
        $table->setData($categories);

        $this->tpl->setContent($table->getHTML());
    }

    protected function confirmImportGlobalCategory() : void
    {
        if (!$this->request->isset('category_id')) {
            $this->showGlobalUnitCategories();
            return;
        }
        $this->confirmImportGlobalCategories(array($this->request->raw('category_id')));
    }

    protected function confirmImportGlobalCategories(array $category_ids) : void
    {
        // @todo: Confirmation Currently not implemented, so forward to import
        $this->importGlobalCategories($category_ids);
    }

    protected function importGlobalCategories(array $category_ids) : void
    {
        if ($this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $i = 0;
        foreach ($category_ids as $category_id) {
            try {
                $category = $this->repository->getUnitCategoryById((int) $category_id);
            } catch (ilException $e) {
                continue;
            }

            // Copy admin-category to custom-category (with question_fi)
            $new_cat_id = $this->repository->copyCategory($category->getId(), $this->repository->getConsumerId());

            // Copy units to custom_category
            $this->repository->copyUnitsByCategories($category->getId(), $new_cat_id, $this->repository->getConsumerId());
            ++$i;
        }

        if ($i) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        }

        $this->ctrl->setParameter($this, 'question_fi', 0);
        $this->ctrl->redirect($this, 'showLocalUnitCategories');
    }
}
