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

use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericData;
use ILIAS\AdvancedMetaData\Repository\FieldDefinition\TypeSpecificData\Select\Gateway;
use ILIAS\AdvancedMetaData\Repository\FieldDefinition\TypeSpecificData\Select\DatabaseGatewayImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificDataImplementation;

/**
 * AMD field type select
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelect extends ilAdvancedMDFieldDefinition
{
    private const REMOVE_ACTION_ID = "-iladvmdrm-";
    private const ADD_NEW_ENTRY_ID = "-advmd_add_new_entry-";

    protected array $confirm_objects = [];
    protected array $confirm_objects_values = [];
    protected ?array $confirmed_objects = null;
    protected ?array $old_options_array = null;
    protected SelectSpecificData $options;

    protected string $default_language;

    private \ilGlobalTemplateInterface $main_tpl;

    private Gateway $db_gateway;

    public function __construct(GenericData $generic_data, string $language = '')
    {
        global $DIC;

        parent::__construct($generic_data, $language);

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->db_gateway = new DatabaseGatewayImplementation($DIC->database());

        $this->readOptions();
    }

    public function getType(): int
    {
        return self::TYPE_SELECT;
    }

    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search): string
    {
        return (string) $a_adt_search->getADT()->getSelection();
    }

    protected function initADTDefinition(): ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
        $def->setNumeric(false);

        $def->setOptions($this->getOptionsInLanguageAsArray($this->language));
        return $def;
    }

    protected function options(): SelectSpecificData
    {
        return $this->options;
    }

    public function getOptionsInDefaultLanguageAsArray(): ?array
    {
        $default_language_values = [];
        foreach ($this->options()->getOptions() as $option) {
            if ($translation = $option->getTranslationInLanguage($this->default_language)) {
                $default_language_values[$option->optionID()] = $translation->getValue();
            }
        }
        return $default_language_values;
    }

    protected function getOptionsInLanguageAsArray(
        string $language,
        bool $default_as_fallback = true
    ): ?array {
        $current_language_values = [];
        foreach ($this->options()->getOptions() as $option) {
            if ($translation = $option->getTranslationInLanguage($language)) {
                $current_language_values[$option->optionID()] = $translation->getValue();
            } elseif (
                $default_as_fallback &&
                $translation = $option->getTranslationInLanguage($this->default_language)
            ) {
                $current_language_values[$option->optionID()] = $translation->getValue();
            }
        }
        return $current_language_values;
    }

    protected function importFieldDefinition(array $a_def): void
    {
    }

    protected function getFieldDefinition(): array
    {
        return [];
    }

    public function getFieldDefinitionForTableGUI(string $content_language): array
    {
        if (strlen($content_language)) {
            $options = $this->getOptionsInLanguageAsArray($content_language);
        } else {
            $options = $this->getOptionsInDefaultLanguageAsArray();
        }
        return [
            $this->lng->txt("meta_advmd_select_options") => implode(",", $options)
        ];
    }

    protected function addCustomFieldToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled = false,
        string $language = ''
    ): void {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->addCustomFieldToDefinitionFormInTranslationMode($a_form, $a_disabled, $language);
            return;
        }

        if (!$this->options()->hasOptions()) {
            $this->addCreateOptionsFieldsToDefinitionForm($a_form, $a_disabled);
            return;
        }

        $this->addEditOptionsFieldsToDefinitionForm($this->options(), $a_form, $a_disabled);
    }

    protected function addCreateOptionsFieldsToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled
    ): void {
        $field = new ilTextInputGUI($this->lng->txt('meta_advmd_select_options'), 'opts_new');
        $field->setRequired(true);
        $field->setMulti(true);
        $field->setMaxLength(255);
        $field->setDisabled($a_disabled);

        $a_form->addItem($field);
    }

    protected function addEditOptionsFieldsToDefinitionForm(
        SelectSpecificData $options,
        ilPropertyFormGUI $a_form,
        bool $a_disabled
    ): void {
        $entries = new ilRadioGroupInputGUI(
            $this->lng->txt('meta_advmd_select_options_edit'),
            'opts_edit'
        );

        $position_identifiers = [
            '0' => $this->lng->txt('meta_advmd_select_first_position_identifier')
        ];
        foreach ($options->getOptions() as $option) {
            $position_identifiers[(string) ($option->getPosition() + 1)] = sprintf(
                $this->lng->txt('meta_advmd_select_position_identifier'),
                $option->getTranslationInLanguage($this->default_language)->getValue()
            );
        }

        $disabled_checkbox_overwrites = [];

        foreach ($options->getOptions() as $option) {
            $option_value = $option->getTranslationInLanguage($this->default_language)->getValue();
            $hidden = $this->addRadioToEntriesGroup(
                $entries,
                $position_identifiers,
                $option->getPosition(),
                $option_value,
                $option_value,
                (string) $option->optionID(),
                count(iterator_to_array($options->getOptions())) > 1,
                $a_disabled
            );
            if (!is_null($hidden)) {
                $disabled_checkbox_overwrites[] = $hidden;
            }
        }

        /*
         * Add radio to add new entry
         */
        $this->addRadioToEntriesGroup(
            $entries,
            $position_identifiers,
            null,
            $this->lng->txt('meta_advmd_select_new_option'),
            '',
            self::ADD_NEW_ENTRY_ID,
            false,
            $a_disabled
        );

        $entries->setDisabled($a_disabled);
        $a_form->addItem($entries);

        foreach ($disabled_checkbox_overwrites as $input) {
            $a_form->addItem($input);
        }
    }

    /**
     * Returns hidden inputs if a disabled checkbox is in the radio,
     * such that we can use the workaround for having disabled checkboxes still
     * write to post.
     */
    protected function addRadioToEntriesGroup(
        ilRadioGroupInputGUI $entries,
        array $position_select_options,
        ?int $position_value,
        string $label,
        string $value,
        string $id,
        bool $with_delete_checkbox,
        bool $disabled
    ): ?ilHiddenInputGUI {
        $disabled_checkbox_overwrite = null;
        $radio = new ilRadioOption($label, $id);

        $value_input = new ilTextInputGUI(
            $this->lng->txt('meta_advmd_select_option_value'),
            'value_' . $id
        );
        $value_input->setRequired(true);
        $value_input->setMaxLength(255);
        $value_input->setValue($value);
        $value_input->setDisabled($disabled);
        $radio->addSubItem($value_input);

        $position = new ilSelectInputGUI(
            $this->lng->txt('meta_advmd_select_option_position'),
            'position_' . $id
        );
        $relevant_position_select_options = $position_select_options;
        if (!is_null($position_value) && isset($relevant_position_select_options[$position_value + 1])) {
            unset($relevant_position_select_options[$position_value + 1]);
        }
        $position->setOptions($relevant_position_select_options);
        $position->setValue($position_value);
        $position->setDisabled($disabled);
        $radio->addSubItem($position);

        if ($with_delete_checkbox) {
            $delete = new ilCheckboxInputGUI(
                $this->lng->txt('meta_advmd_select_delete_option'),
                'delete_me_' . $id
            );
            $delete->setDisabled($disabled);
            $radio->addSubItem($delete);

            /*
             * If disabled, checkboxes don't come with a hidden input to write to post,
             * this is a workaround.
             */
            if ($disabled) {
                $hidden = new ilHiddenInputGUI('delete_me_' . $id);
                $hidden->setValue("1");
                $disabled_checkbox_overwrite = $hidden;
            }
        }

        $radio->setDisabled($disabled);
        $entries->addOption($radio);
        return $disabled_checkbox_overwrite;
    }

    protected function addCustomFieldToDefinitionFormInTranslationMode(
        ilPropertyFormGUI $form,
        bool $disabled,
        string $language = ''
    ): void {
        $default_language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();

        $first = true;
        foreach ($this->options()->getOptions() as $option) {
            $title = '';
            if ($first) {
                $title = $this->lng->txt("meta_advmd_select_options");
            }
            $text = new ilTextInputGUI(
                $title,
                'opts__' . $language . '__' . $option->optionID()
            );

            if ($option->hasTranslationInLanguage($language)) {
                $text->setValue($option->getTranslationInLanguage($language)->getValue());
            }

            $default_value = '';
            if ($option->hasTranslationInLanguage($default_language)) {
                $default_value = $option->getTranslationInLanguage($default_language)->getValue();
            }

            $text->setInfo($default_language . ': ' . $default_value);
            $text->setMaxLength(255);
            $text->setRequired(true);

            $first = false;
            $form->addItem($text);
        }
    }

    /**
     * Process custom post values from definition form
     */
    protected function buildConfirmedObjects(ilPropertyFormGUI $a_form): ?array
    {
        // #15719
        $recipes = $a_form->getInput("conf_det");
        if (is_array($recipes[$this->getFieldId()] ?? null)) {
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

    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = ''): void
    {
        $this->importNewSelectOptions(true, $a_form, $language);
    }

    protected function importNewSelectOptions(
        bool $multi,
        ilPropertyFormGUI $a_form,
        string $language = ''
    ): void {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->importTranslatedFormPostValues($a_form, $language);
            return;
        }

        $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
            $this->getADTDefinition(),
            false,
            $multi
        );

        if (!strlen($language)) {
            $language = $this->default_language;
        }

        if ($new = $a_form->getInput('opts_new')) {
            foreach ($new as $position => $value) {
                $new_option = $this->options()->addOption();
                $new_option->setPosition((int) $position);
                $new_translation = $new_option->addTranslation($language);
                $new_translation->setValue(trim($value));
            }
            return;
        }

        $edited_id = $a_form->getInput('opts_edit');
        if ($edited_id === '' || is_null($edited_id)) {
            return;
        }
        if ($edited_id !== self::ADD_NEW_ENTRY_ID) {
            $edited_id = (int) $edited_id;
        }

        $this->old_options_array = $this->getOptionsInLanguageAsArray($language);

        /*
         * Delete option if needed
         */
        if ($a_form->getInput('delete_me_' . $edited_id) && $edited_id !== self::ADD_NEW_ENTRY_ID) {
            $old_option_value = $this->options()
                                     ->getOption($edited_id)
                                     ?->getTranslationInLanguage($language)?->getValue() ?? '';
            $this->options()->removeOption($edited_id);

            /*
             * If option is to be deleted, collect objects whith that value and
             * and prepare confirmation of their migration.
             */
            $this->confirmed_objects = $this->buildConfirmedObjects($a_form);
            $already_confirmed = is_array($this->confirmed_objects);

            $in_use = $this->findBySingleValue($search, $edited_id);
            if (is_array($in_use)) {
                foreach ($in_use as $item) {
                    if (!$already_confirmed) {
                        $this->confirm_objects[$edited_id][] = $item;
                        $this->confirm_objects_values[$edited_id] = $old_option_value;
                    }
                }
            }

            return;
        }

        $new_value = (string) $a_form->getInput('value_' . $edited_id);
        $new_position = (int) $a_form->getInput('position_' . $edited_id);

        /*
         * Create new option if needed, else assign the new value
         */
        if ($edited_id === self::ADD_NEW_ENTRY_ID) {
            $edited_option = $this->options()->addOption();
        } else {
            $edited_option = $this->options()->getOption($edited_id);
        }

        if ($edited_option->hasTranslationInLanguage($this->default_language)) {
            $edited_option->getTranslationInLanguage($this->default_language)->setValue($new_value);
        } else {
            $edited_option->addTranslation($this->default_language)->setValue($new_value);
        }

        /*
         * Update positions and value
         * If an entry is is moved down, shift new position to reflect entry not
         * being in its old position anymore.
         */
        if ($new_position > $edited_option->getPosition()) {
            $new_position -= 1;
        }

        $position = 0;
        if ($position === $new_position) {
            $edited_option->setPosition($position);
            $position++;
        }
        foreach ($this->options()->getOptions() as $option) {
            if ($option === $edited_option) {
                continue;
            }
            $option->setPosition($position);
            $position++;

            if ($position === $new_position) {
                $edited_option->setPosition($position);
                $position++;
            }
        }
    }

    /**
     * @param ilADTEnumSearchBridgeMulti $a_search
     * @param                            $a_value
     * @return array
     * @todo fix $a_value type
     */
    protected function findBySingleValue(ilADTSearchBridge $a_search, $a_value): array
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

    protected function importTranslatedFormPostValues(ilPropertyFormGUI $form, string $language): void
    {
        foreach ($this->options()->getOptions() as $option) {
            $value = $form->getInput('opts__' . $language . '__' . $option->optionID());
            $value = trim($value);

            if ($option->hasTranslationInLanguage($language)) {
                $option->getTranslationInLanguage($language)->setValue($value);
                continue;
            }
            $new_translation = $option->addTranslation($language);
            $new_translation->setValue($value);
        }
    }

    public function importDefinitionFormPostValuesNeedsConfirmation(): bool
    {
        return is_array($this->confirm_objects) && count($this->confirm_objects) > 0;
    }
    public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $objDefinition = $DIC['objDefinition'];

        $post_conf_det = (array) ($this->http->request()->getParsedBody()['conf_det'] ?? []);
        $post_conf = (array) ($this->http->request()->getParsedBody()['conf'] ?? []);

        $custom_field = $a_form->getItemByPostVar('opts_edit');
        $custom_field->setDisabled(true);
        foreach ($custom_field->getSubInputItemsRecursive() as $sub_input) {
            $sub_input->setDisabled(true);
            /*
             * If disabled, checkboxes don't come with a hidden input to write to post,
             * this is a workaround.
             */
            if ($sub_input instanceof ilCheckboxInputGUI) {
                $hidden = new ilHiddenInputGUI($sub_input->getPostVar());
                $hidden->setValue($sub_input->getValue());
                $a_form->addItem($hidden);
            }
        }

        if (is_array($this->confirm_objects) && count($this->confirm_objects) > 0) {
            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("md_adv_confirm_definition_select_section"));
            $a_form->addItem($sec);

            foreach ($this->confirm_objects as $old_option_index => $items) {
                $old_option_value = $this->confirm_objects_values[$old_option_index];
                $details = new ilRadioGroupInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option") . ': "' . $old_option_value . '"',
                    "conf_det[" . $this->getFieldId() . "][" . $old_option_index . "]"
                );
                $details->setRequired(true);
                $details->setValue("sum");
                $a_form->addItem($details);

                // automatic reload does not work
                if (isset($post_conf_det[$this->getFieldId()][$old_option_index])) {
                    $details->setValue($post_conf_det[$this->getFieldId()][$old_option_index]);
                }

                $sum = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_all"), "sum");
                $details->addOption($sum);

                $sel = new ilSelectInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option_all_action"),
                    "conf_det_act[" . $this->getFieldId() . "][" . $old_option_index . "]"
                );
                $sel->setRequired(true);
                $options = array(
                    "" => $lng->txt("please_select"),
                    self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                );
                foreach ($this->options()->getOptions() as $new_option) {
                    $new_id = $new_option->optionID();
                    $new_value = $new_option->getTranslationInLanguage($this->default_language)->getValue();
                    $options['idx_' . $new_id] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_value . '"';
                }
                $sel->setOptions($options);
                $sum->addSubItem($sel);

                // automatic reload does not work
                if (isset($post_conf_det[$this->getFieldId()][$old_option_index])) {
                    if ($post_conf_det[$this->getFieldId()][$old_option_index]) {
                        $sel->setValue($post_conf_det[$this->getFieldId()][$old_option_index]);
                    } elseif ($post_conf_det[$this->getFieldId()][$old_option_index] == "sum") {
                        $sel->setAlert($lng->txt("msg_input_is_required"));
                        $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                    }
                }
                $single = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_single"), "sgl");
                $details->addOption($single);
                foreach ($items as $item) {
                    $obj_id = (int) $item[0];
                    $sub_type = (string) $item[1];
                    $sub_id = (int) $item[2];

                    /*
                     * media objects are saved in adv_md_values with obj_id=0, and their actual obj_id
                     * as sub_id.
                     */
                    if ($sub_type === 'mob') {
                        $obj_id = $sub_id;
                        $sub_id = 0;
                    }

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
                        "conf[" . $this->getFieldId() . "][" . $old_option_index . "][" . $item_id . "]"
                    );
                    $sel->setRequired(true);
                    $options = array(
                        "" => $lng->txt("please_select"),
                        self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                    );
                    foreach ($this->options()->getOptions() as $new_option) {
                        $new_id = $new_option->optionID();
                        $new_value = $new_option->getTranslationInLanguage($this->default_language)->getValue();
                        $options['idx_' . $new_id] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_value . '"';
                    }
                    $sel->setOptions($options);

                    // automatic reload does not work
                    if (isset($post_conf[$this->getFieldId()][$old_option_index][$item_id])) {
                        if ($post_conf[$this->getFieldId()][$old_option_index][$item_id]) {
                            $sel->setValue($post_conf[$this->getFieldId()][$old_option_index][$item_id]);
                        } elseif ($post_conf_det[$this->getFieldId()][$old_option_index] == "sgl") {
                            $sel->setAlert($lng->txt("msg_input_is_required"));
                            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                        }
                    }

                    $single->addSubItem($sel);
                }
            }
        }
    }

    public function delete(): void
    {
        $this->deleteOptions();
        parent::delete();
    }

    public function save(bool $a_keep_pos = false): void
    {
        parent::save($a_keep_pos);
        $this->saveOptions();
    }

    protected function deleteOptions(): void
    {
        $this->db_gateway->delete($this->getFieldId());
    }

    protected function updateOptions(): void
    {
        $this->db_gateway->update($this->options());
        $this->options = $this->db_gateway->readByID($this->getFieldId());
    }

    protected function saveOptions(): void
    {
        $this->db_gateway->create($this->getFieldId(), $this->options());
        $this->options = $this->db_gateway->readByID($this->getFieldId());
    }

    public function update(): void
    {
        if (is_array($this->confirmed_objects) && count($this->confirmed_objects) > 0) {
            // we need the "old" options for the search
            $def = $this->getADTDefinition();
            $def = clone($def);
            $def->setOptions($this->old_options_array);
            $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($def, false, true);
            ilADTFactory::initActiveRecordByType();

            $page_list_mappings = [];

            foreach ($this->confirmed_objects as $old_option => $item_ids) {
                // get complete old values
                $old_values = [];
                foreach ($this->findBySingleValue($search, $old_option) as $item) {
                    $old_values[$item[0] . "_" . $item[1] . "_" . $item[2]] = $item[3];
                }

                foreach ($item_ids as $item => $new_option) {
                    $parts = explode("_", $item);
                    $obj_id = (int) $parts[0];
                    $sub_type = $parts[1];
                    $sub_id = (int) $parts[2];

                    // update existing value (with changed option)
                    if (isset($old_values[$item])) {
                        $old_id = $old_values[$item];

                        $primary = array(
                            "obj_id" => array("integer", $obj_id),
                            "sub_type" => array("text", $sub_type),
                            "sub_id" => array("integer", $sub_id),
                            "field_id" => array("integer", $this->getFieldId())
                        );

                        $id_old = array_merge(
                            $primary,
                            [
                                'value_index' => [ilDBConstants::T_INTEGER, $old_id]
                            ]
                        );
                        $id_new = array_merge(
                            $primary,
                            [
                                'value_index' => [ilDBConstants::T_INTEGER, $new_option]
                            ]
                        );
                        ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $id_old, 'MultiEnum');

                        if (is_numeric($new_option)) {
                            ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $id_new, 'MultiEnum');
                            ilADTActiveRecordByType::create('adv_md_values', $id_new, 'MultiEnum');
                        }
                    }

                    if ($sub_type == "wpg") {
                        // #15763 - adapt advmd page lists
                        $page_list_mappings[(string) $old_option] = (string) $new_option;
                    }
                }
            }

            if (!empty($page_list_mappings)) {
                ilPCAMDPageList::migrateField(
                    $this->getFieldId(),
                    $page_list_mappings
                );
            }

            $this->confirmed_objects = array();
        }

        parent::update();
        $this->updateOptions();
    }

    protected function addPropertiesToXML(ilXmlWriter $a_writer): void
    {
        foreach ($this->options()->getOptions() as $option) {
            foreach ($option->getTranslations() as $translation) {
                $a_writer->xmlElement(
                    'FieldValue',
                    ['id' => $translation->language()],
                    $translation->getValue()
                );
            }
        }
    }

    /**
     * Since the XML import only allows for a key-value pair, we also rely on
     * the order of properties to sort translations into options.
     */
    public function importXMLProperty(string $a_key, string $a_value): void
    {
        $language = $a_key;
        if ($language === '') {
            $language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();
        }

        $associated_option = null;
        $max_position = -1;
        foreach ($this->options()->getOptions() as $option) {
            if (
                !$option->hasTranslationInLanguage($language) &&
                !isset($associated_option)
            ) {
                $associated_option = $option;
            }
            $max_position = max($max_position, $option->getPosition());
        }
        if (!isset($associated_option)) {
            $associated_option = $this->options()->addOption();
            $associated_option->setPosition($max_position + 1);
        }

        $new_translation = $associated_option->addTranslation($language);
        $new_translation->setValue($a_value);
    }

    public function getValueForXML(ilADT $element): string
    {
        return $element->getSelection();
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $a_cdata = $this->translateLegacyImportValueFromXML($a_cdata);
        $this->getADT()->setSelection($a_cdata);
    }

    /**
     * On import from <7 options are not given by index but by
     * their label. There is nothing in the XML by which one could
     * tell apart legacy and standard imports, so we have to
     * make a best guess here (32410).
     *
     * Might fail for enums where the labels are integers.
     * See also ilAdvancedMDFieldDefinitionGroupBased::importValueFromXML.
     */
    protected function translateLegacyImportValueFromXML(string $value): string
    {
        if (is_numeric($value) && !is_null($this->options()->getOption((int) $value))) {
            return $value;
        }

        $default_language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();
        foreach ($this->options()->getOptions() as $option) {
            if ($value = $option->getTranslationInLanguage($default_language)) {
                return (string) $option->optionID();
            }
        }

        return $value;
    }

    public function prepareElementForEditor(ilADTFormBridge $a_bridge): void
    {
        assert($a_bridge instanceof ilADTEnumFormBridge);

        $a_bridge->setAutoSort(false);
    }

    protected function readOptions(): void
    {
        if ($this->getFieldId()) {
            $this->options = $this->db_gateway->readByID($this->getFieldId());
        }
        if (!isset($this->options)) {
            $this->options = new SelectSpecificDataImplementation();
        }

        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId());
        $this->default_language = $record->getDefaultLanguage();
    }

    public function _clone(int $a_new_record_id): self
    {
        /** @var ilAdvancedMDFieldDefinitionSelect $obj */
        $obj = parent::_clone($a_new_record_id);
        $this->db_gateway->delete($obj->getFieldId());
        $this->db_gateway->create($obj->getFieldId(), $this->options());
        $obj->update();
        return $obj;
    }
}
