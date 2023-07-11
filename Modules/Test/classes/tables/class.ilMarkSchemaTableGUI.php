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
 * Class ilMarkSchemaGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
class ilMarkSchemaTableGUI extends ilTable2GUI
{
    private ?ilMarkSchemaAware $object;

    protected bool $is_editable = true;

    public function __construct($parent, $cmd, $template_context = '', ilMarkSchemaAware $object = null)
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->object = $object;
        $this->ctrl = $ilCtrl;
        $this->is_editable = $this->object->canEditMarks();

        $this->setId('mark_schema_gui_' . $this->object->getMarkSchemaForeignId());
        parent::__construct($parent, $cmd);

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $cmd));
        $this->setFormName('form_' . $this->getId());

        $this->setRowTemplate('tpl.il_as_tst_mark_schema_row.html', 'Modules/Test');

        $this->setNoEntriesText($this->lng->txt('tst_no_marks_defined'));

        if ($this->object->canEditMarks()) {
            $this->addCommandButton('saveMarks', $this->lng->txt('save'));
            $this->addMultiCommand('deleteMarkSteps', $this->lng->txt('delete'));

            $this->setSelectAllCheckbox('marks[]');
        } else {
            $this->disable('select_all');
        }

        $this->setLimit(PHP_INT_MAX);

        $this->initColumns();
        $this->initData();

        $this->initJS($DIC->ui()->mainTemplate());
    }

    protected function initColumns(): void
    {
        $this->addColumn('', '', '1', true);
        $this->addColumn($this->lng->txt('tst_mark_short_form'), '');
        $this->addColumn($this->lng->txt('tst_mark_official_form'), '');
        $this->addColumn($this->lng->txt('tst_mark_minimum_level'), '');
        $this->addColumn($this->lng->txt('tst_mark_passed'), '', '1');
    }

    protected function initData(): void
    {
        $this->object->getMarkSchema()->sort();

        $data = [];

        $marks = $this->object->getMarkSchema()->getMarkSteps();
        foreach ($marks as $key => $value) {
            $data[] = [
                'mark_id' => $key,
                'mark_short' => $value->getShortName(),
                'mark_official' => $value->getOfficialName(),
                'mark_percentage' => $value->getMinimumLevel(),
                'mark_passed' => $value->getPassed()
            ];
        }

        $this->setData($data);
    }

    private function initJS(ilGlobalTemplateInterface $tpl)
    {
        $tpl->addOnloadCode("
            let form = document.querySelector('form[name=\"{$this->getFormName()}\"]');
            let button = form.querySelector('input[name=\"cmd[saveMarks]\"]');
            if (form && button) {
                form.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.requestSubmit(button);
                    }
                })
            }
        ");
    }

    /**
     * @param array $row
     */
    public function fillRow(array $a_set): void
    {
        $short_name = new ilTextInputGUI('', 'mark_short_' . $a_set['mark_id']);
        $short_name->setValue($a_set['mark_short']);
        $short_name->setDisabled(!$this->is_editable);
        $short_name->setMaxLength(15);
        $short_name->setSize(10);

        $official_name = new ilTextInputGUI('', 'mark_official_' . $a_set['mark_id']);
        $official_name->setSize(20);
        $official_name->setDisabled(!$this->object->canEditMarks());
        $official_name->setMaxLength(50);
        $official_name->setValue($a_set['mark_official']);

        $percentage = new ilNumberInputGUI('', 'mark_percentage_' . $a_set['mark_id']);
        $percentage->allowDecimals(true);
        $percentage->setValue($a_set['mark_percentage']);
        $percentage->setSize(10);
        $percentage->setDisabled(!$this->is_editable);
        $percentage->setMinValue(0);
        $percentage->setMaxValue(100);

        $this->tpl->setVariable('VAL_MARK_ID', $a_set['mark_id']);
        $this->tpl->setVariable(
            'VAL_CHECKBOX',
            ilLegacyFormElementsUtil::formCheckbox(false, 'marks[]', $a_set['mark_id'], !$this->is_editable)
        );
        $this->tpl->setVariable('VAL_SHORT_NAME', $short_name->render());
        $this->tpl->setVariable('VAL_OFFICIAL_NAME', $official_name->render());
        $this->tpl->setVariable('VAL_PERCENTAGE', $percentage->render());
        $this->tpl->setVariable(
            'VAL_PASSED_CHECKBOX',
            ilLegacyFormElementsUtil::formCheckbox(
                (bool) $a_set['mark_passed'],
                'passed_' . $a_set['mark_id'],
                '1',
                !$this->is_editable
            )
        );
    }
}
