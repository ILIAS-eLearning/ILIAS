<?php

use ILIAS\UI\Factory as UIFactory;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Implementation\Component\Table\PresentationRow;
use ILIAS\UI\Component\Input\Container\Filter\Standard AS StandardFilter;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Table\Presentation AS PresentationTable;

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


class ilBiblEntryTableGUI
{
    public const P_PAGE = 'page';
    public const P_SORTATION = 'sortation';
    public const SORTATION_BY_TITLE_ASC = 1;
    public const SORTATION_BY_TITLE_DESC = 2;
    public const SORTATION_BY_AUTHOR_ASC = 3;
    public const SORTATION_BY_AUTHOR_DESC = 4;
    public const SORTATION_BY_YEAR_ASC = 5;
    public const SORTATION_BY_YEAR_DESC = 6;


    private HttpServices $http;
    private ilLanguage $lng;
    private UIFactory $ui_factory;
    private Renderer $ui_renderer;
    private ilCtrlInterface $ctrl;
    private Factory $refinery;
    private int $current_page = 0;
    private int $entries_per_page = 10;
    private ilUIService $ui_service;
    private ?StandardFilter $filter;
    private PresentationTable $table;

    /**  @var ilBiblFieldFilterInterface[] */
    protected array $filter_objects = array();

    public function __construct(protected ilObjBibliographicGUI $a_parent_obj, protected ilBiblFactoryFacade $facade, protected UIServices $ui)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_service = $DIC->uiService();
        $this->refinery = $DIC->refinery();
        $this->ctrl->saveParameterByClass(ilObjBibliographicGUI::class, self::P_PAGE);
        $this->ctrl->saveParameterByClass(ilObjBibliographicGUI::class, self::P_SORTATION);

