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

namespace ILIAS\User\Profile\Fields;

class StandardFieldsGUI
{
    public function __construct(

    ) {

    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->prepareOutput();
    }

    public function showCmd(): void
    {
        $this->raiseErrorOnMissingWrite();

        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('mail');
        $this->lng->loadLanguageModule('chatroom');
        $this->setSubTabs('settings');
        $this->tabs_gui->activateTab('settings');
        $this->tabs_gui->activateSubTab('standard_fields');

        $tab = new \ilUserFieldSettingsTableGUI(
            $this,
            'settings'
        );
        if ($this->confirm_change) {
            $tab->setConfirmChange();
        }
        $this->tpl->setContent($tab->getHTML());
    }

    public function confirmSavedCmd(): void
    {
        $this->raiseErrorOnMissingWrite();
        $this->saveGlobalUserSettingsObject('save');
    }

    public function saveCmd(string $action = ''): void
    {
        $this->raiseErrorOnMissingWrite();

        $checked = $this->user_request->getChecked();
        $selected = $this->user_request->getSelect();

        $user_settings_config = $this->user_settings_config;

        // see \ilUserFieldSettingsTableGUI
        $up = new \ilUserProfile();
        $up->skipField('username');
        $field_properties = $up->getStandardFields();
        $profile_fields = array_keys($field_properties);

        $valid = true;
        foreach ($profile_fields as $field) {
            if (($checked['required_' . $field] ?? false) &&
                !(int) ($checked['visib_reg_' . $field] ?? null)
            ) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('invalid_visible_required_options_selected'));
            $this->confirm_change = 1;
            $this->settingsObject();
            return;
        }

        // For the following fields, the required state can not be changed
        $fixed_required_fields = [
            'firstname' => 1,
            'lastname' => 1,
            'upload' => 0,
            'password' => 0,
            'language' => 0,
            'skin_style' => 0,
            'hide_own_online_status' => 0
        ];

        // Reset user confirmation
        if ($action == 'save') {
            \ilMemberAgreement::_reset();
        }

        $changed_fields = $this->collectChangedFields();
        if ($this->handleChangeListeners($changed_fields, $field_properties)) {
            return;
        }

