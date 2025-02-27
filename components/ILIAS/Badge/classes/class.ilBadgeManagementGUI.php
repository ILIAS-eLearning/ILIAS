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

use ILIAS\Badge\ilBadgeImage;
use ILIAS\ResourceStorage\Services;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\Badge\ilBadgeTableGUI;
use ILIAS\Badge\ilBadgeUserTableGUI;
use ILIAS\Refinery\Factory;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Setup\ArrayEnvironment;

/**
 * @ilCtrl_Calls ilBadgeManagementGUI: ilPropertyFormGUI
 */
class ilBadgeManagementGUI
{
    public const TABLE_ALL_OBJECTS_ACTION = 'ALL_OBJECTS';

    private ilBadgeGUIRequest $request;
    private ilBadgeManagementSessionRepository $session_repo;
    private ilLanguage $lng;
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs;
    private ilAccessHandler $access;
    private ilToolbarGUI $toolbar;
    private ilGlobalTemplateInterface $tpl;
    private ilObjUser $user;
    private \ILIAS\UI\Factory $ui_factory;
    private int $parent_obj_id;
    private string $parent_obj_type;

    private ?ilBadgeImage $badge_image_service = null;
    private ?Services $resource_storage;
    private ?FileUpload $upload_service;
    private ?ilBadgePictureDefinition $flavour_definition = null;
    private \ILIAS\HTTP\Services $http;
    private Factory $refinery;

    public function __construct(
        private readonly int $parent_ref_id,
        ?int $a_parent_obj_id = null,
        ?string $a_parent_obj_type = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->resource_storage = $DIC->resourceStorage();
        $this->upload_service = $DIC->upload();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $lng = $DIC->language();
        $this->parent_obj_id = $a_parent_obj_id
            ?: ilObject::_lookupObjId($parent_ref_id);
        $this->parent_obj_type = $a_parent_obj_type
            ?: ilObject::_lookupType($this->parent_obj_id);

        if (!ilBadgeHandler::getInstance()->isObjectActive($this->parent_obj_id)) {
            throw new ilException('inactive object');
        }

        $lng->loadLanguageModule('badge');

        $this->request = new ilBadgeGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->session_repo = new ilBadgeManagementSessionRepository();
        $this->badge_image_service = new ilBadgeImage(
            $DIC->resourceStorage(),
            $DIC->upload(),
            $DIC->ui()->mainTemplate()
        );
        $this->flavour_definition = new ilBadgePictureDefinition();
    }

