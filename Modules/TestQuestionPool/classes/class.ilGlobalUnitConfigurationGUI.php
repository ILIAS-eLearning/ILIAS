<?php

declare(strict_types = 1);

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
 * Class ilGlobalUnitConfigurationGUI
 */
class ilGlobalUnitConfigurationGUI extends ilUnitConfigurationGUI
{
    public const REQUEST_PARAM_SUB_CONTEXT = 'context';

    protected function getDefaultCommand(): string
    {
        return 'showGlobalUnitCategories';
    }

    public function getUnitCategoryOverviewCommand(): string
    {
        return 'showGlobalUnitCategories';
    }

    public function isCRUDContext(): bool
    {
        return true;
    }

    public function getUniqueId(): string
    {
        return $this->repository->getConsumerId() . '_global';
    }

    protected function showGlobalUnitCategories(): void
    {
        global $DIC;

        $ilToolbar = $DIC->toolbar();
        $rbacsystem = $DIC->rbac()->system();

        if ($rbacsystem->checkAccess('write', $this->request->getRefId())) {
            $ilToolbar->addButton($this->lng->txt('un_add_category'), $this->ctrl->getLinkTarget($this, 'showUnitCategoryCreationForm'));
        }

        parent::showGlobalUnitCategories();
    }

    protected function showUnitCategories(array $categories): void
    {
        $table = new ilGlobalUnitCategoryTableGUI($this, $this->getUnitCategoryOverviewCommand());
        $table->setData($categories);

        $this->tpl->setContent($table->getHTML());
    }
}
