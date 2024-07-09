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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;

/**
 * Class ilUnitCategoryTableGUI
 * @abstract
 */
abstract class ilUnitCategoryTableGUI extends ilTable2GUI
{
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    private RequestDataCollector $request;

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
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->setId('ucats_' . $controller->getUniqueId());

        parent::__construct($controller, $cmd);

        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('title'), 'category', '99%');
        $this->addColumn('', '', '1%', true);

        $this->setDefaultOrderDirection('category');
        $this->setDefaultOrderDirection('ASC');

        $local_dic = QuestionPoolDIC::dic();
        $this->request = $local_dic['general_question_properties_repository'];
        $ref_id = $this->request->getRefId();
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
        $this->setRowTemplate('tpl.unit_category_row.html', 'components/ILIAS/TestQuestionPool');
    }

    abstract protected function populateTitle(): void;

    /**
     * @param array $row
     */
    public function fillRow(array $row): void
    {
        global $DIC;

        $row['chb'] = ilLegacyFormElementsUtil::formCheckbox(false, 'category_ids[]', $row['category_id']);

        $actions = [];

        $this->ctrl->setParameter($this->getParentObject(), 'category_id', $row['category_id']);
        $actions[] = $this->ui_factory->link()->standard($this->lng->txt('un_show_units'), $this->ctrl->getLinkTarget($this->getParentObject(), 'showUnitsOfCategory'));
        $ref_id = $this->request->getRefId();
        $type = ilObject::_lookupType($ref_id, true);
        if ($type === 'assf') {
            $hasAccess = $DIC->rbac()->system()->checkAccess('edit', $ref_id);
        } else {
            $hasAccess = $DIC->access()->checkAccess('edit', 'showUnitCategoryModificationForm', $ref_id) &&
            $DIC->access()->checkAccess('edit', 'confirmDeleteCategory', $ref_id);
        }
        if ($this->getParentObject()->isCRUDContext()) {
            if ($hasAccess) {
                $actions[] = $this->ui_factory->link()->standard($this->lng->txt('edit'), $this->ctrl->getLinkTarget($this->getParentObject(), 'showUnitCategoryModificationForm'));
                $actions[] = $this->ui_factory->link()->standard($this->lng->txt('delete'), $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteCategory'));
            }
        } else {
            $actions[] = $this->ui_factory->link()->standard($this->lng->txt('import'), $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmImportGlobalCategory'));
        }
        $row['title_href'] = $this->ctrl->getLinkTarget($this->getParentObject(), 'showUnitsOfCategory');
        $this->ctrl->setParameter($this->getParentObject(), 'category_id', '');
        $dropdown = $this->ui_factory->dropdown()->standard($actions)->withLabel($this->lng->txt('actions'));
        $row['actions'] = $this->ui_renderer->render($dropdown);

        parent::fillRow($row);
    }
}
