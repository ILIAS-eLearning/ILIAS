<?php

/**
 * Class ilBiblFieldFilterTableGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterTableGUI extends ilTable2GUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    const TBL_ID = 'tbl_bibl_filters';
    /**
     * @var \ilBiblFactoryFacade
     */
    protected $facade;
    /**
     * @var \ilBiblFieldFilterGUI
     */
    protected $parent_obj;
    /**
     * @var array
     */
    protected $filter = [];
    /**
     * @var \ILIAS\UI\Component\Modal\Interruptive[]
     */
    protected $interruptive_modals = [];


    /**
     * ilBiblFieldFilterTableGUI constructor.
     *
     * @param \ilBiblFieldFilterGUI $a_parent_obj
     * @param \ilBiblFactoryFacade  $facade
     */
    public function __construct(\ilBiblFieldFilterGUI $a_parent_obj, ilBiblFactoryFacade $facade)
    {
        $this->facade = $facade;
        $this->parent_obj = $a_parent_obj;

        $f = $this->dic()->ui()->factory();
        $this->modal = $f->modal()->roundtrip('---', $f->legacy(''))->withAsyncRenderUrl($this->ctrl()->getLinkTarget($this->parent_obj, ilBiblFieldFilterGUI::CMD_EDIT));

        $this->setId(self::TBL_ID);
        $this->setPrefix(self::TBL_ID);
        $this->setFormName(self::TBL_ID);
        $this->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

        parent::__construct($a_parent_obj);
        $this->parent_obj = $a_parent_obj;
        $this->setRowTemplate('tpl.bibl_settings_filters_list_row.html', 'Modules/Bibliographic');

        $this->setFormAction($this->ctrl()->getFormActionByClass(ilBiblFieldFilterGUI::class));

        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");
        $this->setEnableHeader(true);

        $this->initColumns();
        $this->addFilterItems();
        $this->parseData();
    }


    protected function initColumns()
    {
        $this->addColumn($this->lng()->txt('field'), 'field');
        $this->addColumn($this->lng()->txt('filter_type'), 'filter_type');
        $this->addColumn($this->lng()->txt('actions'), '', '150px');
    }


    protected function addFilterItems()
    {
        $field = new ilTextInputGUI($this->lng()->txt('field'), 'field');
        $this->addAndReadFilterItem($field);
    }


    /**
     * @param $field
     */
    protected function addAndReadFilterItem(ilFormPropertyGUI $field)
    {
        $this->addFilterItem($field);
        $field->readFromSession();
        if ($field instanceof ilCheckboxInputGUI) {
            $this->filter[$field->getPostVar()] = $field->getChecked();
        } else {
            $this->filter[$field->getPostVar()] = $field->getValue();
        }
    }


    /**
     * Fills table rows with content from $a_set.
     *
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        /**
         * @var ilBiblFieldFilter $filter
         * @var ilBiblField       $field
         */
        $filter = $this->facade->filterFactory()->findById((int) $a_set['id']);
        $field = $this->facade->fieldFactory()->findById($filter->getFieldId());

        $this->tpl->setVariable(
            'VAL_FIELD',
            $this->facade->translationFactory()->translate($field)
        );
        $this->tpl->setVariable(
            'VAL_FILTER_TYPE',
            $this->lng()->txt(
                "filter_type_" . $filter->getFilterType()
            )
        );

        $this->addActionMenu($filter);
    }


    /**
     * @param \ilBiblFieldFilter $ilBiblFieldFilter
     */
    protected function addActionMenu(ilBiblFieldFilter $ilBiblFieldFilter)
    {
        $this->ctrl()->setParameterByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::FILTER_ID, $ilBiblFieldFilter->getId());

        $f = $this->dic()->ui()->factory();
        $r = $this->dic()->ui()->renderer();

        $edit = $f->button()->shy($this->lng()->txt("edit"), $this->ctrl()->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_EDIT));

        $delete_modal = $f->modal()->interruptive(
            '',
            '',
            ''
        )->withAsyncRenderUrl($this->ctrl()->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_RENDER_INTERRUPTIVE, '', true));

        $delete = $f->button()->shy($this->lng()->txt("delete"), '')->withOnClick($delete_modal->getShowSignal());

        $this->tpl->setVariable('VAL_ACTIONS', $r->render([$f->dropdown()->standard([$edit, $delete])]));

        $this->interruptive_modals[] = $delete_modal;
    }


    protected function parseData()
    {
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $sorting_column = $this->getOrderField() ? $this->getOrderField() : 'id';

        $offset = $this->getOffset() ? $this->getOffset() : 0;

        $sorting_direction = $this->getOrderDirection();
        $num = $this->getLimit();

        $info = new ilBiblTableQueryInfo();
        $info->setSortingColumn($sorting_column);
        $info->setOffset($offset);
        $info->setSortingDirection($sorting_direction);
        $info->setLimit($num);

        $filter = $this->facade->filterFactory()->filterItemsForTable($this->facade->iliasObjId(), $info);
        $this->setData($filter);
    }


    /**
     * @return \ILIAS\UI\Component\Modal\Interruptive[]
     */
    protected function getInterruptiveModals() : array
    {
        return $this->interruptive_modals;
    }


    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        $table = parent::getHTML();
        $modals = $this->dic()->ui()->renderer()->render($this->getInterruptiveModals());

        return $table . $modals;
    }
}