        foreach ($profile_fields as $field) {
            // Enable disable searchable
            if (\ilUserSearchOptions::_isSearchable($field)) {
                \ilUserSearchOptions::_saveStatus(
                    $field,
                    (bool) ($checked['searchable_' . $field] ?? false)
                );
            }

            if (!($checked['visible_' . $field] ?? false) && !($field_properties[$field]['visible_hide'] ?? false)) {
                $user_settings_config->setVisible(
                    $field,
                    false
                );
            } else {
                $user_settings_config->setVisible(
                    $field,
                    true
                );
            }

            if (!($checked['changeable_' . $field] ?? false) &&
                !($field_properties[$field]['changeable_hide'] ?? false)) {
                $user_settings_config->setChangeable(
                    $field,
                    false
                );
            } else {
                $user_settings_config->setChangeable(
                    $field,
                    true
                );
            }

            // registration visible
            if (($checked['visib_reg_' . $field] ?? false) && !($field_properties[$field]['visib_reg_hide'] ?? false)) {
                $this->settings->set(
                    'usr_settings_visib_reg_' . $field,
                    '1'
                );
            } else {
                $this->settings->set(
                    'usr_settings_visib_reg_' . $field,
                    '0'
                );
            }

            if ($checked['visib_lua_' . $field] ?? false) {
                $this->settings->set(
                    'usr_settings_visib_lua_' . $field,
                    '1'
                );
            } else {
                $this->settings->set(
                    'usr_settings_visib_lua_' . $field,
                    '0'
                );
            }

            if ((int) ($checked['changeable_lua_' . $field] ?? false)) {
                $this->settings->set(
                    'usr_settings_changeable_lua_' . $field,
                    '1'
                );
            } else {
                $this->settings->set(
                    'usr_settings_changeable_lua_' . $field,
                    '0'
                );
            }

            if (($checked['export_' . $field] ?? false) && !($field_properties[$field]['export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_export_' . $field);
            }

            // Course export/visibility
            if (($checked['course_export_' . $field] ?? false) && !($field_properties[$field]['course_export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_course_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_course_export_' . $field);
            }

            // Group export/visibility
            if (($checked['group_export_' . $field] ?? false) && !($field_properties[$field]['group_export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_group_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_group_export_' . $field);
            }

            if (($checked['prg_export_' . $field] ?? false) && !($field_properties[$field]['prg_export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_prg_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_prg_export_' . $field);
            }

            $is_fixed = array_key_exists(
                $field,
                $fixed_required_fields
            );
            if (($is_fixed && $fixed_required_fields[$field]) || (!$is_fixed && ($checked['required_' . $field] ?? false))) {
                $this->ilias->setSetting(
                    'require_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('require_' . $field);
            }
        }

        $this->ilias->setSetting(
            'session_reminder_lead_time',
            $this->user_request->getDefaultSessionReminder()
        );

        if (isset($checked['export_preferences']) && $checked['export_preferences'] === 1) {
            $this->ilias->setSetting(
                'usr_settings_export_preferences',
                '1'
            );
        } else {
            $this->ilias->deleteSetting('usr_settings_export_preferences');
        }

        $this->ilias->setSetting(
            'mail_incoming_mail',
            $selected['default_mail_incoming_mail'] ?? '0'
        );
        $this->ilias->setSetting(
            'chat_osc_accept_msg',
            $selected['default_chat_osc_accept_msg'] ?? 'n'
        );
        $this->ilias->setSetting(
            'chat_broadcast_typing',
            $selected['default_chat_broadcast_typing'] ?? 'n'
        );
        $this->ilias->setSetting(
            'bs_allow_to_contact_me',
            $selected['default_bs_allow_to_contact_me'] ?? 'n'
        );
        $this->ilias->setSetting(
            'hide_own_online_status',
            $selected['default_hide_own_online_status'] ?? 'n'
        );

        if ($this->usrFieldChangeListenersAccepted && count($changed_fields) > 0) {
            $this->event->raise(
                'components/ILIAS/User',
                'onUserFieldAttributesChanged',
                $changed_fields
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('usr_settings_saved'));
        $this->settingsObject();
    }

    public function confirmUsrFieldChangeListenersCmd(): void
    {
        $this->usrFieldChangeListenersAccepted = true;
        $this->confirmSavedObject();
    }

    /**
     * @param array<InterestedUserFieldChangeListener> $interested_change_listeners
     */
    private function showFieldChangeComponentsListeningConfirmDialog(
        array $interested_change_listeners
    ): void {
        $post = $this->user_request->getParsedBody();
        $confirmDialog = new \ilConfirmationGUI();
        $confirmDialog->setHeaderText($this->lng->txt('usr_field_change_components_listening'));
        $confirmDialog->setFormAction($this->ctrl->getFormActionByClass(
            [self::class],
            'settings'
        ));
        $confirmDialog->setConfirm($this->lng->txt('confirm'), 'confirmUsrFieldChangeListeners');
        $confirmDialog->setCancel($this->lng->txt('cancel'), 'settings');

        $tpl = new \ilTemplate(
            'tpl.usr_field_change_listener_confirm.html',
            true,
            true,
            'components/ILIAS/User'
        );

        foreach ($interested_change_listeners as $interested_change_listener) {
            $tpl->setVariable('FIELD_NAME', $interested_change_listener->getName());
            foreach ($interested_change_listener->getAttributes() as $attribute) {
                $tpl->setVariable('ATTRIBUTE_NAME', $attribute->getName());
                foreach ($attribute->getComponents() as $component) {
                    $tpl->setVariable('COMPONENT_NAME', $component->getComponentName());
                    $tpl->setVariable('DESCRIPTION', $component->getDescription());
                    $tpl->setCurrentBlock('component');
                    $tpl->parseCurrentBlock('component');
                }
                $tpl->setCurrentBlock('attribute');
                $tpl->parseCurrentBlock('attribute');
            }
            $tpl->setCurrentBlock('field');
            $tpl->parseCurrentBlock('field');
        }

        $confirmDialog->addItem('', '0', $tpl->get());

        foreach ($post['chb'] as $postVar => $value) {
            $confirmDialog->addHiddenItem("chb[{$postVar}]", $value);
        }
        foreach ($post['select'] as $postVar => $value) {
            $confirmDialog->addHiddenItem("select[{$postVar}]", $value);
        }
        foreach ($post['current'] as $postVar => $value) {
            $confirmDialog->addHiddenItem("current[{$postVar}]", $value);
        }
        $this->tpl->setContent($confirmDialog->getHTML());
    }

    /**
     * @param array<string, ChangedUserFieldAttribute> $changed_fields
     * @param array<string, array>                     $field_properties => See \ilUserProfile::getStandardFields()
     */
    private function handleChangeListeners(
        array $changed_fields,
        array $field_properties
    ): bool {
        if (count($changed_fields) > 0) {
            $interested_change_listeners = [];
            foreach ($field_properties as $field_name => $properties) {
                if (!isset($properties['change_listeners'])) {
                    continue;
                }

                foreach ($properties['change_listeners'] as $change_listener_class_name) {
                    /**
                     * @var UserFieldAttributesChangeListener $listener
                     */
                    $listener = new $change_listener_class_name($this->dic);
                    foreach ($changed_fields as $changed_field) {
                        $attribute_name = $changed_field->getAttributeName();
                        $description_for_field = $listener->getDescriptionForField($field_name, $attribute_name);
                        if ($description_for_field !== null && $description_for_field !== '') {
                            $interested_change_listener = null;
                            foreach ($interested_change_listeners as $interested_listener) {
                                if ($interested_listener->getFieldName() === $field_name) {
                                    $interested_change_listener = $interested_listener;
                                    break;
                                }
                            }

                            if ($interested_change_listener === null) {
                                $interested_change_listener = new InterestedUserFieldChangeListener(
                                    $this->lng,
                                    $this->getTranslationForField($field_name, $properties),
                                    $field_name
                                );
                                $interested_change_listeners[] = $interested_change_listener;
                            }

                            $interested_attribute = $interested_change_listener->addAttribute($attribute_name);
                            $interested_attribute->addComponent(
                                $listener->getComponentName(),
                                $description_for_field
                            );
                        }
                    }
                }
            }

            if (!$this->usrFieldChangeListenersAccepted && count($interested_change_listeners) > 0) {
                $this->showFieldChangeComponentsListeningConfirmDialog($interested_change_listeners);
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, ChangedUserFieldAttribute>
     */
    private function collectChangedFields(): array
    {
        $changed_fields = [];
        $post = $this->user_request->getParsedBody();
        if (
            !isset($post['chb'])
            && !is_array($post['chb'])
            && !isset($post['current'])
            && !is_array($post['current'])
        ) {
            return $changed_fields;
        }

        $old = $post['current'];
        $new = $post['chb'];

        foreach ($old as $key => $old_value) {
            if (!isset($new[$key])) {
                $is_boolean = filter_var($old_value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $new[$key] = $is_boolean ? '0' : $old_value;
            }
        }

        $oldToNewDiff = array_diff_assoc($old, $new);

        foreach ($oldToNewDiff as $key => $old_value) {
            $changed_fields[$key] = new ChangedUserFieldAttribute($key, $old_value, $new[$key]);
        }

        return $changed_fields;
    }

    private function raiseErrorOnMissingWrite(): void
    {
        if (!$this->access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        )) {
            $this->ilias->raiseError(
                $this->lng->txt('permission_denied'),
                $this->ilias->error_obj->MESSAGE
            );
        }
    }
}
