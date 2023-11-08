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
 * Class ilBiblAdminFieldTableGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblAdminFieldTableGUI extends ilTable2GUI
{
    use \ILIAS\components\OrgUnit\ARHelper\DIC;

    public const TBL_ID = 'tbl_bibl_fields';
    protected \ilBiblAdminFactoryFacadeInterface $facade;
    protected int $position_index = 1;
    protected array $filter = [];

    /**
     * ilBiblAdminFieldTableGUI constructor.
     * @param object                             $a_parent_obj
     */
    public function __construct(?object $a_parent_obj, ilBiblAdminFactoryFacadeInterface $facade)
    {
        $this->facade = $facade;
        $this->parent_obj = $a_parent_obj;

        $this->setId(self::TBL_ID);
        $this->setPrefix(self::TBL_ID);
        $this->setFormName(self::TBL_ID);
        $this->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

        parent::__construct($a_parent_obj);
        $this->parent_obj = $a_parent_obj;
        $this->setRowTemplate('tpl.bibl_administration_fields_list_row.html', 'components/ILIAS/Bibliographic');

        $this->setFormAction($this->ctrl()->getFormAction($this->parent_obj));

        $this->setExternalSorting(true);

        $this->setDefaultOrderField("identifier");
        $this->setDefaultOrderDirection("asc");
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);

        $this->initColumns();

        if ($this->parent_obj->checkPermissionBoolAndReturn('write')) {
            $this->addCommandButton(ilBiblAdminFieldGUI::CMD_SAVE, $this->lng()->txt("save"));
        }

        $this->addFilterItems();
        $this->parseData();
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->lng()->txt('order'));
        $this->addColumn($this->lng()->txt('identifier'));
        $this->addColumn($this->lng()->txt('translation'));
        $this->addColumn($this->lng()->txt('standard'));
        $this->addColumn($this->lng()->txt('actions'), '', '150px');
    }

    protected function addFilterItems(): void
    {
        $field = new ilTextInputGUI($this->lng()->txt('identifier'), 'identifier');
        $this->addAndReadFilterItem($field);
    }

    protected function addAndReadFilterItem(ilTableFilterItem $field): void
    {
        $this->addFilterItem($field);
        $field->readFromSession();
        $this->filter[$field->getPostVar()] = $field instanceof ilCheckboxInputGUI ? $field->getChecked() : $field->getValue();
    }

    /**
     * Fills table rows with content from $a_set.
     */
    public function fillRow(array $a_set): void
    {
        $field = $this->facade->fieldFactory()->findById($a_set['id']);

        $this->tpl->setVariable('FIELD_ID', $field->getId());
        $this->tpl->setCurrentBlock("POSITION");
        $this->tpl->setVariable('POSITION_VALUE', $this->position_index);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("IDENTIFIER");
        $this->tpl->setVariable('IDENTIFIER_VALUE', $field->getIdentifier());
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("TRANSLATION");
        $this->tpl->setVariable('VAL_TRANSLATION', $this->facade->translationFactory()->translate($field));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("STANDARD");
        if ($field->isStandardField()) {
            $this->tpl->setVariable('IS_STANDARD_VALUE', $this->lng()->txt('standard'));
        } else {
            $this->tpl->setVariable('IS_STANDARD_VALUE', $this->lng()->txt('custom'));
        }

        $this->tpl->parseCurrentBlock();
        if ($this->parent_obj->checkPermissionBoolAndReturn('write')) {
            $this->addActionMenu($field);
        }

        $this->position_index++;
    }

    protected function addActionMenu(ilBiblFieldInterface $field): void
    {
        $this->ctrl()->setParameter(
            $this->parent_obj,
            ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER,
            $field->getId()
        );
        $this->ctrl()->setParameterByClass(
            ilBiblTranslationGUI::class,
            ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER,
            $field->getId()
        );

        // build translate action entry
        $action_entries['translate'] = $this->ui()->factory()->button()->shy(
            $this->lng()->txt('translate'),
            $this->ctrl()->getLinkTargetByClass(ilBiblTranslationGUI::class, ilBiblTranslationGUI::CMD_DEFAULT)
        );
        // build actions dropdown
        $actions = $this->ui()->factory()->dropdown()->standard($action_entries)->withLabel($this->lng->txt("actions"));
        $rendered_actions = $this->ui()->renderer()->render($actions);

        $this->tpl->setVariable('VAL_ACTIONS', $rendered_actions);
    }

    protected function parseData(): void
    {
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $q = new ilBiblTableQueryInfo();

        foreach ($this->filter as $filter_key => $filter_value) {
            switch ($filter_key) {
                case 'identifier':
                    $filter = new ilBiblTableQueryFilter();
                    $filter->setFieldName($filter_key);
                    $filter->setFieldValue('%' . $filter_value . '%');
                    $filter->setOperator("LIKE");
                    $q->addFilter($filter);
                    break;
            }
        }
        $q->setSortingColumn('position');
        $q->setSortingDirection('ASC');

        $data = $this->facade->fieldFactory()
                             ->filterAllFieldsForTypeAsArray($this->facade->type(), $q);

        $this->setData($data);
    }
}
