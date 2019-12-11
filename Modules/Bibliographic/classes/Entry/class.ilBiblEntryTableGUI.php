<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDataCollectionField
 *
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 *
 * @deprecated REFACTOR
 */
class ilBiblEntryTableGUI extends ilTable2GUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    /**
     * @var \ilBiblFieldFilterInterface[]
     */
    protected $filter_objects = array();
    /**
     * @var array
     */
    protected $applied_filter = array();
    /**
     * @var \ilBiblFactoryFacade
     */
    protected $facade;
    /**
     * @var \ilObjBibliographicGUI
     */
    protected $parent_obj;


    /**
     * ilBiblEntryTableGUI constructor.
     *
     * @param \ilObjBibliographicGUI $a_parent_obj
     * @param \ilBiblFactoryFacade   $facade
     */
    public function __construct(ilObjBibliographicGUI $a_parent_obj, ilBiblFactoryFacade $facade)
    {
        $this->facade = $facade;
        $this->setId('tbl_bibl_overview');
        $this->setPrefix('tbl_bibl_overview');
        $this->setFormName('tbl_bibl_overview');
        parent::__construct($a_parent_obj);
        $this->parent_obj = $a_parent_obj;

        //Number of records
        $this->setEnableNumInfo(true);
        $this->setShowRowsSelector(true);

        $this->setEnableHeader(false);
        $this->setFormAction($this->ctrl()->getFormAction($a_parent_obj));
        $this->setRowTemplate('tpl.bibliographic_record_table_row.html', 'Modules/Bibliographic');
        // enable sorting by alphabet -- therefore an unvisible column 'content' is added to the table, and the array-key 'content' is also delivered in setData
        $this->addColumn($this->lng()->txt('a'), 'content', 'auto');
        $this->initFilter();
        $this->initData();
        $this->setOrderField('content');
        $this->setDefaultOrderField('content');
    }


    public function initFilter()
    {
        $available_fields_for_object = $this->facade->fieldFactory()->getAvailableFieldsForObjId($this->facade->iliasObjId());

        foreach ($available_fields_for_object as $available_field) {
            $filter = $this->facade->filterFactory()->findByFieldId($available_field->getId());
            if (!empty($filter)) {
                $filter_presentation = new ilBiblFieldFilterPresentationGUI($filter, $this->facade);
                $field = $filter_presentation->getFilterItem();
                $this->addAndReadFilterItem($field);
                $this->filter_objects[$field->getPostVar()] = $filter;
            }
        }
    }


    /**
     * @param $field
     */
    protected function addAndReadFilterItem(ilTableFilterItem $field)
    {
        $this->addFilterItem($field);
        $field->readFromSession();
        if ($field instanceof ilCheckboxInputGUI) {
            $this->applied_filter[$field->getPostVar()] = $field->getChecked();
            ;
        } else {
            $this->applied_filter[$field->getPostVar()] = $field->getValue();
        }
    }


    /**
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        $ilBiblEntry = $this->facade->entryFactory()->findByIdAndTypeString($a_set['entry_id'], $a_set['entry_type']);
        //TODO instanciate presentation gui class
        $ilBiblOverviewGUI = new ilBiblEntryTablePresentationGUI($ilBiblEntry, $this->facade);
        $this->tpl->setVariable('SINGLE_ENTRY', ilBiblEntryDetailPresentationGUI::prepareLatex($ilBiblOverviewGUI->getHtml()));
        //Detail-Link
        $this->ctrl->setParameter($this->parent_obj, ilObjBibliographicGUI::P_ENTRY_ID, $a_set['entry_id']);
        $this->tpl->setVariable('DETAIL_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'showDetails'));
        // generate/render links to libraries
        $libraries = $this->facade->libraryFactory()->getAll();
        $arr_library_link = array();
        foreach ($libraries as $library) {
            if ($library->getShowInList()) {
                $presentation = new ilBiblLibraryPresentationGUI($library, $this->facade);
                $arr_library_link[] = $presentation->getButton($this->facade, $ilBiblEntry);
            }
        }
        if (count($arr_library_link)) {
            $this->tpl->setVariable('LIBRARY_LINK', implode('<br/>', $arr_library_link));
        }
    }


    protected function initData()
    {
        global $DIC;
        $query = new ilBiblTableQueryInfo();
        /**
         * @var $filter \ilBiblFieldFilterInterface
         */
        foreach ($this->applied_filter as $field_name => $field_value) {
            if (!$field_value || count($field_value) == 0) {
                continue;
            }
            $filter = $this->filter_objects[$field_name];
            $filter_info = new ilBiblTableQueryFilter();
            $filter_info->setFieldName($field_name);
            switch ($filter->getFilterType()) {
                case ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT:
                    $filter_info->setFieldValue($field_value);
                    $filter_info->setOperator("IN");
                    break;
                case ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT:
                    $filter_info->setFieldValue($field_value);
                    $filter_info->setOperator("=");
                    break;
                case ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT:
                    $filter_info->setFieldValue("%{$field_value}%");
                    $filter_info->setOperator("LIKE");
                    break;
            }

            $query->addFilter($filter_info);
        }

        $entries = array();
        $object_id = $this->facade->iliasObjId();
        foreach ($this->facade->entryFactory()
                              ->filterEntryIdsForTableAsArray($object_id, $query) as $entry) {
            $ilBibliographicEntry = $this->facade->entryFactory()->findByIdAndTypeString($entry['entry_id'], $entry['entry_type']);
            $entry['content'] = strip_tags($ilBibliographicEntry->getOverview());
            $entries[] = $entry;
        }

        $this->setData($entries);
    }
}
