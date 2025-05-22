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

/**
 * @ilCtrl_IsCalledBy ilDclCreateViewDefinitionGUI: ilDclTableViewEditGUI
 */
class ilDclCreateViewDefinitionGUI
{
    public ilDclTableView $tableview;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(int $tableview_id)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tableview = ilDclTableView::findOrGetInstance($tableview_id);
    }

    public function executeCommand(): string
    {
        if ($this->ctrl->getCmd() === 'saveTable') {
            $this->saveTable();
        }
        $table = new ilDclCreateViewTableGUI($this);
        $this->tpl->setContent($table->getHTML());
        return '';

    }

    public function saveTable(): void
    {
        $f = new ilDclDefaultValueFactory();
        $raw_values = $this->http->request()->getParsedBody();
        foreach ($raw_values as $key => $value) {
            if (strpos($key, "default_") === 0) {
                $parts = explode("_", $key);
                $id = $parts[1];
                $data_type_id = intval($parts[2]);

                // Delete all field values associated with this id
                $existing_values = ilDclTableViewBaseDefaultValue::findAll($data_type_id, (int) $id);

                if (!is_null($existing_values)) {
                    foreach ($existing_values as $existing_value) {
                        $existing_value->delete();
                    }
                }

                // Create fields
                if ($value !== '') {
                    // Check number field
                    $default_value = $f->create($data_type_id);
                    if ($data_type_id === ilDclDatatype::INPUTFORMAT_NUMBER) {
                        if (!ctype_digit($value)) {
                            $this->tpl->setOnScreenMessage(
                                'failure',
                                $this->lng->txt('dcl_tableview_default_value_fail'),
                                true
                            );
                            $this->ctrl->saveParameter($this, 'tableview_id');
                            $this->ctrl->redirect($this, 'presentation');
                        }
                    }

                    if ($default_value::class == ilDclTableViewNumberDefaultValue::class) {
                        $default_value->setValue((int) $value);
                    } else {
                        $default_value->setValue($value);
                    }
                    $default_value->setTviewSetId((int) $id);
                    $default_value->create();
                }
            }
        }
        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($this->tableview->getFieldSettings() as $setting) {
            if (!$setting->getFieldObject()->isStandardField()) {

                // Radio Inputs
                $attribute = "RadioGroup";
                $selection_key = $attribute . '_' . $setting->getField();
                $selection = $this->http->wrapper()->post()->retrieve(
                    $selection_key,
                    $this->refinery->kindlyTo()->string()
                );
                $selected_radio_attribute = explode("_", $selection)[0];

                foreach (["LockedCreate",
                          "RequiredCreate",
                          "VisibleCreate",
                          "NotVisibleCreate"
                         ] as $radio_attribute) {
                    $result = false;

                    if ($selected_radio_attribute === $radio_attribute) {
                        $result = true;
                    }

                    $setting->{'set' . $radio_attribute}($result);
                }

                // Text Inputs
                $attribute = "DefaultValue";
                $key = $attribute . '_' . $setting->getField();
                if ($this->http->wrapper()->post()->has($key)) {
                    $attribute_value = $this->http->wrapper()->post()->retrieve(
                        $key,
                        $this->refinery->kindlyTo()->string()
                    );
                } else {
                    $attribute_value = "";
                }

                $setting->{'set' . $attribute}($attribute_value);

                $setting->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_updated'), true);
        $this->ctrl->saveParameter($this, 'tableview_id');
        $this->ctrl->redirect($this, 'presentation');
    }
}
