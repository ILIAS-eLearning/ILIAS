<?php declare(strict_types=1);

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\GlobalHttpState;

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordGUI
{
    public const MODE_UNDEFINED = 0;
    public const MODE_EDITOR = 1;
    public const MODE_SEARCH = 2;
    public const MODE_INFO = 3;
    public const MODE_APP_PRESENTATION = 8;

    // glossary
    public const MODE_REC_SELECTION = 4;        // record selection (per object)
    public const MODE_FILTER = 5;                // filter (as used e.g. in tables)
    public const MODE_TABLE_HEAD = 6;                // table header (columns)
    public const MODE_TABLE_CELLS = 7;            // table cells

    private int $mode = self::MODE_UNDEFINED;
    private string $obj_type = '';
    private string $sub_type = '';
    private int $sub_id = 0;
    private int $obj_id = 0;
    private ?int $ref_id = null;

    // mode specific parameters
    private ?ilTable2GUI $table_gui = null;
    private ?array $row_data = null;
    private ?array $adt_search = null;
    protected ?ilPropertyFormGUI $form = null;
    protected array $search_values = [];
    protected ?array $search_form = null;
    protected ?array $search_form_values = null;

    protected array $editor_form = [];

    protected ?ilInfoScreenGUI $info = null;

    // $adv_id - $adv_type - $adv_subtype:
    // Object, that defines the adv md records being used. Default is $this->object, but the
    // context may set another object (e.g. media pool for media objects)

    // $adv_id must be a ref id, if $in_repository is true,
    // otherwise an object id
    protected ?int $adv_id = null;
    protected ?string $adv_type = null;
    protected ?string $adv_subtype = null;

    // This is false e.g. for portfolios
    protected bool $in_repository = true;

    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected GlobalHttpState $http;
    protected RefineryFactory $refinery;


    /**
     * @var ?int[] id filter for adv records
     */
    protected ?array $record_filter = null;


    /**
     * Constructor
     * @param int $a_mode mode either MODE_EDITOR or MODE_SEARCH
     */
    public function __construct(
        int $a_mode,
        string $a_obj_type = '',
        int $a_obj_id = 0,
        string $a_sub_type = '',
        int $a_sub_id = 0,
        bool $in_repository = true
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->mode = $a_mode;
        $this->obj_type = $a_obj_type;
        $this->obj_id = $a_obj_id;
        $this->sub_type = $a_sub_type;
        $this->sub_id = $a_sub_id;

        if ($a_obj_id && $this->in_repository) {
            $refs = ilObject::_getAllReferences($a_obj_id);
            $this->ref_id = end($refs);
        }
        $this->in_repository = $in_repository;
        $this->refinery = $DIC->refinery();
        $this->http = $DIC->http();
    }

    /**
     * Set object, that defines the adv md records being used. Default is $this->object, but the
     * context may set another object (e.g. media pool for media objects)
     * @param int $a_adv_id ref id, if $in_repository is true, otherwise object id
     */
    public function setAdvMdRecordObject(int $a_adv_id, string $a_adv_type, string $a_adv_subtype = "-") : void
    {
        $this->adv_id = $a_adv_id;
        $this->adv_type = $a_adv_type;
        $this->adv_subtype = $a_adv_subtype;
    }

    /**
     * Get adv md record parameters
     * @return array adv type
     */
    public function getAdvMdRecordObject() : array
    {
        if ($this->adv_type == null) {
            if ($this->in_repository) {
                return [$this->ref_id, $this->obj_type, $this->sub_type];
            } else {
                return [$this->obj_id, $this->obj_type, $this->sub_type];
            }
        }
        return [$this->adv_id, $this->adv_type, $this->adv_subtype];
    }

    /**
     * Set ref_id for context. In case of object creations this is the reference id
     * of the parent container.
     */
    public function setRefId(int $a_ref_id) : void
    {
        $this->ref_id = $a_ref_id;
    }

    public function setPropertyForm(ilPropertyFormGUI $form) : void
    {
        $this->form = $form;
    }

    /**
     * Set values for search form
     */
    public function setSearchValues(array $a_values) : void
    {
        $this->search_values = $a_values;
    }

    /**
     * get info sections
     * @todo use another required parameter injection for modes
     */
    public function setInfoObject(ilInfoScreenGUI $info) : void
    {
        $this->info = $info;
    }

    /**
     * Set advanced record filter
     * @param ?int[] $filter
     */
    public function setRecordFilter(?array $filter = null) : void
    {
        $this->record_filter = $filter;
    }

    /**
     * Check filter
     */
    protected function checkFilter($record_id) : bool
    {
        return !(is_array($this->record_filter) && !in_array($record_id, $this->record_filter));
    }

    /**
     * Get HTML
     * @throws InvalidArgumentException
     * @noinspection PhpVoidFunctionResultUsedInspection
     * @todo         return type depends on mode
     *       MODE_APP_PRESENTATION => array
     *       MODE_TABLE_CELLS => string
     *       all other void
     *  refactor this type parsing.
     */
    public function parse()
    {
        switch ($this->mode) {
            case self::MODE_EDITOR:
                return $this->parseEditor();

            case self::MODE_SEARCH:
                return $this->parseSearch();

            case self::MODE_INFO:
                return $this->parseInfoPage();

            case self::MODE_APP_PRESENTATION:
                return $this->parseAppointmentPresentationa();

            case self::MODE_REC_SELECTION:
                return $this->parseRecordSelection();

            case self::MODE_FILTER:
                return $this->parseFilter();

            case self::MODE_TABLE_HEAD:
                return $this->parseTableHead();

            case self::MODE_TABLE_CELLS:
                return $this->parseTableCells();

            default:
                throw new InvalidArgumentException('Missing or wrong ADV mode given: ' . $this->mode);
        }
    }

    /**
     * Parse property form in editor mode
     */
    protected function parseEditor() : void
    {
        $this->editor_form = array();
        foreach ($this->getActiveRecords() as $record_obj) {
            $record_id = $record_obj->getRecordId();

            $values = new ilAdvancedMDValues($record_id, $this->obj_id, $this->sub_type, $this->sub_id);
            $values->read();
            $defs = $values->getDefinitions();

            // empty record?
            if (!sizeof($defs)) {
                continue;
            }

            if (!$this->checkFilter($record_id)) {
                continue;
            }

            $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_obj->getRecordId());
            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_obj->getRecordId());

            $adt_group_form = ilADTFactory::getInstance()->getFormBridgeForInstance($values->getADTGroup());
            $adt_group_form->setForm($this->form);

            $adt_group_form->setTitle($translations->getTitleForLanguage($this->user->getLanguage()));
            $adt_group_form->setInfo($translations->getDescriptionForLanguage($this->user->getLanguage()));

            foreach ($defs as $def) {
                $element = $adt_group_form->getElement((string) $def->getFieldId());
                $element->setTitle($field_translations->getTitleForLanguage(
                    $def->getFieldId(),
                    $this->user->getLanguage()
                ));
                $element->setInfo($field_translations->getDescriptionForLanguage(
                    $def->getFieldId(),
                    $this->user->getLanguage()
                ));

                // definition may customize ADT form element
                $def->prepareElementForEditor($element);

                if ($values->isDisabled((string) $def->getFieldId())) {
                    $element->setDisabled(true);
                }
            }

            $adt_group_form->addToForm();

            $this->editor_form[$record_id] = array("values" => $values, "form" => $adt_group_form);
        }
    }

    /**
     * Load edit form values from post
     */
    public function importEditFormPostValues() : bool
    {
        $valid = true;
        foreach ($this->editor_form as $item) {
            $item["form"]->importFromPost();
            if (!$item["form"]->validate()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Write edit form values to db
     * @todo throw exception in case of missing parameters
     */
    public function writeEditForm(?int $a_obj_id = null, ?int $a_sub_id = null) : bool
    {
        if (!count($this->editor_form)) {
            return false;
        }

        // switch ids?
        if ($a_obj_id) {
            $this->obj_id = $a_obj_id;
        }
        if ($a_sub_id) {
            $this->sub_id = $a_sub_id;
        }

        foreach ($this->editor_form as $item) {
            if ($a_obj_id || $a_sub_id) {
                // switch active record to updated primary keys, e.g. after creation
                $item["values"]->setActiveRecordPrimary($this->obj_id, $this->sub_type, $this->sub_id);
            }

            $item["values"]->write();
        }
        return true;
    }

    /**
     * Parse search
     */
    private function parseSearch() : void
    {
        // this is NOT used for the global search, see ilLuceneAdvancedSearchFields::getFormElement()
        // (so searchable flag is NOT relevant)
        //
        // current usage: wiki page element "[amd] page list"

        $this->lng->loadLanguageModule('search');

        $this->search_form = array();
        foreach ($this->getActiveRecords() as $record) {
            $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record->getRecordId(), true);

            // empty record?
            if (!sizeof($fields)) {
                continue;
            }

            $record_translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record->getRecordId());
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($record_translations->getTitleForLanguage($this->user->getLanguage()));
            $section->setInfo($record_translations->getDescriptionForLanguage($this->user->getLanguage()));
            $this->form->addItem($section);

            foreach ($fields as $field) {
                $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record->getRecordId());

                $field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                    $field->getADTDefinition(),
                    true,
                    false
                );
                $field_form->setForm($this->form);
                $field_form->setElementId("advmd[" . $field->getFieldId() . "]");
                $field_form->setTitle($field_translations->getTitleForLanguage(
                    $field->getFieldId(),
                    $this->user->getLanguage()
                ));

                if (is_array($this->search_form_values) &&
                    isset($this->search_form_values[$field->getFieldId()])) {
                    $field->setSearchValueSerialized($field_form, $this->search_form_values[$field->getFieldId()]);
                }

                $field->prepareElementForSearch($field_form);

                $field_form->addToForm();

                $this->search_form[$field->getFieldId()] = array("def" => $field, "value" => $field_form);
            }
        }
    }

    /**
     * Load edit form values from post
     */
    public function importSearchForm() : ?array
    {
        if (!is_array($this->search_form)) {
            return null;
        }

        $valid = true;
        $res = array();
        foreach ($this->search_form as $field_id => $item) {
            $item["value"]->importFromPost();
            if (!$item["value"]->validate()) {
                $valid = false;
            }
            $value = $item["def"]->getSearchValueSerialized($item["value"]);
            if ($value !== null) {
                $res[$field_id] = $value;
            }
        }

        if ($valid) {
            return $res;
        }
        return null;
    }

    public function setSearchFormValues(array $a_values) : void
    {
        $this->search_form_values = $a_values;
    }

    /**
     * Presentation for info page
     * @return void
     */
    private function parseInfoPage() : void
    {
        foreach (ilAdvancedMDValues::getInstancesForObjectId(
            $this->obj_id,
            $this->obj_type,
            $this->sub_type,
            $this->sub_id
        ) as $record_id => $a_values) {
            // this correctly binds group and definitions
            $a_values->read();

            $record_translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);
            $this->info->addSection($record_translations->getTitleForLanguage($this->user->getLanguage()));

            $defs = $a_values->getDefinitions();
            foreach ($a_values->getADTGroup()->getElements() as $element_id => $element) {
                if (!$element->isNull()) {
                    $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);
                    $title = $field_translations->getTitleForLanguage($element_id, $this->user->getLanguage());

                    $this->info->addProperty(
                        $title,
                        ilADTFactory::getInstance()->getPresentationBridgeForInstance($element)->getHTML()
                    );
                }
            }
        }
    }

    /**
     * Presentation for calendar agenda list.
     */
    private function parseAppointmentPresentationa() : array
    {
        $sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->obj_type);

        $definitions = ilAdvancedMDFieldDefinition::getInstancesByObjType($this->obj_type);
        $definitions = $sub->sortDefinitions($definitions);

        $positions = array();
        foreach ($definitions as $position => $value) {
            $positions[$value->getFieldId()] = $position;
        }

        $array_elements = array();
        foreach (ilAdvancedMDValues::getInstancesForObjectId(
            $this->obj_id,
            $this->obj_type,
            $this->sub_type,
            $this->sub_id
        ) as $record_id => $a_values) {
            // this correctly binds group and definitions
            $a_values->read();

            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);

            $defs = $a_values->getDefinitions();
            foreach ($a_values->getADTGroup()->getElements() as $element_id => $element) {
                if (!$element->isNull()) {
                    $presentation_bridge = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);
                    #21615
                    if (get_class($element) == 'ilADTLocation') {
                        $presentation_bridge->setSize(100, 200);
                        #22638
                        $presentation_value = $presentation_bridge->getHTML();
                        $presentation_value .= "<script>ilInitMaps();</script>";
                    } else {
                        #22638
                        $presentation_value = strip_tags($presentation_bridge->getHTML());
                    }
                    $array_elements[$positions[$element_id]] =
                        [
                            "title" => $field_translations->getTitleForLanguage(
                                $element_id,
                                $this->user->getLanguage()
                            ),
                            "value" => $presentation_value
                        ];
                }
            }
        }

        // already sorted by record positions
        return $array_elements;
    }

    /**
     * handle ecs definitions
     */
    private function handleECSDefinitions($a_definition) : bool
    {
        if (ilECSServerSettings::getInstance()->activeServerExists() or
            ($this->obj_type != 'crs' and $this->obj_type != 'rcrs')
        ) {
            return false;
        }
        return false;
    }

    /**
     * Parse property form in editor mode
     * @todo the parameter is never filled.
     */
    public function parseRecordSelection(string $a_sec_head = "") : void
    {
        $first = true;
        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType(
            $this->obj_type,
            $this->sub_type
        ) as $record_obj) {
            $selected = ilAdvancedMDRecord::getObjRecSelection($this->obj_id, $this->sub_type);
            if ($first) {
                $first = false;
                $section = new ilFormSectionHeaderGUI();
                $sec_tit = ($a_sec_head == "")
                    ? $this->lng->txt("meta_adv_records")
                    : $a_sec_head;
                $section->setTitle($sec_tit);
                $this->form->addItem($section);
            }

            // checkbox for each active record
            $cb = new ilCheckboxInputGUI($record_obj->getTitle(), "amet_use_rec[]");
            $cb->setInfo($record_obj->getDescription());
            $cb->setValue((string) $record_obj->getRecordId());
            if (in_array((string) $record_obj->getRecordId(), $selected)) {
                $cb->setChecked(true);
            }
            $this->form->addItem($cb);
        }
    }

    /**
     * Save selection per object
     */
    public function saveSelection() : void
    {
        $post_amet_use_rec = [];
        if ($this->http->wrapper()->post()->has('amet_use_rec')) {
            $post_amet_use_rec = $this->http->wrapper()->post()->retrieve(
                'amet_use_rec',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        ilAdvancedMDRecord::saveObjRecSelection($this->obj_id, $this->sub_type, $post_amet_use_rec);
    }

    /**
     * Set table for self::MODE_TABLE_FILTER
     */
    public function setTableGUI(ilTable2GUI $a_val) : void
    {
        $this->table_gui = $a_val;
    }

    public function getTableGUI() : ?ilTable2GUI
    {
        return $this->table_gui;
    }

    /**
     * Set row data
     * @param array $a_val assoc array of row data (containing md record data)
     */
    public function setRowData(array $a_val) : void
    {
        $this->row_data = $a_val;
    }

    /**
     * Get row data
     * @return array assoc array of row data (containing md record data)
     */
    public function getRowData() : ?array
    {
        return $this->row_data;
    }

    /**
     * @return ilAdvancedMDRecord[]
     */
    protected function getActiveRecords() : array
    {
        list($adv_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();
        return ilAdvancedMDRecord::_getSelectedRecordsByObject($adv_type, $adv_id, $adv_subtype, $this->in_repository);
    }

    /**
     * Parse property for filter (table)
     */
    private function parseFilter() : void
    {
        $this->adt_search = array();

        foreach ($this->getActiveRecords() as $record_obj) {
            $record_id = $record_obj->getRecordId();

            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);

            $defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
            foreach ($defs as $def) {
                // some input GUIs do NOT support filter rendering yet
                if (!$def->isFilterSupported()) {
                    continue;
                }

                $this->adt_search[$def->getFieldId()] = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                    $def->getADTDefinition(),
                    true,
                    false
                );
                $this->adt_search[$def->getFieldId()]->setTableGUI($this->table_gui);
                $this->adt_search[$def->getFieldId()]->setTitle(
                    $field_translations->getTitleForLanguage($def->getFieldId(), $this->user->getLanguage())
                );
                $this->adt_search[$def->getFieldId()]->setElementId('md_' . $def->getFieldId());

                $this->adt_search[$def->getFieldId()]->loadFilter();
                $this->adt_search[$def->getFieldId()]->addToForm();
            }
        }
    }

    /**
     * Import filter (post) values
     */
    public function importFilter() : void
    {
        if (!is_array($this->adt_search)) {
            return;
        }

        foreach ($this->adt_search as $element) {
            $element->importFromPost();
        }
    }

    /**
     * Get SQL conditions for current filter value(s)
     */
    public function getFilterElements(bool $a_only_non_empty = true) : array
    {
        if (!is_array($this->adt_search)) {
            return [];
        }

        $res = [];
        foreach ($this->adt_search as $def_id => $element) {
            if (!$element->isNull() ||
                !$a_only_non_empty) {
                $res[$def_id] = $element;
            }
        }
        return $res;
    }

    /**
     * Parse property for table head
     */
    private function parseTableHead() : void
    {
        foreach ($this->getActiveRecords() as $record_obj) {
            $record_id = $record_obj->getRecordId();

            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);

            $defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
            foreach ($defs as $def) {
                if ($this->handleECSDefinitions($def)) {
                    continue;
                }

                $this->table_gui->addColumn(
                    $field_translations->getTitleForLanguage($def->getFieldId(), $this->user->getLanguage()),
                    'md_' . $def->getFieldId()
                );
            }
        }
    }

    /**
     * Parse table cells
     */
    private function parseTableCells() : string
    {
        $data = $this->getRowData();
        $html = "";

        foreach ($this->getActiveRecords() as $record_obj) {
            $record_id = $record_obj->getRecordId();

            $defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
            foreach ($defs as $def) {
                if ($this->handleECSDefinitions($def)) {
                    continue;
                }

                $html .= "<td class='std'>" . $data['md_' . $def->getFieldId()] . "</td>";
            }
        }
        return $html;
    }
}
