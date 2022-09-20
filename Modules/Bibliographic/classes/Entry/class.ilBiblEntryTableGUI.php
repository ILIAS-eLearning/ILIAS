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
 * Class ilDataCollectionField
 *
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 *
 * @deprecated REFACTOR
 */
class ilBiblEntryTableGUI extends ilTable2GUI
{
    /**
     * @var \ilBiblFieldFilterInterface[]
     */
    protected array $filter_objects = array();
    protected array $applied_filter = array();
    protected \ilBiblFactoryFacade $facade;

    /**
     * ilBiblEntryTableGUI constructor.
     */
    public function __construct(ilObjBibliographicGUI $a_parent_obj, ilBiblFactoryFacade $facade)
    {
        $this->facade = $facade;
        $this->setId('tbl_bibl_overview_' . $facade->iliasRefId());
        $this->setPrefix('tbl_bibl_overview_' . $facade->iliasRefId());
        $this->setFormName('tbl_bibl_overview_' . $facade->iliasRefId());
        parent::__construct($a_parent_obj, ilObjBibliographicGUI::CMD_VIEW);
        $this->parent_obj = $a_parent_obj;

        //Number of records
        $this->setEnableNumInfo(true);
        $this->setShowRowsSelector(true);

        $this->setEnableHeader(false);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate('tpl.bibliographic_record_table_row.html', 'Modules/Bibliographic');
        // enable sorting by alphabet -- therefore an unvisible column 'content' is added to the table, and the array-key 'content' is also delivered in setData
        $this->addColumn($this->lng->txt('a'), 'content', 'auto');
        $this->initFilter();
        $this->setOrderField('content');
        $this->setExternalSorting(true);
        $this->initData();
        $this->setDefaultOrderField('content');
    }


    public function initFilter(): void
    {
        $available_field_ids_for_object = array_map(function (ilBiblField $field) {
            return $field->getId();
        }, $this->facade->fieldFactory()->getAvailableFieldsForObjId($this->facade->iliasObjId()));

        foreach ($this->facade->filterFactory()->getAllForObjectId($this->facade->iliasObjId()) as $item) {
            if (in_array($item->getFieldId(), $available_field_ids_for_object)) {
                $filter_presentation = new ilBiblFieldFilterPresentationGUI($item, $this->facade);
                $field = $filter_presentation->getFilterItem();
                $this->addAndReadFilterItem($field);
                $this->filter_objects[$field->getPostVar()] = $item;
            }
        }
    }


    /**
     * @param $field
     */
    protected function addAndReadFilterItem(ilTableFilterItem $field): void
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


    public function fillRow(array $a_set): void
    {
        $ilBiblOverviewGUI = $a_set['overview_gui'];
        $this->tpl->setVariable(
            'SINGLE_ENTRY',
            $ilBiblOverviewGUI->getHtml()
        );
        //Detail-Link
        $this->ctrl->setParameter($this->parent_obj, ilObjBibliographicGUI::P_ENTRY_ID, $a_set['entry_id']);
        $this->tpl->setVariable('DETAIL_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'showDetails'));
        // generate/render links to libraries
        $libraries = $this->facade->libraryFactory()->getAll();
        $arr_library_link = array();
        foreach ($libraries as $library) {
            if ($library->getShowInList()) {
                $presentation = new ilBiblLibraryPresentationGUI($library, $this->facade);
                $arr_library_link[] = $presentation->getButton($this->facade, $ilBiblOverviewGUI->getEntry());
            }
        }
        if ($arr_library_link !== []) {
            $this->tpl->setVariable('LIBRARY_LINK', implode('<br/>', $arr_library_link));
        }
    }


    protected function initData(): void
    {
        $query = new ilBiblTableQueryInfo();
        /**
         * @var $filter \ilBiblFieldFilterInterface
         */
        foreach ($this->applied_filter as $field_name => $field_value) {
            if (!$field_value || (is_array($field_value) && count($field_value) == 0)) {
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

        $entries = [];
        $object_id = $this->facade->iliasObjId();
        foreach (
            $this->facade->entryFactory()
                ->filterEntryIdsForTableAsArray($object_id, $query) as $entry
        ) {
            /** @var $bibl_entry ilBiblEntry */
            $bibl_entry = $this->facade->entryFactory()->findByIdAndTypeString($entry['entry_id'], $entry['entry_type']);
            $overview_gui = new ilBiblEntryTablePresentationGUI($bibl_entry, $this->facade);
            $entry['content'] = strip_tags($overview_gui->getHtml());
            $entry['overview_gui'] = $overview_gui;
            $entries[] = $entry;
        }

        usort($entries, function ($a, $b) {
            return strcmp($a['content'], $b['content']);
        });

        $this->setData($entries);
    }
}
