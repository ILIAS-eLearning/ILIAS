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
 * Class ilLocalUnitConfigurationGUI
 */
class ilLocalUnitConfigurationGUI extends ilUnitConfigurationGUI
{
    private const REQUEST_PARAM_SUB_CONTEXT_ID = 'question_fi';

    protected function getDefaultCommand(): string
    {
        return 'showLocalUnitCategories';
    }

    public function getUnitCategoryOverviewCommand(): string
    {
        if ($this->isCRUDContext()) {
            return 'showLocalUnitCategories';
        }

        return 'showGlobalUnitCategories';
    }

    public function isCRUDContext(): bool
    {
        if (!$this->request->isset(self::REQUEST_PARAM_SUB_CONTEXT_ID) ||
            $this->request->raw(self::REQUEST_PARAM_SUB_CONTEXT_ID) == $this->repository->getConsumerId()) {
            return true;
        }

        return false;
    }

    public function getUniqueId(): string
    {
        $id = $this->repository->getConsumerId();
        if ($this->isCRUDContext()) {
            $id .= '_local';
        } else {
            $id .= '_global';
        }

        return $id;
    }

    public function executeCommand(): void
    {
        global $DIC;

        /** @var ilHelpGUI $ilHelp */
        $ilHelp = $DIC['ilHelp'];

        $this->ctrl->saveParameter($this, self::REQUEST_PARAM_SUB_CONTEXT_ID);

        $ilHelp->setScreenIdComponent('qpl');
        parent::executeCommand();
    }

    protected function handleSubtabs(): void
    {
        global $DIC;

        $ilTabs = $DIC->tabs();

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

    protected function showLocalUnitCategories(): void
    {
        global $DIC;

        $ilToolbar = $DIC->toolbar();

        $ilToolbar->addButton($this->lng->txt('un_add_category'), $this->ctrl->getLinkTarget($this, 'showUnitCategoryCreationForm'));

        $repo = $this->repository;
        $categories = array_filter(
            $this->repository->getAllUnitCategories(),
            static function (assFormulaQuestionUnitCategory $category) use ($repo): bool {
                return $category->getQuestionFi() === $repo->getConsumerId();
            }
        );
        $data = [];
        foreach ($categories as $category) {
            /** @var assFormulaQuestionUnitCategory $category */
            $data[] = [
                'category_id' => $category->getId(),
                'category' => $category->getDisplayString()
            ];
        }

        $this->showUnitCategories($data);
    }

    /**
     * @param array $categories
     */
    protected function showUnitCategories(array $categories): void
    {
        $table = new ilLocalUnitCategoryTableGUI($this, $this->getUnitCategoryOverviewCommand());
        $table->setData($categories);

        $this->tpl->setContent($table->getHTML());
    }

    protected function confirmImportGlobalCategory(): void
    {
        if (!$this->request->isset('category_id')) {
            $this->showGlobalUnitCategories();
            return;
        }
        $this->confirmImportGlobalCategories([$this->request->raw('category_id')]);
    }

    protected function confirmImportGlobalCategories(array $category_ids): void
    {
        // @todo: Confirmation Currently not implemented, so forward to import
        $this->importGlobalCategories($category_ids);
    }

    protected function importGlobalCategories(array $category_ids): void
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
