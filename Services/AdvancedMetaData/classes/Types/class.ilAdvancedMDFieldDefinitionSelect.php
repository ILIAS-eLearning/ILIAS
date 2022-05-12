<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type select
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelect extends ilAdvancedMDFieldDefinition
{
    public const REMOVE_ACTION_ID = "-iladvmdrm-";

    protected ?array $options = null;
    protected array $confirm_objects = [];
    protected array $confirm_objects_values = [];
    protected ?array $confirmed_objects = null;
    protected ?array $old_options = null;

    protected array $option_translations = [];
    private \ilGlobalTemplateInterface $main_tpl;
    public function __construct(?int $a_field_id = null, string $language = '')
    {
        parent::__construct($a_field_id, $language);
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function getType() : int
    {
        return self::TYPE_SELECT;
    }

    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search) : string
    {
        return $a_adt_search->getADT()->getSelection();
    }

    protected function initADTDefinition() : ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
        $def->setNumeric(false);

        $options = $this->getOptions();
        $translated_options = [];
        if (isset($this->getOptionTranslations()[$this->language])) {
            $translated_options = $this->getOptionTranslations()[$this->language];
        }
        $def->setOptions(array_replace($options, $translated_options));
        return $def;
    }

    public function setOptions(array $a_values = null) : void
    {
        if ($a_values !== null) {
            foreach ($a_values as $idx => $value) {
                $a_values[$idx] = trim($value);
                if (!$a_values[$idx]) {
                    unset($a_values[$idx]);
                }
            }
            $a_values = array_unique($a_values);
        }
        $this->options = $a_values;
    }

    public function getOptions() : ?array
    {
        return $this->options;
    }

    public function getOptionTranslations() : array
    {
        return $this->option_translations;
    }

    public function getOptionTranslation(string $language) : array
    {
        if (isset($this->getOptionTranslations()[$language])) {
            return $this->getOptionTranslations()[$language];
        }
        return [];
    }

    public function setOptionTranslations(array $translations) : void
    {
        $this->option_translations = $translations;
    }

    public function setOptionTranslationsForLanguage(array $translations, string $language) : void
    {
        $this->option_translations[$language] = $translations;
    }

    protected function importFieldDefinition(array $a_def) : void
    {
        // options (field_values from adv_mdf_field_definitions are not used)
        $this->setOptions([]);
    }

    protected function getFieldDefinition() : array
    {
        return [];
    }

    public function getFieldDefinitionForTableGUI(string $content_language) : array
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (strlen($content_language)) {
            $options = $this->getOptionTranslation($content_language);
        } else {
            $options = $this->getOptions();
        }
        return [
            $lng->txt("meta_advmd_select_options") => implode(",", $options)
        ];
    }

    protected function addCustomFieldToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled = false,
        string $language = ''
    ) : void {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->addCustomFieldToDefinitionFormInTranslationMode($a_form, $a_disabled, $language);
            return;
        }

        // if not the default language is chosen => no add/remove; no sorting
        $field = new ilTextInputGUI($this->lng->txt("meta_advmd_select_options"), "opts");
        $field->setRequired(true);
        $field->setMulti(true, true);
        $field->setMaxLength(255); // :TODO:
        $a_form->addItem($field);

        $options = $this->getOptions();
        if ($options) {
            $field->setMultiValues($options);
            $field->setValue(array_shift($options));
        }

        if ($a_disabled) {
            $field->setDisabled(true);
        }
    }

    protected function addCustomFieldToDefinitionFormInTranslationMode(
        ilPropertyFormGUI $form,
        bool $disabled,
        string $language = ''
    ) : void {
        $default_language = ilAdvancedMDRecord::_getInstanceByRecordId($this->record_id)->getDefaultLanguage();

        $translation = $this->getOptionTranslation($language);

        $first = true;
        foreach ($this->getOptions() as $index => $option) {
            $title = '';
            if ($first) {
                $title = $this->lng->txt("meta_advmd_select_options");
            }
            $text = new ilTextInputGUI(
                $title,
                'opts__' . $language . '__' . $index
            );
            if (isset($translation[$index])) {
                $text->setValue($translation[$index]);
            }
            $text->setInfo($default_language . ': ' . $option);
            $text->setMaxLength(255);
            $text->setRequired(true);

            $first = false;
            $form->addItem($text);
        }
    }

    /**
     * Process custom post values from definition form
     */
    protected function buildConfirmedObjects(ilPropertyFormGUI $a_form) : ?array
    {
        // #15719
        $recipes = $a_form->getInput("conf_det");
        if (is_array($recipes[$this->getFieldId()])) {
            $recipes = $recipes[$this->getFieldId()];
            $sum = $a_form->getInput("conf_det_act");
            $sum = $sum[$this->getFieldId()];
            $sgl = $a_form->getInput("conf");
            $sgl = $sgl[$this->getFieldId()];

            $res = array();
            foreach ($recipes as $old_option => $recipe) {
                $sum_act = $sum[$old_option];
                $sgl_act = $sgl[$old_option];

                if ($recipe == "sum") {
                    // #18885
                    if (!$sum_act) {
                        return null;
                    }

                    foreach (array_keys($sgl_act) as $obj_idx) {
                        if ($sum_act == self::REMOVE_ACTION_ID) {
                            $sum_act = "";
                        }
                        if (substr($sum_act, 0, 4) == 'idx_') {
                            $parts = explode('_', $sum_act);
                            $sum_act = $parts[1];
                        }
                        $res[$old_option][$obj_idx] = $sum_act;
                    }
                } else {
                    // #18885
                    foreach ($sgl_act as $sgl_index => $sgl_item) {
                        if (!$sgl_item) {
                            return null;
                        } elseif ($sgl_item == self::REMOVE_ACTION_ID) {
                            $sgl_act[$sgl_index] = "";
                        }
                        if (substr($sgl_item, 0, 4) == 'idx_') {
                            $parts = explode('_', $sgl_item);
                            $sgl_act[$sgl_index] = $parts[1];
                        }
                    }

                    $res[$old_option] = $sgl_act;
                }
            }
            return $res;
        }
        return null;
    }

    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '') : void
    {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->importTranslatedFormPostValues($a_form, $language);
            return;
        }

        if (!strlen($language)) {
            $language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();
        }

        $old = $this->getOptionTranslation($language);
        $new = $a_form->getInput("opts");

        $missing = array_diff_assoc($old, $new);

        if (sizeof($missing)) {
            $this->confirmed_objects = $this->buildConfirmedObjects($a_form);

            if (!is_array($this->confirmed_objects)) {
                $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                    $this->getADTDefinition(),
                    false,
                    true
                );
                foreach ($missing as $missing_idx => $missing_value) {
                    $in_use = $this->findBySingleValue($search, $missing_idx);
                    if (is_array($in_use)) {
                        foreach ($in_use as $item) {
                            $this->confirm_objects[$missing_idx][] = $item;
                            $this->confirm_objects_values[$missing_idx] = $old[$missing_idx];
                        }
                    }
                }
            }
        }
        $this->old_options = $old;
        $this->setOptionTranslationsForLanguage($new, $language);
    }

    /**
     * @param ilADTEnumSearchBridgeMulti $a_search
     * @param                            $a_value
     * @return array
     * @todo fix $a_value type
     */
    protected function findBySingleValue(ilADTSearchBridge $a_search, $a_value) : array
    {
        $res = array();
        $a_search->getADT()->setSelections((array) $a_value);
        $condition = $a_search->getSQLCondition('value_index');

        $in_use = ilADTActiveRecordByType::find(
            "adv_md_values",
            "Enum",
            $this->getFieldId(),
            $condition
        );
        if ($in_use) {
            foreach ($in_use as $item) {
                $res[] = array($item["obj_id"], $item["sub_type"], $item["sub_id"], $item["value_index"]);
            }
        }
        return $res;
    }

    protected function importTranslatedFormPostValues(ilPropertyFormGUI $form, string $language) : void
    {
        $translated_options = [];
        foreach ($this->getOptions() as $idx => $value) {
            $value = $form->getInput('opts__' . $language . '__' . $idx);
            $translated_options[] = trim($value);
        }
        $translations = $this->getOptionTranslations();
        $translations[$language] = $translated_options;
        $this->setOptionTranslations($translations);
    }

    public function importDefinitionFormPostValuesNeedsConfirmation() : bool
    {
        return is_array($this->confirm_objects) && count($this->confirm_objects) > 0;
    }
    public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form) : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $objDefinition = $DIC['objDefinition'];

        $post_conf_det = (array) ($this->http->request()->getParsedBody()['conf_det'] ?? []);
        $post_conf = (array) ($this->http->request()->getParsedBody()['conf'] ?? []);

        $a_form->getItemByPostVar("opts")->setDisabled(true);
        if (is_array($this->confirm_objects) && count($this->confirm_objects) > 0) {
            $new_options = $a_form->getInput("opts");

            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("md_adv_confirm_definition_select_section"));
            $a_form->addItem($sec);

            foreach ($this->confirm_objects as $old_option => $items) {
                $old_option_value = $this->confirm_objects_values[$old_option];
                $details = new ilRadioGroupInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option") . ': "' . $old_option_value . '"',
                    "conf_det[" . $this->getFieldId() . "][" . $old_option . "]"
                );
                $details->setRequired(true);
                $details->setValue("sum");
                $a_form->addItem($details);

                // automatic reload does not work
                if (isset($post_conf_det[$this->getFieldId()][$old_option])) {
                    $details->setValue($post_conf_det[$this->getFieldId()][$old_option]);
                }

                $sum = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_all"), "sum");
                $details->addOption($sum);

                $sel = new ilSelectInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option_all_action"),
                    "conf_det_act[" . $this->getFieldId() . "][" . $old_option . "]"
                );
                $sel->setRequired(true);
                $options = array(
                    "" => $lng->txt("please_select"),
                    self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                );
                foreach ($new_options as $new_option_index => $new_option) {
                    $options['idx_' . $new_option_index] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_option . '"';
                }
                $sel->setOptions($options);
                $sum->addSubItem($sel);

                // automatic reload does not work
                if (isset($post_conf_det[$this->getFieldId()][$old_option])) {
                    if ($post_conf_det[$this->getFieldId()][$old_option]) {
                        $sel->setValue($post_conf_det[$this->getFieldId()][$old_option]);
                    } elseif ($post_conf_det[$this->getFieldId()][$old_option] == "sum") {
                        $sel->setAlert($lng->txt("msg_input_is_required"));
                        $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                    }
                }
                $single = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_single"), "sgl");
                $details->addOption($single);
                foreach ($items as $item) {
                    $obj_id = $item[0];
                    $sub_type = $item[1];
                    $sub_id = $item[2];

                    $item_id = $obj_id . "_" . $sub_type . "_" . $sub_id;

                    $type = ilObject::_lookupType($obj_id);
                    $type_title = $lng->txt("obj_" . $type);
                    $title = ' "' . ilObject::_lookupTitle($obj_id) . '"';

                    if ($sub_id) {
                        $class = "ilObj" . $objDefinition->getClassName($type);
                        $class_path = $objDefinition->getLocation($type);
                        $ints = class_implements($class);
                        if (isset($ints["ilAdvancedMetaDataSubItems"])) {
                            /** @noinspection PhpUndefinedMethodInspection */
                            $sub_title = $class::getAdvMDSubItemTitle($obj_id, $sub_type, $sub_id);
                            if ($sub_title) {
                                $title .= ' (' . $sub_title . ')';
                            }
                        }
                    }

                    $sel = new ilSelectInputGUI(
                        $type_title . ' ' . $title,
                        "conf[" . $this->getFieldId() . "][" . $old_option . "][" . $item_id . "]"
                    );
                    $sel->setRequired(true);
                    $options = array(
                        "" => $lng->txt("please_select"),
                        self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                    );
                    foreach ($new_options as $new_option_index => $new_option) {
                        $options['idx_' . $new_option_index] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_option . '"';
                    }
                    $sel->setOptions($options);

                    // automatic reload does not work
                    if (isset($post_conf[$this->getFieldId()][$old_option][$item_id])) {
                        if ($post_conf[$this->getFieldId()][$old_option][$item_id]) {
                            $sel->setValue($post_conf[$this->getFieldId()][$old_option][$item_id]);
                        } elseif ($post_conf_det[$this->getFieldId()][$old_option] == "sgl") {
                            $sel->setAlert($lng->txt("msg_input_is_required"));
                            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                        }
                    }

                    $single->addSubItem($sel);
                }
            }
        }
    }

    public function delete() : void
    {
        $this->deleteOptionTranslations();
        parent::delete();
    }

    public function save(bool $a_keep_pos = false) : void
    {
        parent::save($a_keep_pos);
        $this->saveOptionTranslations();
    }

    protected function deleteOptionTranslations() : void
    {
        $query = 'delete from adv_mdf_enum ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    protected function updateOptionTranslations() : void
    {
        $this->deleteOptionTranslations();
        $this->saveOptionTranslations();
    }

    protected function saveOptionTranslations() : void
    {
        foreach ($this->getOptionTranslations() as $lang_key => $options) {
            foreach ($options as $idx => $option) {
                $query = 'insert into adv_mdf_enum (field_id, lang_code, idx, value )' .
                    'values (  ' .
                    $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ', ' .
                    $this->db->quote($lang_key, ilDBConstants::T_TEXT) . ', ' .
                    $this->db->quote($idx, ilDBConstants::T_INTEGER) . ', ' .
                    $this->db->quote($option, ilDBConstants::T_TEXT) .
                    ')';
                $this->db->manipulate($query);
            }
        }
    }

    public function update() : void
    {
        if (is_array($this->confirmed_objects) && count($this->confirmed_objects) > 0) {
            // we need the "old" options for the search
            $def = $this->getADTDefinition();
            $def = clone($def);
            $def->setOptions($this->old_options);
            $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($def, false, true);
            ilADTFactory::initActiveRecordByType();

            foreach ($this->confirmed_objects as $old_option => $item_ids) {
                // get complete old values
                $old_values = array();
                foreach ($this->findBySingleValue($search, $old_option) as $item) {
                    $old_values[$item[0] . "_" . $item[1] . "_" . $item[2]] = $item[3];
                }

                foreach ($item_ids as $item => $new_option) {
                    $parts = explode("_", $item);
                    $obj_id = $parts[0];
                    $sub_type = $parts[1];
                    $sub_id = $parts[2];

                    // update existing value (with changed option)
                    if (isset($old_values[$item])) {
                        $old_value = $old_values[$item];
                        // find changed option in old value
                        //$old_value = explode(ilADTMultiEnumDBBridge::SEPARATOR, $old_values[$item]);
                        // remove separators
                        //array_shift($old_value);
                        //array_pop($old_value);

                        //$old_idx = array_keys($old_value, $old_option);
                        $old_idx = $old_value;
                        if (isset($old_idx)) {
                            $primary = array(
                                "obj_id" => array("integer", $obj_id),
                                "sub_type" => array("text", $sub_type),
                                "sub_id" => array("integer", $sub_id),
                                "field_id" => array("integer", $this->getFieldId())
                            );

                            $index_old = array_merge(
                                $primary,
                                [
                                    'value_index' => [ilDBConstants::T_INTEGER, $old_idx]
                                ]
                            );
                            $index_new = array_merge(
                                $primary,
                                [
                                    'value_index' => [ilDBConstants::T_INTEGER, $new_option]
                                ]
                            );
                            ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $index_old, 'MultiEnum');

                            if (is_numeric($new_option)) {
                                ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $index_new, 'MultiEnum');
                                ilADTActiveRecordByType::create('adv_md_values', $index_new, 'MultiEnum');
                            } else {
                            }
                        }
                    }

                    if ($sub_type == "wpg") {
                        // #15763 - adapt advmd page lists
                        ilPCAMDPageList::migrateField($obj_id, $this->getFieldId(), $old_option, $new_option, true);
                    }
                }
            }

            $this->confirmed_objects = array();
        }

        parent::update();
        $this->updateOptionTranslations();
    }

    protected function addPropertiesToXML(ilXmlWriter $a_writer) : void
    {
        foreach ($this->getOptions() as $value) {
            $a_writer->xmlElement('FieldValue', null, $value);
        }
        foreach ($this->getOptionTranslations() as $lang_key => $translations) {
            foreach ((array) $translations as $value) {
                $a_writer->xmlElement('FieldValue', ['id' => $lang_key], $value);
            }
        }
    }

    public function importXMLProperty(string $a_key, string $a_value) : void
    {
        if (!$a_key) {
            $this->options[] = $a_value;
        } else {
            $this->option_translations[$a_key][] = $a_value;
        }
    }

    public function getValueForXML(ilADT $element) : string
    {
        return $element->getSelection();
    }

    public function importValueFromXML(string $a_cdata) : void
    {
        $this->getADT()->setSelection($a_cdata);
    }

    public function prepareElementForEditor(ilADTFormBridge $a_bridge) : void
    {
        assert($a_bridge instanceof ilADTEnumFormBridge);

        $a_bridge->setAutoSort(false);
    }

    protected function import(array $a_data) : void
    {
        parent::import($a_data);

        $query = 'select * from adv_mdf_enum ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'order by idx';
        $res = $this->db->query($query);
        $options = [];
        $default = [];
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId());
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->lang_code == $record->getDefaultLanguage()) {
                $default[(int) $row->idx] = (string) $row->value;
            }
            $options[(string) $row->lang_code][(int) $row->idx] = (string) $row->value;
        }
        $this->setOptions($default);
        $this->setOptionTranslations($options);
    }

    public function _clone(int $a_new_record_id) : self
    {
        /** @var ilAdvancedMDFieldDefinitionSelect $obj */
        $obj = parent::_clone($a_new_record_id);
        $query = 'select * from adv_mdf_enum ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'order by idx';
        $res = $this->db->query($query);
        $options = [];
        $default = [];
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId());
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->lang_code == $record->getDefaultLanguage()) {
                $default[(int) $row->idx] = (string) $row->value;
            }
            $options[(string) $row->lang_code][(int) $row->idx] = (string) $row->value;
        }
        $obj->setOptions($default);
        $obj->setOptionTranslations($options);
        $obj->update();
        return $obj;
    }
}
