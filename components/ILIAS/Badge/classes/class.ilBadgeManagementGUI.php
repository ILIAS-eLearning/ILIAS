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
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\Badge\ilBadgeTableGUI;
use ILIAS\Badge\ilBadgeUserTableGUI;
use ILIAS\Refinery\Factory;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @ilCtrl_Calls ilBadgeManagementGUI: ilPropertyFormGUI
 */
class ilBadgeManagementGUI implements ilCtrlSecurityInterface
{
    private const string LIST_USERS_ACTION = 'listUsers';
    private const string LIST_BADGES_ACTION = 'listBadges';
    private const string DELETE_BADGES_ACTION = 'deleteBadges';
    private const string DEASSIGN_BADGE_ACTION = 'deassignBadge';
    private const string SAVE_BADGE_ACTION = 'saveBadge';
    private const string UPDATE_BADGE_ACTION = 'updateBadge';
    private const string ADD_BADGE_ACTION = 'addBadge';
    private const string PASTE_BADGES_ACTION = 'pasteBadges';
    private const string CLEAR_CLIPBOARD_ACTION = 'clearClipboard';
    private const string SELECT_BADGE_FOR_AWARD_REVOKE_ACTION = 'selectBadgeForAwardingOrRevoking';
    private const string AWARD_BADGE_USER_SELECTION_ACTION = 'awardBadgeUserSelection';
    private const string DEFAULT_ACTION = self::LIST_BADGES_ACTION;
    private const string TABLE_ACTIONS = 'handleTableActions';
    public const string TABLE_ALL_OBJECTS_ACTION = 'ALL_OBJECTS';