        $this->filter = $this->buildFilter();
        $this->table = $this->buildTable();
    }

    public function getRenderedTableAndExistingFilters(): string
    {
        if ($this->filter !== null) {
            $components[] = $this->filter;
        }
        $components[] = $this->table;
        return $this->ui_renderer->render($components);
    }

    protected function buildFilter(): ?StandardFilter
    {
        $filter_objects = $this->facade->filterFactory()->getAllForObjectId($this->facade->iliasObjId());
        if (empty($filter_objects)) {
            return null;
        }

        $available_field_ids_for_object = array_map(static function (ilBiblField $field) {
            return $field->getId();
        }, $this->facade->fieldFactory()->getAvailableFieldsForObjId($this->facade->iliasObjId()));

        $filter_inputs = [];
        $filter_active_states = [];
        foreach ($filter_objects as $filter_object) {
            if (in_array($filter_object->getFieldId(), $available_field_ids_for_object, true)) {
                $filter_presentation = new ilBiblFieldFilterPresentationGUI($filter_object, $this->facade);
                $field = $this->facade->fieldFactory()->findById($filter_object->getFieldId());
                $post_var = $field->getIdentifier();
                $filter_input = $filter_presentation->getFilterInput();
                $filter_inputs[$post_var] = $filter_input;
                $filter_active_states[] = true;
                $this->filter_objects[$post_var] = $filter_object;
            }
        }

        return $this->ui_service->filter()->standard(
            'bibl_entry_filter',
            $this->ctrl->getLinkTargetByClass(
                ilObjBibliographicGUI::class,
                ilObjBibliographicGUI::CMD_SHOW_CONTENT,
                "",
                true
            ),
            $filter_inputs,
            $filter_active_states,
            true,
            true
        );
    }

    protected function buildTable(): PresentationTable
    {
        $records = $this->getData();
        $sorted_records = $this->getSortedRecords($records);
        $this->current_page = $this->determinePage();
        $records_current_page = $this->getRecordsOfCurrentPage($sorted_records);
        $view_controls = [];
        $sortations = [];
        foreach (array_keys($this->getSortationsMapping()) as $sort_id) {
            $sortations[$sort_id] = $this->lng->txt('sorting_' . $sort_id);
        }
        $view_controls[] = $this->ui_factory->viewControl()->sortation($sortations)
            ->withTargetURL(
                $this->ctrl->getLinkTargetByClass(ilObjBibliographicGUI::class, ilObjBibliographicGUI::CMD_SHOW_CONTENT),
                self::P_SORTATION
            )->withLabel($this->lng->txt('sorting_' . $this->determineSortation()));
        $view_controls[] = $this->ui_factory->viewControl()->pagination()
          ->withTargetURL($this->http->request()->getRequestTarget(), self::P_PAGE)
          ->withTotalEntries(count($records))
          ->withPageSize($this->entries_per_page)
            ->withCurrentPage($this->current_page);
        return $this->ui_factory->table()->presentation(
            "",
            $view_controls,
            function (
                PresentationRow $row,
                array $record,
                UIFactory $ui_factory
            ): PresentationRow {
                // Create row with fields and actions
                $author = $record['author'] ?? '';
                $title = $record['title'] ?? '';
                $year = $record['year'] ?? '';
                unset($record['author'], $record['title']);
                $translated_record = $this->getRecordWithTranslatedKeys($record);

                return $row
                    ->withHeadline($title)
                    ->withSubheadline($author)
                    ->withImportantFields([$year])
                    ->withContent( $ui_factory->listing()->descriptive($translated_record));
            }
        )->withData($records_current_page);
    }


    protected function getData(): array
    {
        $query = new ilBiblTableQueryInfo();

        $filter_data = ($this->filter !== null) ? ($this->ui_service->filter()->getData($this->filter) ?? []) : [];
        foreach ($filter_data as $field_name => $field_value) {
            if (empty($field_value) || (is_array($field_value) && count($field_value) === 0)) {
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
                    $filter_info->setFieldValue("%$field_value%");
                    $filter_info->setOperator("LIKE");
                    break;
            }

            $query->addFilter($filter_info);
        }

        $bibl_data = [];
        $object_id = $this->facade->iliasObjId();
        $entries = $this->facade->entryFactory()->filterEntryIdsForTableAsArray($object_id, $query);

        foreach ($entries as $entry) {
            /** @var $bibl_entry ilBiblEntry */
            $bibl_entry = $this->facade->entryFactory()->findByIdAndTypeString($entry['entry_id'], $entry['entry_type']);
            $entry_attributes = $this->facade->attributeFactory()->getAttributesForEntry($bibl_entry);
            $sorted_attributes = $this->facade->attributeFactory()->sortAttributes($entry_attributes);
            $entry_data = [];
            foreach ($sorted_attributes as $sorted_attribute) {
                $entry_data[$sorted_attribute->getName()] = $sorted_attribute->getValue();
            }
            if(!array_key_exists('author', $entry_data)) {
                $entry_data['author'] = '';
            }
            if(!array_key_exists('title', $entry_data)) {
                $entry_data['title'] = '';
            }
            if(!array_key_exists('year', $entry_data)) {
                $entry_data['year'] = '';
            }
            $bibl_data[] = $entry_data;
        }

        return $bibl_data;
    }

    protected function getSortedRecords(array $records): array
    {
        $sortation = $this->determineSortation();
        $sortation_mapping = $this->getSortationsMapping();
        $sortation_string = $sortation_mapping[$sortation];
        $sortation_parts = explode(' ', $sortation_string);
        $sortation_field = array_column($records, $sortation_parts[0]);
        $sortation_direction = ($sortation_parts[1] === 'ASC') ? SORT_ASC : SORT_DESC;
        array_multisort($sortation_field, $sortation_direction, $records);
        return $records;
    }


    protected function getRecordsOfCurrentPage(array $records): array
    {
        $offset = array_search($this->current_page * $this->entries_per_page, array_keys($records), true);
        $length = $this->entries_per_page;
        return array_slice($records, $offset, $length);
    }


    protected function getRecordWithTranslatedKeys(array $record): array
    {
        $translated_record = [];
        foreach ($record as $key => $value) {
            /** @var ilBiblField $field */
            $field = ilBiblField::where(['identifier' => $key])->first();
            $translated_key = $this->facade->translationFactory()->translate($field);
            $translated_record[$translated_key] = $value;
        }
        return $translated_record;
    }


    private function determinePage(): int
    {
        return $this->http->wrapper()->query()->has(self::P_PAGE)
            ? $this->http->wrapper()->query()->retrieve(self::P_PAGE, $this->refinery->kindlyTo()->int())
            : 0;
    }

    private function determineSortation(): int
    {
        return $this->http->wrapper()->query()->has(self::P_SORTATION)
            ? $this->http->wrapper()->query()->retrieve(self::P_SORTATION, $this->refinery->kindlyTo()->int())
            : array_keys($this->getSortationsMapping())[0] ?? self::SORTATION_BY_TITLE_ASC;
    }

    public function getSortationsMapping(): array
    {
        return [
            self::SORTATION_BY_TITLE_ASC => 'title ASC',
            self::SORTATION_BY_TITLE_DESC => 'title DESC',
            self::SORTATION_BY_AUTHOR_ASC => 'author ASC',
            self::SORTATION_BY_AUTHOR_DESC => 'author DESC',
            self::SORTATION_BY_YEAR_ASC => 'year ASC',
            self::SORTATION_BY_YEAR_DESC => 'year DESC'
        ];
    }
}
