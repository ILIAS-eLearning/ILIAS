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

/**
 * Class ilUnitTableGUI
 */
class ilUnitTableGUI extends ilTable2GUI
{
    /**
     * @var int
     */
    private $position = 1;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    /**
     * @param ilUnitConfigurationGUI         $controller
     * @param string                         $default_cmd
     * @param assFormulaQuestionUnitCategory $category
     */
    public function __construct(ilUnitConfigurationGUI $controller, $default_cmd, assFormulaQuestionUnitCategory $category)
    {
        /**
         * @var $ilCtrl ilCtrl
         * @var $lng    ilLanguage
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->setId('units_' . $controller->getUniqueId());

        parent::__construct($controller, $default_cmd);

        $ilCtrl->setParameter($this->getParentObject(), 'category_id', $category->getId());

        if ($this->getParentObject()->isCRUDContext()) {
            $this->addColumn('', '', '1%', true);
            $this->setSelectAllCheckbox('unit_ids[]');
            $this->addMultiCommand('confirmDeleteUnits', $this->lng->txt('delete'));
            $this->addCommandButton('saveOrder', $this->lng->txt('un_save_order'));
        }

        $this->setTitle(sprintf($this->lng->txt('un_units_of_category_x'), $category->getDisplayString()));

        $this->addColumn($this->lng->txt('un_sequence'), '');
        $this->addColumn($this->lng->txt('unit'), '');
        $this->addColumn($this->lng->txt('baseunit'), '');
        $this->addColumn($this->lng->txt('factor'), '');
        $this->addColumn('', '', '1%', true);

        // Show all units because of sorting
        $this->setLimit(PHP_INT_MAX);

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), 'showUnitsOfCategory'));

        $this->setDefaultOrderDirection('sequence');
        $this->setDefaultOrderDirection('ASC');

        $this->setRowTemplate('tpl.unit_row_html', 'Modules/TestQuestionPool');
    }

    /**
     * @param array $a_set
     */
    public function fillRow(array $a_set): void
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->getParentObject()->isCRUDContext()) {
            $a_set['chb'] = ilLegacyFormElementsUtil::formCheckbox(false, 'unit_ids[]', $a_set['unit_id']);

            $sequence = new ilNumberInputGUI('', 'sequence[' . $a_set['unit_id'] . ']');
            $sequence->setValue($this->position++ * 10);
            $sequence->setMinValue(0);
            $sequence->setSize(3);
            $a_set['sequence'] = $sequence->render();

            $actions = [];
            $ilCtrl->setParameter($this->getParentObject(), 'unit_id', $a_set['unit_id']);
            $actions[] = $this->ui_factory->link()->standard($this->lng->txt('edit'), $ilCtrl->getLinkTarget($this->getParentObject(), 'showUnitModificationForm'));
            $actions[] = $this->ui_factory->link()->standard($this->lng->txt('delete'), $ilCtrl->getLinkTarget($this->getParentObject(), 'confirmDeleteUnit'));
            $ilCtrl->setParameter($this->getParentObject(), 'unit_id', '');
            $dropdown = $this->ui_factory->dropdown()->standard($actions)->withLabel($this->lng->txt('actions'));
            $a_set['actions'] = $this->ui_renderer->render($dropdown);
        }
        if ($a_set['unit_id'] == $a_set['baseunit_id']) {
            $a_set['baseunit'] = '';
        }
        parent::fillRow($a_set);
    }
}