    private ilBadgeGUIRequest $request;
    private ilBadgeManagementSessionRepository $session_repo;
    private ilLanguage $lng;
    private ilCtrlInterface $ctrl;
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
            if ($splittable_user_ids === [self::TABLE_ALL_OBJECTS_ACTION]) {
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
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::DEFAULT_ACTION);
        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd . 'Cmd')) {
            $cmd = self::DEFAULT_ACTION;
        }
        $cmd .= 'Cmd';

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
                    $this->ctrl->setParameter($this, 'type', $type_id);
                    $handler = ilBadgeHandler::getInstance();
                    $type = $handler->getTypeInstanceByUniqueId($type_id);
                    $form = $this->initBadgeForm('create', $type, $type_id);
                }
                $this->ctrl->forwardCommand($form);
                break;

            default:
                $badge_ids = $this->request->getMultiActionBadgeIdsFromUrl();
                if (count($badge_ids) === 1) {
                    $badge_id = array_pop($badge_ids);
                    $this->ctrl->setParameter($this, 'tid', $badge_id);
                }

                $this->$cmd();
                break;
        }
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    private function getTableAction(): ?string
    {
        return $this->http->wrapper()->query()->retrieve(
            'tid_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
    }

    private function handleTableActionsCmd(): void
    {
        match ($this->getTableAction()) {
            'badge_table_activate' => $this->activateBadges(),
            'badge_table_deactivate' => $this->deactivateBadges(),
            'badge_table_edit' => $this->editBadgeCmd(),
            'badge_table_delete' => $this->confirmDeleteBadges(),
            'award_revoke_badge' => $this->awardBadgeUserSelectionCmd(),
            'revokeBadge' => $this->confirmDeassignBadge(),
            'assignBadge' => $this->assignBadge(),
            default => $this->ctrl->redirect($this, self::DEFAULT_ACTION),
        };
    }

    public function getUnsafeGetCommands(): array
    {
        return [self::TABLE_ACTIONS];
    }

    private function setTabs(string $a_active): void
    {
        $this->tabs->addSubTab(
            'badges',
            $this->lng->txt('obj_bdga'),
            $this->ctrl->getLinkTarget($this, self::LIST_BADGES_ACTION)
        );

        $this->tabs->addSubTab(
            'users',
            $this->lng->txt('users'),
            $this->ctrl->getLinkTarget($this, self::LIST_USERS_ACTION)
        );

        $this->tabs->activateSubTab($a_active);
    }

    private function hasWrite(): bool
    {
        return $this->access->checkAccess('write', '', $this->parent_ref_id);
    }

    private function listBadgesCmd(): void
    {
        $this->setTabs('badges');

        if ($this->hasWrite()) {
            $handler = ilBadgeHandler::getInstance();
            $valid_types = $handler->getAvailableTypesForObjType($this->parent_obj_type);
            if ($valid_types) {
                $options = [];
                foreach ($valid_types as $id => $type) {
                    $this->ctrl->setParameter($this, 'type', $id);
                    $options[$id] = $this->ui_factory->link()->standard(
                        $this->parent_obj_type !== 'bdga' ? ilBadge::getExtendedTypeCaption($type) : $type->getCaption(
                        ),
                        $this->ctrl->getLinkTarget($this, self::ADD_BADGE_ACTION)
                    );
                    $this->ctrl->setParameter($this, 'type', null);
                }
                asort($options);
                $options = array_values($options);

                $this->toolbar->addComponent(
                    $this->ui_factory->dropdown()->standard($options)->withLabel($this->lng->txt('badge_create'))
                );
            } else {
                $this->tpl->setOnScreenMessage(
                    $this->tpl::MESSAGE_TYPE_INFO,
                    $this->lng->txt('badge_no_valid_types_for_obj')
                );
            }

            $clip_ids = $this->session_repo->getBadgeIds();
            if (count($clip_ids) > 0) {
                if ($valid_types) {
                    $this->toolbar->addSeparator();
                }

                $tt = [];
                foreach ($this->getValidBadgesFromClipboard() as $badge) {
                    $tt[] = $badge->getTitle();
                }
                $ttid = 'bdgpst';

                $this->lng->loadLanguageModule('content');
                $this->toolbar->addButton(
                    $this->lng->txt('cont_paste_from_clipboard') .
                    ' (' . count($tt) . ')',
                    $this->ctrl->getLinkTarget($this, self::PASTE_BADGES_ACTION),
                    '',
                    null,
                    '',
                    $ttid
                );
                $this->toolbar->addButton(
                    $this->lng->txt('clear_clipboard'),
                    $this->ctrl->getLinkTarget($this, self::CLEAR_CLIPBOARD_ACTION)
                );
            }
        }

        $table = new ilBadgeTableGUI($this->parent_obj_id, $this->parent_obj_type, $this->hasWrite());
        $table->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    //
    // badge (CRUD)
    //

    private function addBadgeCmd(?ilPropertyFormGUI $a_form = null): void
    {
        $type_id = $this->request->getType();
        if (!$type_id ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $this->ctrl->setParameter($this, 'type', $type_id);

        $handler = ilBadgeHandler::getInstance();
        $type = $handler->getTypeInstanceByUniqueId($type_id);
        if (!$type) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        if (!$a_form) {
            $a_form = $this->initBadgeForm('create', $type, $type_id);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    private function initBadgeForm(
        string $a_mode,
        ilBadgeType $a_type,
        string $a_type_unique_id
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::SAVE_BADGE_ACTION));
        $form->setTitle($this->lng->txt('badge_badge') . ' "' . $a_type->getCaption() . '"');

        $active = new ilCheckboxInputGUI($this->lng->txt('active'), 'act');
        $form->addItem($active);

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setMaxLength(255);
        $title->setRequired(true);
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $desc->setMaxNumOfChars(4000);
        $desc->setRequired(true);
        $form->addItem($desc);

        $crit = new ilTextAreaInputGUI($this->lng->txt('badge_criteria'), 'crit');
        $crit->setMaxNumOfChars(4000);
        $crit->setRequired(true);
        $form->addItem($crit);

        if ($a_mode === 'create') {
            // upload

            $img_mode = new ilRadioGroupInputGUI($this->lng->txt('image'), 'img_mode');
            $img_mode->setRequired(true);
            $img_mode->setValue('tmpl');
            $form->addItem($img_mode);

            $img_mode_tmpl = new ilRadioOption($this->lng->txt('badge_image_from_template'), 'tmpl');
            $img_mode->addOption($img_mode_tmpl);

            $img_mode_up = new ilRadioOption($this->lng->txt('badge_image_from_upload'), 'up');
            $img_mode->addOption($img_mode_up);

            $img_upload = new ilImageFileInputGUI($this->lng->txt('file'), 'img');
            $img_upload->setRequired(true);
            $img_upload->setSuffixes(['png', 'svg']);
            $img_mode_up->addSubItem($img_upload);

            // templates

            $valid_templates = ilBadgeImageTemplate::getInstancesByType($a_type_unique_id);
            if (count($valid_templates)) {
                $options = [];
                $options[''] = $this->lng->txt('please_select');
                foreach ($valid_templates as $tmpl) {
                    $options[$tmpl->getId()] = $tmpl->getTitle();
                }

                $tmpl = new ilSelectInputGUI($this->lng->txt('badge_image_template_form'), 'tmpl');
                $tmpl->setRequired(true);
                $tmpl->setOptions($options);
                $img_mode_tmpl->addSubItem($tmpl);
            } else {
                // no templates, activate upload
                $img_mode_tmpl->setDisabled(true);
                $img_mode->setValue('up');
            }
        } else {
            $img_upload = new ilImageFileInputGUI($this->lng->txt('image'), 'img');
            $img_upload->setSuffixes(['png', 'svg']);
            $img_upload->setAllowDeletion(false);
            $img_upload->setUseCache(false);
            $form->addItem($img_upload);
        }

        $valid = new ilTextInputGUI($this->lng->txt('badge_valid'), 'valid');
        $valid->setMaxLength(255);
        $form->addItem($valid);

        $custom = $a_type->getConfigGUIInstance();
        if ($custom instanceof ilBadgeTypeGUI) {
            $custom->initConfigForm($form, $this->parent_ref_id);
        }

        // :TODO: valid date/period

        if ($a_mode === 'create') {
            $form->addCommandButton(self::SAVE_BADGE_ACTION, $this->lng->txt('save'));
        } else {
            $form->addCommandButton(self::UPDATE_BADGE_ACTION, $this->lng->txt('save'));
        }
        $form->addCommandButton(self::LIST_BADGES_ACTION, $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * @throws ilCtrlException
     * @throws IllegalStateException
     */
    private function saveBadgeCmd(): void
    {
        $type_id = $this->request->getType();
        if (!$type_id ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $this->ctrl->setParameter($this, 'type', $type_id);

        $handler = ilBadgeHandler::getInstance();
        $type = $handler->getTypeInstanceByUniqueId($type_id);
        if (!$type) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
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

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $form->setValuesByPost();
        $this->addBadgeCmd($form);
    }

    private function editBadgeCmd(?ilPropertyFormGUI $a_form = null): void
    {
        $badge_id = $this->request->getBadgeIdFromUrl();
        if (!$badge_id) {
            $badge_id = $this->request->getBadgeId();
        }

        if (!$badge_id ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $this->ctrl->setParameter($this, 'bid', $badge_id);

        $badge = new ilBadge($badge_id);

        $static_cnt = ilBadgeHandler::getInstance()->countStaticBadgeInstances($badge);
        if ($static_cnt) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_INFO,
                sprintf($this->lng->txt('badge_edit_with_published'), $static_cnt)
            );
        }

        if (!$a_form) {
            $type = $badge->getTypeInstance();
            $a_form = $this->initBadgeForm('edit', $type, $badge->getTypeId());
            $this->setBadgeFormValues($a_form, $badge, $type);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    private function setBadgeFormValues(
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
    private function updateBadgeCmd(): void
    {
        $badge_id = $this->request->getBadgeId();
        if (!$badge_id ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $this->ctrl->setParameter($this, 'bid', $badge_id);

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
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('form_input_not_valid'));
        $form->setValuesByPost();
        $this->editBadgeCmd($form);
    }

    private function confirmDeleteBadges(): void
    {
        $badge_ids = $this->request->getMultiActionBadgeIdsFromUrl();
        if ($badge_ids === [self::TABLE_ALL_OBJECTS_ACTION]) {
            $badge_ids = [];
            foreach (ilBadge::getInstancesByParentId($this->parent_obj_id) as $badge) {
                $badge_ids[] = $badge->getId();
            }
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::LIST_BADGES_ACTION)
        );

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('badge_deletion_confirmation'));
        $confirmation_gui->setCancel($this->lng->txt('cancel'), self::LIST_BADGES_ACTION);
        $confirmation_gui->setConfirm($this->lng->txt('delete'), self::DELETE_BADGES_ACTION);

        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge((int) $badge_id);
            $confirmation_gui->addItem(
                'id[]',
                (string) $badge_id,
                $badge->getTitle() .
                ' (' . count(ilBadgeAssignment::getInstancesByBadgeId($badge_id)) . ')'
            );
        }

        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    private function deleteBadgesCmd(): void
    {
        $badge_ids = $this->request->getIds();

        if (count($badge_ids) > 0) {
            foreach ($badge_ids as $badge_id) {
                $badge = new ilBadge((int) $badge_id);
                $badge->delete();
            }
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        } else {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('badge_select_one'), true);
        }

        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    //
    // badges multi action
    //

    /**
     * @return int[]
     */
    private function getBadgesFromMultiAction(): array
    {
        $badge_ids = $this->request->getIds();
        if (!$badge_ids ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        return $badge_ids;
    }

    private function copyBadgesCmd(): void
    {
        $badge_ids = $this->getBadgesFromMultiAction();

        $clip_ids = $this->session_repo->getBadgeIds();
        $clip_ids = array_unique(
            array_merge($clip_ids, $badge_ids)
        );
        $this->session_repo->setBadgeIds(array_map(intval(...), $clip_ids));

        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    private function clearClipboardCmd(): void
    {
        $this->session_repo->clear();
        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    /**
     * @return ilBadge[]
     */
    private function getValidBadgesFromClipboard(): array
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

    private function pasteBadgesCmd(): void
    {
        $clip_ids = $this->session_repo->getBadgeIds();
        if (!$this->hasWrite() || count($clip_ids) === 0) {
            $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
        }

        $copy_suffix = $this->lng->txt('copy_of_suffix');
        foreach ($this->getValidBadgesFromClipboard() as $badge) {
            $badge->copy($this->parent_obj_id, $copy_suffix);
        }

        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    private function toggleBadges(bool $a_status): void
    {
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
                $this->tpl->setOnScreenMessage(
                    $this->tpl::MESSAGE_TYPE_SUCCESS,
                    $this->lng->txt('settings_saved'),
                    true
                );
            }
        } else {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('badge_select_one'), true);
        }

        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    private function activateBadges(): void
    {
        $this->toggleBadges(true);
    }

    private function deactivateBadges(): void
    {
        $this->toggleBadges(false);
    }

    //
    // users
    //

    private function listUsersCmd(): void
    {
        $this->setTabs('users');

        if ($this->hasWrite()) {
            $manual = ilBadgeHandler::getInstance()->getAvailableManualBadges(
                $this->parent_obj_id,
                $this->parent_obj_type
            );
            if (count($manual)) {
                $drop = new ilSelectInputGUI($this->lng->txt('badge_badge'), 'bid');
                $drop->setOptions($manual);
                $this->toolbar->addInputItem($drop, true);

                $this->toolbar->setFormAction(
                    $this->ctrl->getFormAction($this, self::SELECT_BADGE_FOR_AWARD_REVOKE_ACTION)
                );
                $this->toolbar->addFormButton(
                    $this->lng->txt('badge_award_badge'),
                    self::SELECT_BADGE_FOR_AWARD_REVOKE_ACTION
                );
            }
        }

        $tbl = new ilBadgeUserTableGUI($this->parent_ref_id);
        $tbl->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    private function selectBadgeForAwardingOrRevokingCmd(): never
    {
        $this->ctrl->setParameter(
            $this,
            'bid',
            $this->http->wrapper()->post()->retrieve('bid', $this->refinery->kindlyTo()->int())
        );
        $this->ctrl->redirect($this, self::AWARD_BADGE_USER_SELECTION_ACTION);
    }

    private function awardBadgeUserSelectionCmd(): void
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
            $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
        }

        $manual = array_keys(
            ilBadgeHandler::getInstance()->getAvailableManualBadges($this->parent_obj_id, $this->parent_obj_type)
        );

        if (!in_array($bid, $manual, true)) {
            $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
        }

        $back_target = self::LIST_USERS_ACTION;
        if ($this->request->getTgt() === 'bdgl') {
            $this->ctrl->saveParameter($this, 'tgt');
            $back_target = self::LIST_BADGES_ACTION;
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, $back_target)
        );

        $this->ctrl->setParameter($this, 'bid', $bid);

        $badge = new ilBadge($bid);

        $tbl = new ilBadgeUserTableGUI($this->parent_ref_id, $badge);
        $tbl->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    private function assignBadge(): void
    {
        $splittable_user_ids = $this->request->getBadgeAssignableUsers();
        [$user_ids, $badge_id] = $this->splitBadgeAndUserIdsFromString($splittable_user_ids);

        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
        }

        $new_badges = [];
        foreach ($user_ids as $user_id) {
            if (!ilBadgeAssignment::exists($badge_id, $user_id)) {
                $ass = new ilBadgeAssignment($badge_id, $user_id);
                $ass->setAwardedBy($this->user->getId());
                $ass->store();

                $new_badges[$user_id][] = $badge_id;
            }
        }

        ilBadgeHandler::getInstance()->sendNotification($new_badges, $this->parent_ref_id);

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
    }

    private function confirmDeassignBadge(): void
    {
        $splittable_user_ids = $this->request->getMultiActionBadgeIdsFromUrl();
        [$user_ids, $badge_id] = $this->splitBadgeAndUserIdsFromString($splittable_user_ids);

        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::LIST_USERS_ACTION)
        );

        $badge = new ilBadge($badge_id);

        $this->ctrl->setParameter($this, 'bid', $badge->getId());

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this, self::DEASSIGN_BADGE_ACTION));
        $confirmation_gui->setHeaderText(
            sprintf($this->lng->txt('badge_assignment_deletion_confirmation'), $badge->getTitle())
        );
        $confirmation_gui->setCancel($this->lng->txt('cancel'), self::LIST_USERS_ACTION);
        $confirmation_gui->setConfirm($this->lng->txt('badge_remove_badge'), self::DEASSIGN_BADGE_ACTION);

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

        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    private function deassignBadgeCmd(): void
    {
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
            $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
        }

        foreach ($user_ids as $user_id) {
            $ass = new ilBadgeAssignment((int) $badge_id, (int) $user_id);
            $ass->delete();
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::LIST_USERS_ACTION);
    }

    /**
     * @throws Exception
     */
    private function cloneBadgeTemplate(ilBadge $badge, ?ResourceIdentification $rid): void
    {
        if ($rid !== null) {
            $new_rid = $this->badge_image_service->cloneBadgeImageByRid($rid);
            $badge->setImageRid($new_rid);
            $badge->update();
        }
    }

    private function removeResourceStorageImage(ilBadge $badge): void
    {
        if ($badge->getImageRid() !== '') {
            $this->resource_storage->manage()->remove(
                new ResourceIdentification($badge->getImageRid()),
                new ilBadgeFileStakeholder()
            );
        }
    }
}