    /**
     * @param list<string> $splittable_user_ids
     * @return array{0: list<int>, 1: int}
     */
    private function splitBadgeAndUserIdsFromString(array $splittable_user_ids): array
    {
        $user_ids = [];
        $badge_id = null;

        if ($splittable_user_ids !== []) {
            if ($splittable_user_ids === ['ALL_OBJECTS']) {
                $parent_obj_id = $this->parent_obj_id;
                if (!$parent_obj_id && $this->parent_ref_id) {
                    $parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
                }

                if ($this->parent_ref_id) {
                    $user_ids = ilBadgeHandler::getInstance()->getUserIds($this->parent_ref_id, $parent_obj_id);
                }

                $badge_id = $this->http->wrapper()->query()->retrieve('bid', $this->refinery->kindlyTo()->int());
                return [$user_ids, $badge_id];
            } else {
                foreach ($splittable_user_ids as $row) {
                    if (str_contains($row, '_')) {
                        $split = explode('_', $row);

                        if ($badge_id === null && $split[0] !== '') {
                            $badge_id = (int) $split[0];
                        }

                        if ($split[1] !== '') {
                            $user_ids[] = (int) $split[1];
                        }
                    } else {
                        return [$user_ids, 0];
                    }
                }
            }
        }

        return [$user_ids, $badge_id];
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd('listBadges');

        switch ($next_class) {
            case 'ilpropertyformgui':
                // ajax - update
                if ($this->request->getBadgeId()) {
                    $badge = new ilBadge($this->request->getBadgeId());
                    $type = $badge->getTypeInstance();
                    $form = $this->initBadgeForm('edit', $type, $badge->getTypeId());
                    $this->setBadgeFormValues($form, $badge, $type);
                } // ajax- create
                else {
                    $type_id = $this->request->getType();
                    $ilCtrl->setParameter($this, 'type', $type_id);
                    $handler = ilBadgeHandler::getInstance();
                    $type = $handler->getTypeInstanceByUniqueId($type_id);
                    $form = $this->initBadgeForm('create', $type, $type_id);
                }
                $ilCtrl->forwardCommand($form);
                break;

            default:
                $render_default = true;
                global $DIC;
                $action_parameter_token = 'tid_id';
                $parameter = 'tid_table_action';

                $query = $DIC->http()->wrapper()->query();
                if ($query->has($action_parameter_token)) {
                    if ($query->has($action_parameter_token)) {
                        $id = $query->retrieve(
                            $action_parameter_token,
                            $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->string())
                        );
                        if (is_array($id)) {
                            $id = array_pop($id);
                        }
                        $DIC->ctrl()->setParameter($this, "tid", $id);
                    }
                }
                $action = '';
                if ($query->has($parameter)) {
                    $action = $query->retrieve($parameter, $DIC->refinery()->kindlyTo()->string());
                }
                if ($action === 'badge_table_activate') {
                    $this->activateBadges();
                } elseif ($action === 'badge_table_deactivate') {
                    $this->deactivateBadges();
                } elseif ($action === 'badge_table_edit') {
                    $this->editBadge();
                    $render_default = false;
                } elseif ($action === 'badge_table_delete') {
                    $this->confirmDeleteBadges();
                    $render_default = false;
                } elseif ($action === 'award_revoke_badge') {
                    $this->awardBadgeUserSelection();
                    $render_default = false;
                } elseif ($action === 'revokeBadge') {
                    $this->confirmDeassignBadge();
                    $render_default = false;
                } elseif ($action === 'assignBadge') {
                    $this->assignBadge();
                    $render_default = false;
                }

                if ($render_default) {
                    $this->$cmd();
                    break;
                }
                break;
        }
    }

    protected function setTabs(string $a_active): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTab(
            'badges',
            $lng->txt('obj_bdga'),
            $ilCtrl->getLinkTarget($this, 'listBadges')
        );

        $ilTabs->addSubTab(
            'users',
            $lng->txt('users'),
            $ilCtrl->getLinkTarget($this, 'listUsers')
        );

        $ilTabs->activateSubTab($a_active);
    }

    protected function hasWrite(): bool
    {
        $ilAccess = $this->access;
        return $ilAccess->checkAccess('write', '', $this->parent_ref_id);
    }

    protected function listBadges(): void
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs('badges');

        if ($this->hasWrite()) {
            $handler = ilBadgeHandler::getInstance();
            $valid_types = $handler->getAvailableTypesForObjType($this->parent_obj_type);
            if ($valid_types) {
                $options = [];
                foreach ($valid_types as $id => $type) {
                    $ilCtrl->setParameter($this, 'type', $id);
                    $options[$id] = $this->ui_factory->link()->standard(
                        $this->parent_obj_type !== 'bdga' ? ilBadge::getExtendedTypeCaption($type) : $type->getCaption(),
                        $ilCtrl->getLinkTarget($this, 'addBadge')
                    );
                    $ilCtrl->setParameter($this, 'type', null);
                }
                asort($options);
                $options = array_values($options);

                $ilToolbar->addComponent(
                    $this->ui_factory->dropdown()->standard($options)->withLabel($lng->txt('badge_create'))
                );
            } else {
                $this->tpl->setOnScreenMessage('info', $lng->txt('badge_no_valid_types_for_obj'));
            }

            $clip_ids = $this->session_repo->getBadgeIds();
            if (count($clip_ids) > 0) {
                if ($valid_types) {
                    $ilToolbar->addSeparator();
                }

                $tt = [];
                foreach ($this->getValidBadgesFromClipboard() as $badge) {
                    $tt[] = $badge->getTitle();
                }
                $ttid = 'bdgpst';

                $lng->loadLanguageModule('content');
                $ilToolbar->addButton(
                    $lng->txt('cont_paste_from_clipboard') .
                    ' (' . count($tt) . ')',
                    $ilCtrl->getLinkTarget($this, 'pasteBadges'),
                    '',
                    null,
                    '',
                    $ttid
                );
                $ilToolbar->addButton(
                    $lng->txt('clear_clipboard'),
                    $ilCtrl->getLinkTarget($this, 'clearClipboard')
                );
            }
        }

        $table = new ilBadgeTableGUI($this->parent_obj_id, $this->parent_obj_type, $this->hasWrite());
        $table->renderTable();
    }


    //
    // badge (CRUD)
    //

    protected function addBadge(?ilPropertyFormGUI $a_form = null): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $type_id = $this->request->getType();
        if (!$type_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        $ilCtrl->setParameter($this, 'type', $type_id);

        $handler = ilBadgeHandler::getInstance();
        $type = $handler->getTypeInstanceByUniqueId($type_id);
        if (!$type) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        if (!$a_form) {
            $a_form = $this->initBadgeForm('create', $type, $type_id);
        }

        $tpl->setContent($a_form->getHTML());
    }

    protected function initBadgeForm(
        string $a_mode,
        ilBadgeType $a_type,
        string $a_type_unique_id
    ): ilPropertyFormGUI {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, 'saveBadge'));
        $form->setTitle($lng->txt('badge_badge') . ' "' . $a_type->getCaption() . '"');

        $active = new ilCheckboxInputGUI($lng->txt('active'), 'act');
        $form->addItem($active);

        $title = new ilTextInputGUI($lng->txt('title'), 'title');
        $title->setMaxLength(255);
        $title->setRequired(true);
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI($lng->txt('description'), 'desc');
        $desc->setMaxNumOfChars(4000);
        $desc->setRequired(true);
        $form->addItem($desc);

        $crit = new ilTextAreaInputGUI($lng->txt('badge_criteria'), 'crit');
        $crit->setMaxNumOfChars(4000);
        $crit->setRequired(true);
        $form->addItem($crit);

        if ($a_mode === 'create') {
            // upload

            $img_mode = new ilRadioGroupInputGUI($lng->txt('image'), 'img_mode');
            $img_mode->setRequired(true);
            $img_mode->setValue('tmpl');
            $form->addItem($img_mode);

            $img_mode_tmpl = new ilRadioOption($lng->txt('badge_image_from_template'), 'tmpl');
            $img_mode->addOption($img_mode_tmpl);

            $img_mode_up = new ilRadioOption($lng->txt('badge_image_from_upload'), 'up');
            $img_mode->addOption($img_mode_up);

            $img_upload = new ilImageFileInputGUI($lng->txt('file'), 'img');
            $img_upload->setRequired(true);
            $img_upload->setSuffixes(['png', 'svg']);
            $img_mode_up->addSubItem($img_upload);

            // templates

            $valid_templates = ilBadgeImageTemplate::getInstancesByType($a_type_unique_id);
            if (count($valid_templates)) {
                $options = [];
                $options[''] = $lng->txt('please_select');
                foreach ($valid_templates as $tmpl) {
                    $options[$tmpl->getId()] = $tmpl->getTitle();
                }

                $tmpl = new ilSelectInputGUI($lng->txt('badge_image_template_form'), 'tmpl');
                $tmpl->setRequired(true);
                $tmpl->setOptions($options);
                $img_mode_tmpl->addSubItem($tmpl);
            } else {
                // no templates, activate upload
                $img_mode_tmpl->setDisabled(true);
                $img_mode->setValue('up');
            }
        } else {
            $img_upload = new ilImageFileInputGUI($lng->txt('image'), 'img');
            $img_upload->setSuffixes(['png', 'svg']);
            $img_upload->setAllowDeletion(false);
            $img_upload->setUseCache(false);
            $form->addItem($img_upload);
        }

        $valid = new ilTextInputGUI($lng->txt('badge_valid'), 'valid');
        $valid->setMaxLength(255);
        $form->addItem($valid);

        $custom = $a_type->getConfigGUIInstance();
        if ($custom instanceof ilBadgeTypeGUI) {
            $custom->initConfigForm($form, $this->parent_ref_id);
        }

        // :TODO: valid date/period

        if ($a_mode === 'create') {
            $form->addCommandButton('saveBadge', $lng->txt('save'));
        } else {
            $form->addCommandButton('updateBadge', $lng->txt('save'));
        }
        $form->addCommandButton('listBadges', $lng->txt('cancel'));

        return $form;
    }

    /**
     * @throws ilCtrlException
     * @throws IllegalStateException
     */
    protected function saveBadge(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $type_id = $this->request->getType();
        if (!$type_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        $ilCtrl->setParameter($this, 'type', $type_id);

        $handler = ilBadgeHandler::getInstance();
        $type = $handler->getTypeInstanceByUniqueId($type_id);
        if (!$type) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        $form = $this->initBadgeForm('create', $type, $type_id);
        $custom = $type->getConfigGUIInstance();

        if ($form->checkInput() &&
            (!$custom || $custom->validateForm($form))) {
            $badge = new ilBadge();
            $badge->setParentId($this->parent_obj_id); // :TODO: ref_id?
            $badge->setTypeId($type_id);
            $badge->setActive($form->getInput('act'));
            $badge->setTitle($form->getInput('title'));
            $badge->setDescription($form->getInput('desc'));
            $badge->setCriteria($form->getInput('crit'));
            $badge->setValid($form->getInput('valid'));

            if ($custom instanceof ilBadgeTypeGUI) {
                $badge->setConfiguration($custom->getConfigFromForm($form));
            }

            $badge->create();

            if ($form->getInput('img_mode') === 'up') {
                $this->badge_image_service->processImageUpload($badge);
            } else {
                $tmpl = new ilBadgeImageTemplate($form->getInput('tmpl'));
                $this->cloneBadgeTemplate($badge, new ResourceIdentification($tmpl->getImageRid()));
            }

            $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this, 'listBadges');
        }

        $form->setValuesByPost();
        $this->addBadge($form);
    }

    protected function editBadge(?ilPropertyFormGUI $a_form = null): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $badge_id = $this->request->getBadgeIdFromUrl();
        if (!$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        $ilCtrl->setParameter($this, 'bid', $badge_id);

        $badge = new ilBadge($badge_id);

        $static_cnt = ilBadgeHandler::getInstance()->countStaticBadgeInstances($badge);
        if ($static_cnt) {
            $this->tpl->setOnScreenMessage('info', sprintf($lng->txt('badge_edit_with_published'), $static_cnt));
        }

        if (!$a_form) {
            $type = $badge->getTypeInstance();
            $a_form = $this->initBadgeForm('edit', $type, $badge->getTypeId());
            $this->setBadgeFormValues($a_form, $badge, $type);
        }

        $tpl->setContent($a_form->getHTML());
    }

    protected function setBadgeFormValues(
        ilPropertyFormGUI $a_form,
        ilBadge $a_badge,
        ilBadgeType $a_type
    ): void {
        $a_form->getItemByPostVar('act')->setChecked($a_badge->isActive());
        $a_form->getItemByPostVar('title')->setValue($a_badge->getTitle());
        $a_form->getItemByPostVar('desc')->setValue($a_badge->getDescription());
        $a_form->getItemByPostVar('crit')->setValue($a_badge->getCriteria());
        $a_form->getItemByPostVar('img')->setValue($a_badge->getImage());
        $a_form->getItemByPostVar('img')->setImage($a_badge->getImagePath());

        $image_src = $this->badge_image_service->getImageFromBadge($a_badge);
        if ($image_src !== '') {
            $a_form->getItemByPostVar('img')->setImage($image_src);
        }

        $a_form->getItemByPostVar('valid')->setValue($a_badge->getValid());

        $custom = $a_type->getConfigGUIInstance();
        if ($custom instanceof ilBadgeTypeGUI) {
            $custom->importConfigToForm($a_form, $a_badge->getConfiguration());
        }
    }

    /**
     * @throws ilCtrlException
     * @throws IllegalStateException
     */
    protected function updateBadge(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $badge_id = $this->request->getBadgeId();
        if (!$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        $ilCtrl->setParameter($this, 'bid', $badge_id);

        $badge = new ilBadge($badge_id);
        $type = $badge->getTypeInstance();
        $custom = $type->getConfigGUIInstance();
        if ($custom &&
            !($custom instanceof ilBadgeTypeGUI)) {
            $custom = null;
        }
        $form = $this->initBadgeForm('update', $type, $badge->getTypeId());
        if ($form->checkInput() &&
            (!$custom || $custom->validateForm($form))) {
            $badge->setActive($form->getInput('act'));
            $badge->setTitle($form->getInput('title'));
            $badge->setDescription($form->getInput('desc'));
            $badge->setCriteria($form->getInput('crit'));
            $badge->setValid($form->getInput('valid'));

            $image = $form->getInput('img');
            if (isset($image['name']) && $image['name'] !== '') {
                $this->removeResourceStorageImage($badge);
                $this->badge_image_service->processImageUpload($badge);
            }

            if ($custom) {
                $badge->setConfiguration($custom->getConfigFromForm($form));
            }
            $tmpl_id = $form->getInput('tmpl');
            if ($tmpl_id !== '') {
                $this->removeResourceStorageImage($badge);
                $tmpl = new ilBadgeImageTemplate($tmpl_id);
                $this->cloneBadgeTemplate($badge, new ResourceIdentification($tmpl->getImageRid()));
            }

            $badge->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this, 'listBadges');
        }

        $this->tpl->setOnScreenMessage('failure', $lng->txt('form_input_not_valid'));
        $form->setValuesByPost();
        $this->editBadge($form);
    }

    protected function confirmDeleteBadges(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $badge_ids = $this->request->getMultiActionBadgeIdsFromUrl();
        if ($badge_ids === ['ALL_OBJECTS']) {
            $badge_ids = [];
            foreach (ilBadge::getInstancesByParentId($this->parent_obj_id) as $badge) {
                $badge_ids[] = $badge->getId();
            }
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt('back'),
            $ilCtrl->getLinkTarget($this, 'listBadges')
        );

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt('badge_deletion_confirmation'));
        $confirmation_gui->setCancel($lng->txt('cancel'), 'listBadges');
        $confirmation_gui->setConfirm($lng->txt('delete'), 'deleteBadges');

        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge((int) $badge_id);
            $confirmation_gui->addItem(
                'id[]',
                (string) $badge_id,
                $badge->getTitle() .
                ' (' . count(ilBadgeAssignment::getInstancesByBadgeId($badge_id)) . ')'
            );
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }

    protected function deleteBadges(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $badge_ids = $this->request->getIds();

        if (count($badge_ids) > 0) {
            foreach ($badge_ids as $badge_id) {
                $badge = new ilBadge((int) $badge_id);
                $badge->delete();
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $lng->txt('badge_select_one'), true);
        }

        $ilCtrl->redirect($this, 'listBadges');
    }


    //
    // badges multi action
    //

    /**
     * @return int[]
     */
    protected function getBadgesFromMultiAction(): array
    {
        $ilCtrl = $this->ctrl;

        $badge_ids = $this->request->getIds();
        if (!$badge_ids ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        return $badge_ids;
    }

    protected function copyBadges(): void
    {
        $ilCtrl = $this->ctrl;

        $badge_ids = $this->getBadgesFromMultiAction();

        $clip_ids = $this->session_repo->getBadgeIds();
        $clip_ids = array_unique(
            array_merge($clip_ids, $badge_ids)
        );
        $this->session_repo->setBadgeIds(array_map(intval(...), $clip_ids));

        $ilCtrl->redirect($this, 'listBadges');
    }

    protected function clearClipboard(): void
    {
        $ilCtrl = $this->ctrl;

        $this->session_repo->clear();
        $ilCtrl->redirect($this, 'listBadges');
    }

    /**
     * @return ilBadge[]
     */
    protected function getValidBadgesFromClipboard(): array
    {
        $res = [];

        $valid_types = array_keys(ilBadgeHandler::getInstance()->getAvailableTypesForObjType($this->parent_obj_type));

        foreach ($this->session_repo->getBadgeIds() as $badge_id) {
            $badge = new ilBadge($badge_id);
            if (in_array($badge->getTypeId(), $valid_types, true)) {
                $res[] = $badge;
            }
        }

        return $res;
    }

    protected function pasteBadges(): void
    {
        $ilCtrl = $this->ctrl;

        $clip_ids = $this->session_repo->getBadgeIds();
        if (!$this->hasWrite() || count($clip_ids) === 0) {
            $ilCtrl->redirect($this, 'listBadges');
        }

        $copy_suffix = $this->lng->txt("copy_of_suffix");
        foreach ($this->getValidBadgesFromClipboard() as $badge) {
            $badge->copy($this->parent_obj_id, $copy_suffix);
        }

        $ilCtrl->redirect($this, 'listBadges');
    }

    protected function toggleBadges(bool $a_status): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $badge_ids = $this->request->getMultiActionBadgeIdsFromUrl();
        if (count($badge_ids) > 0) {
            foreach ($badge_ids as $badge_id) {
                if ($badge_id === self::TABLE_ALL_OBJECTS_ACTION) {
                    foreach (ilBadge::getInstancesByParentId($this->parent_obj_id) as $badge) {
                        $badge = new ilBadge($badge->getId());
                        $badge->setActive($a_status);
                        $badge->update();
                    }
                } else {
                    $badge = new ilBadge((int) $badge_id);
                    $badge->setActive($a_status);
                    $badge->update();
                }
                $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $lng->txt('badge_select_one'), true);
        }

        $ilCtrl->redirect($this, 'listBadges');
    }

    protected function activateBadges(): void
    {
        $this->toggleBadges(true);
    }

    protected function deactivateBadges(): void
    {
        $this->toggleBadges(false);
    }


    //
    // users
    //

    protected function listUsers(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;

        $this->setTabs('users');

        if ($this->hasWrite()) {
            $manual = ilBadgeHandler::getInstance()->getAvailableManualBadges(
                $this->parent_obj_id,
                $this->parent_obj_type
            );
            if (count($manual)) {
                $drop = new ilSelectInputGUI($lng->txt('badge_badge'), 'bid');
                $drop->setOptions($manual);
                $ilToolbar->addInputItem($drop, true);

                $ilToolbar->setFormAction($ilCtrl->getFormAction($this, 'selectBadgeForAwardingOrRevoking'));
                $ilToolbar->addFormButton($lng->txt('badge_award_badge'), 'selectBadgeForAwardingOrRevoking');
            }
        }

        $tbl = new ilBadgeUserTableGUI($this->parent_ref_id);
        $tbl->renderTable();
    }

    private function selectBadgeForAwardingOrRevoking(): never
    {
        $this->ctrl->setParameter(
            $this,
            'bid',
            $this->http->wrapper()->post()->retrieve('bid', $this->refinery->kindlyTo()->int())
        );
        $this->ctrl->redirect($this, 'awardBadgeUserSelection');
    }

    protected function awardBadgeUserSelection(): void
    {
        $badge_ids = $this->request->getMultiActionBadgeIdsFromUrl();
        $bid = null;

        if ($badge_ids === []) {
            if ($this->http->wrapper()->post()->has('bid')) {
                $bid = $this->http->wrapper()->post()->retrieve('bid', $this->refinery->kindlyTo()->int());
            } elseif ($this->http->wrapper()->query()->has('bid')) {
                $bid = $this->http->wrapper()->query()->retrieve('bid', $this->refinery->kindlyTo()->int());
            }
        } elseif (count($badge_ids) === 1) {
            $bid = (int) $badge_ids[0];
        }

        if (!$bid ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, 'listUsers');
        }

        $manual = array_keys(
            ilBadgeHandler::getInstance()->getAvailableManualBadges($this->parent_obj_id, $this->parent_obj_type)
        );

        if (!in_array($bid, $manual, true)) {
            $this->ctrl->redirect($this, 'listUsers');
        }

        $back_target = 'listUsers';
        if ($this->request->getTgt() === 'bdgl') {
            $this->ctrl->saveParameter($this, 'tgt');
            $back_target = 'listBadges';
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, $back_target)
        );

        $this->ctrl->setParameter($this, 'bid', $bid);

        $badge = new ilBadge($bid);

        $tbl = new ilBadgeUserTableGUI($this->parent_ref_id, $badge);
        $tbl->renderTable();
    }

    protected function assignBadge(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $splittable_user_ids = $this->request->getBadgeAssignableUsers();
        [$user_ids, $badge_id] = $this->splitBadgeAndUserIdsFromString($splittable_user_ids);

        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listUsers');
        }

        $new_badges = [];
        foreach ($user_ids as $user_id) {
            if (!ilBadgeAssignment::exists($badge_id, $user_id)) {
                $ass = new ilBadgeAssignment($badge_id, $user_id);
                $ass->setAwardedBy($ilUser->getId());
                $ass->store();

                $new_badges[$user_id][] = $badge_id;
            }
        }

        ilBadgeHandler::getInstance()->sendNotification($new_badges, $this->parent_ref_id);

        $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
        $ilCtrl->redirect($this, 'listUsers');
    }

    protected function confirmDeassignBadge(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $splittable_user_ids = $this->request->getMultiActionBadgeIdsFromUrl();
        [$user_ids, $badge_id] = $this->splitBadgeAndUserIdsFromString($splittable_user_ids);

        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listUsers');
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt('back'),
            $ilCtrl->getLinkTarget($this, 'listUsers')
        );

        $badge = new ilBadge($badge_id);

        $ilCtrl->setParameter($this, 'bid', $badge->getId());

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText(
            sprintf($lng->txt('badge_assignment_deletion_confirmation'), $badge->getTitle())
        );
        $confirmation_gui->setCancel($lng->txt('cancel'), 'listUsers');
        $confirmation_gui->setConfirm($lng->txt('badge_remove_badge'), 'deassignBadge');

        $assigned_users = ilBadgeAssignment::getAssignedUsers($badge->getId());

        foreach ($user_ids as $user_id) {
            if (in_array($user_id, $assigned_users, true)) {
                $confirmation_gui->addItem(
                    "id[$user_id]",
                    (string) $badge_id,
                    ilUserUtil::getNamePresentation($user_id, false, false, '', true)
                );
            }
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }

    protected function deassignBadge(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $post_values = $this->request->getIds();
        $user_ids = [];
        $badge_id = null;
        foreach ($post_values as $usr_id => $found_badge_id) {
            $badge_id = $found_badge_id;
            $user_ids[] = $usr_id;
        }

        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, 'listUsers');
        }

        foreach ($user_ids as $user_id) {
            $ass = new ilBadgeAssignment((int) $badge_id, (int) $user_id);
            $ass->delete();
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
        $ilCtrl->redirect($this, 'listUsers');
    }

    /**
     * @throws Exception
     */
    protected function cloneBadgeTemplate(ilBadge $badge, ?ResourceIdentification $rid): void
    {
        if ($rid !== null) {
            $new_rid = $this->badge_image_service->cloneBadgeImageByRid($rid);
            $badge->setImageRid($new_rid);
            $badge->update();
        }
    }

    protected function removeResourceStorageImage(ilBadge $badge): void
    {
        if ($badge->getImageRid() !== '') {
            $this->resource_storage->manage()->remove(
                new ResourceIdentification($badge->getImageRid()),
                new ilBadgeFileStakeholder()
            );
        }
    }
}
