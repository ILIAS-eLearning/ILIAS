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

use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\EmployeeTalk\TalkSeries\Repository\IliasDBEmployeeTalkSeriesRepository;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\EmployeeTalk\Metadata\MetadataHandlerInterface;
use ILIAS\EmployeeTalk\Metadata\EditFormInterface;
use ILIAS\EmployeeTalk\Metadata\MetadataHandler;
use ILIAS\EmployeeTalk\Notification\NotificationHandlerInterface;
use ILIAS\EmployeeTalk\Notification\NotificationHandler;
use ILIAS\EmployeeTalk\Notification\Calendar\VCalendarGenerator;
use ILIAS\EmployeeTalk\Notification\NotificationType;

/**
 * Class ilObjEmployeeTalkGUI
 *
 * @ilCtrl_IsCalledBy ilObjEmployeeTalkGUI: ilEmployeeTalkMyStaffListGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkGUI: ilPropertyFormGUI
 */
final class ilObjEmployeeTalkGUI extends ilObjectGUI
{
    protected HttpServices $http;
    protected Refinery $refinery;
    protected UIFactory $ui_factory;
    protected ilPropertyFormGUI $form;
    protected bool $isReadonly;
    protected ilObjEmployeeTalkAccess $talkAccess;
    protected IliasDBEmployeeTalkSeriesRepository $repository;
    protected MetadataHandlerInterface $md_handler;
    protected NotificationHandlerInterface $notif_handler;
    private string $link_to_parent;

    public function __construct()
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $refId = $this->http->wrapper()->query()->retrieve(
            "ref_id",
            $this->refinery->kindlyTo()->int()
        );
        parent::__construct([], $refId, true, false);

        $DIC->language()->loadLanguageModule('mst');
        $DIC->language()->loadLanguageModule('trac');
        $DIC->language()->loadLanguageModule('etal');
        $DIC->language()->loadLanguageModule('dateplaner');
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();

        $this->type = 'etal';

        $this->omitLocator();
        $DIC->ui()->mainTemplate()->setTitle($this->lng->txt('mst_my_staff'));
        $this->talkAccess = ilObjEmployeeTalkAccess::getInstance();
        $this->repository = new IliasDBEmployeeTalkSeriesRepository($this->user, $DIC->database());
        $this->md_handler = new MetadataHandler();
        $this->notif_handler = new NotificationHandler(new VCalendarGenerator($DIC->language()));
    }

    private function checkAccessOrFail(): void
    {
        if (!$this->talkAccess->canRead($this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    public function setLinkToParentGUI(string $link): void
    {
        $this->link_to_parent = $link;
    }

    public function redirectToParentGUI(): void
    {
        if (isset($this->link_to_parent)) {
            $this->ctrl->redirectToURL($this->link_to_parent);
        }
        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class));
    }

    public function executeCommand(): void
    {
        $this->checkAccessOrFail();
        $this->isReadonly = !$this->talkAccess->canEdit($this->object->getRefId());

        // determine next class in the call structure
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilpermissiongui':
                parent::prepareOutput();
                $this->tabs_gui->activateTab('perm_settings');
                $ilPermissionGUI = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($ilPermissionGUI);
                break;
            case 'ilinfoscreengui':
                parent::prepareOutput();
                $this->tabs_gui->activateTab('info_short');
                $ilInfoScreenGUI = new ilInfoScreenGUI($this);
                $this->ctrl->forwardCommand($ilInfoScreenGUI);
                break;
            case strtolower(ilRepositorySearchGUI::class):
                $repo = new ilRepositorySearchGUI();
                //TODO: Add user filter
                $repo->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_IGNORE_USER_SETTING);
                //$repo->addUserAccessFilterCallable(function () {
                //    $orgUnitUser = ilOrgUnitUser::getInstanceById($this->container->user()->getId());
                //    $orgUnitUser->addPositions()                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ;
                //});
                $this->ctrl->forwardCommand($repo);
                break;
            case strtolower(ilEmployeeTalkAppointmentGUI::class):
                $appointmentGUI = new ilEmployeeTalkAppointmentGUI(
                    $this->tpl,
                    $this->lng,
                    $this->ctrl,
                    $this->http,
                    $this->refinery,
                    $this->tabs_gui,
                    $this->notif_handler,
                    $this->object
                );
                $this->ctrl->forwardCommand($appointmentGUI);
                break;
            default:
                parent::executeCommand();
        }
    }

    public function editObject(): void
    {
        $this->tabs_gui->activateTab('settings');

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values);
        }

        $this->addChangeDateButtonsToToolbar();
        $this->tpl->setContent($form->getHTML());
    }

    protected function validateCustom(ilPropertyFormGUI $form): bool
    {
        $refId = $this->object->getRefId();
        $settings = $this->repository->readEmployeeTalkSerieSettings($this->object->getId());
        $oldLockSettings = $settings->isLockedEditing();
        $lockEdititngForOthers = boolval(
            intval($form->getInput('etal_settings_locked_for_others'))
        );
        if ($oldLockSettings === $lockEdititngForOthers) {
            return true;
        }

        return $this->talkAccess->canEditTalkLockStatus($refId);
    }

    public function updateObject(): void
    {
        $form = $this->initEditForm();
        $this->addExternalEditFormCustom($form);
        if ($form->checkInput() &&
            $this->validateCustom($form) &&
            !$this->isReadonly) {
            $this->object->setTitle($form->getInput("title"));
            $this->object->setDescription($form->getInput("desc"));
            $this->updateCustom($form);
            $this->object->update();

            $this->afterUpdate();
            return;
        }

        // display form again to correct errors
        $this->tabs_gui->activateTab("settings");
        $this->addChangeDateButtonsToToolbar();
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    public function confirmedDeleteObject(): void
    {
        if (!$this->talkAccess->canDelete($this->ref_id)) {
            ilSession::clear("saved_post");
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->redirectToParentGUI();

            return;
        }

        if ($this->post_wrapper->has("interruptive_items")) {
            $ref_id = $this->post_wrapper->retrieve(
                "interruptive_items",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            $saved_post = array_unique(array_merge(ilSession::get('saved_post') ?? [], $ref_id));
            ilSession::set('saved_post', $saved_post);
        }

        $ru = new ilRepositoryTrashGUI($this);
        $ref_ids = ilSession::get("saved_post");
        $talks = [];

        foreach ($ref_ids as $refId) {
            $talks[] = new ilObjEmployeeTalk(intval($refId), true);
        }

        $this->sendNotification(...$talks);

        $ru->deleteObjects($this->requested_ref_id, $ref_ids);
        $trashEnabled = boolval($this->settings->get('enable_trash'));

        if ($trashEnabled) {
            ilRepUtil::removeObjectsFromSystem($ref_ids);
        }

        ilSession::clear("saved_post");

        $this->redirectToParentGUI();
    }

    private function sendNotification(ilObjEmployeeTalk ...$talks): void
    {
        $this->notif_handler->send(NotificationType::CANCELLATION, ...$talks);
    }

    private function sendUpdateNotification(ilObjEmployeeTalk ...$talks): void
    {
        $this->notif_handler->send(NotificationType::UPDATE, ...$talks);
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;

        /**
         * @var EmployeeTalk $data
         */
        $data = $this->object->getData();

        $lng->loadLanguageModule('etal');
        $lng->loadLanguageModule('orgu');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));

        $form->setTitle($this->lng->txt('talk_serial'));

        $generalSection = new ilFormSectionHeaderGUI();
        $generalSection->setTitle($this->lng->txt($this->object->getType() . "_edit"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setInfo($this->lng->txt('will_update_series_info_title'));
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $ti->setDisabled($this->isReadonly);
        $form->addItem($ti);

        $superior = new ilTextInputGUI($this->lng->txt("superior"), "etal_superior");
        $superior->setDisabled(true);
        $form->addItem($superior);

        $login = new ilTextInputGUI($this->lng->txt("employee"), "etal_employee");
        $login->setDisabled(true);
        $form->addItem($login);

        $writeLockForOthers = new ilCheckboxInputGUI($this->lng->txt("lock_edititng_for_others"), "etal_settings_locked_for_others");
        $writeLockForOthers->setInfo($this->lng->txt('will_update_series_info_lock'));
        $writeLockForOthers->setDisabled(
            $this->isReadonly ||
            !$this->talkAccess->canEditTalkLockStatus($this->object->getRefId())
        );
        $form->addItem($writeLockForOthers);

        $form->addItem($generalSection);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $ta->setDisabled($this->isReadonly);
        $form->addItem($ta);

        $location = new ilTextInputGUI($this->lng->txt('location'), 'etal_location');
        $location->setMaxLength(200);
        $location->setDisabled($this->isReadonly);
        $form->addItem($location);

        $completed = new ilCheckboxInputGUI($this->lng->txt('etal_status_completed'), 'etal_completed');
        $completed->setDisabled($this->isReadonly);
        $form->addItem($completed);

        $this->initEditCustomForm($form);

        if (!$this->isReadonly) {
            $form->addCommandButton("update", $this->lng->txt("save"));
        }

        return $form;
    }

    public function addChangeDateButtonsToToolbar(): void
    {
        if ($this->isReadonly) {
            return;
        }
        $appointment_class = strtolower(ilEmployeeTalkAppointmentGUI::class);
        $this->ctrl->setParameterByClass($appointment_class, 'ref_id', $this->ref_id);

        $this->ctrl->setParameterByClass(
            $appointment_class,
            ilEmployeeTalkAppointmentGUI::EDIT_MODE,
            ilEmployeeTalkAppointmentGUI::EDIT_MODE_APPOINTMENT
        );
        $link_single = $this->ctrl->getLinkTargetByClass(
            $appointment_class,
            ControlFlowCommand::UPDATE_INDEX
        );
        $button_single = $this->ui_factory->button()->standard(
            $this->lng->txt('change_date_of_talk'),
            $link_single
        );

        $this->ctrl->setParameterByClass(
            $appointment_class,
            ilEmployeeTalkAppointmentGUI::EDIT_MODE,
            ilEmployeeTalkAppointmentGUI::EDIT_MODE_SERIES
        );
        $link_all = $this->ctrl->getLinkTargetByClass(
            $appointment_class,
            ControlFlowCommand::UPDATE_INDEX
        );
        $button_all = $this->ui_factory->button()->standard(
            $this->lng->txt('change_date_of_series'),
            $link_all
        );

        $this->ctrl->clearParametersByClass($appointment_class);

        $this->toolbar->addComponent($button_single);
        $this->toolbar->addComponent($button_all);
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        /**
         * @var EmployeeTalk $data
         */
        $data = $this->object->getData();
        $parent = $this->object->getParent();
        $settings = $this->repository->readEmployeeTalkSerieSettings(intval($parent->getId()));

        $a_values['etal_superior'] = ilObjUser::_lookupLogin($this->object->getOwner());
        $a_values['etal_employee'] = ilObjUser::_lookupLogin($data->getEmployee());
        $a_values['etal_settings_locked_for_others'] = $settings->isLockedEditing();
        $a_values['etal_location'] = $data->getLocation();
        $a_values['etal_completed'] = $data->isCompleted();
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        /**
         * @var ilObjEmployeeTalkSeries $series
         */
        $series = $this->object->getParent();
        $updated_series = false;

        $location = $form->getInput('etal_location');
        $completed = boolval(
            intval($form->getInput('etal_completed'))
        );
        $lockEdititngForOthers = boolval(
            intval($form->getInput('etal_settings_locked_for_others'))
        );

        $settings = $this->repository->readEmployeeTalkSerieSettings($series->getId());
        if ($lockEdititngForOthers !== $settings->isLockedEditing()) {
            $settings->setLockedEditing($lockEdititngForOthers);
            $this->repository->storeEmployeeTalkSerieSettings($settings);
            $updated_series = true;
        }


        /**
         * @var EmployeeTalk $data
         */
        $data = $this->object->getData();
        $data->setCompleted($completed);
        $data->setLocation($location ?? '');
        $this->object->setData($data);

        /**
         * @var ilObjEmployeeTalkSeries $parent
         */
        $parent = $this->object->getParent();
        /**
         * @var ilObjEmployeeTalk[] $talks
         */
        $subTree = $parent->getSubItems()['_all'];


        $talks = [];
        $talks[] = $this->object;
        // Update the title of the talk series
        if ($parent->getTitle() !== $this->object->getTitle()) {
            $parent->setTitle($this->object->getTitle());
            $parent->update();
        }
        // Update the title of every talk which belongs to the talk series
        foreach ($subTree as $treeNode) {
            if (boolval($treeNode['deleted']) === true) {
                continue;
            }
            $talk = new ilObjEmployeeTalk(intval($treeNode['ref_id']));
            if ($talk->getId() === $this->object->getId()) {
                continue;
            }
            if ($talk->getTitle() !== $this->object->getTitle()) {
                $talk->setTitle($this->object->getTitle());
                $talk->update();
                $talks[] = $talk;
            } elseif ($updated_series) {
                $talks[] = $talk;
            }
        }

        parent::updateCustom($form);

        $this->sendUpdateNotification(...$talks);
    }

    public function viewObject(): void
    {
        $this->tabs_gui->activateTab('view_content');
        $form = $this->getMetadataForm();
        $this->tpl->setContent($form->render());
    }

    public function updateMetadataObject(): void
    {
        /**
         * @var ilObjEmployeeTalk $talk_object
         */
        $talk_object = $this->object;
        $series = $talk_object->getParent();

        $form = $this->getMetadataForm();

        if ($form->importFromPostAndValidate()) {
            $form->updateMetadata();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            $this->sendUpdateNotification($talk_object);
            $this->ctrl->redirect($this, ControlFlowCommand::INDEX);
        }

        $this->tabs_gui->activateTab('view_content');
        $this->tpl->setContent($form->render());
    }

    protected function getTabs(): void
    {
        $this->tabs_gui->addTab(
            'view_content',
            $this->lng->txt("content"),
            $this->ctrl->getLinkTarget($this, ControlFlowCommand::INDEX)
        );
        $this->tabs_gui->addTab(
            "info_short",
            "Info",
            $this->ctrl->getLinkTargetByClass(strtolower(ilInfoScreenGUI::class), "showSummary")
        );
        $this->tabs_gui->addTab(
            'settings',
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, ControlFlowCommand::UPDATE)
        );
    }

    /**
     * @param ilTabsGUI $tabs_gui
     */
    public function getAdminTabs(): void
    {
        $this->getTabs();

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        get_class($this),
                        'ilpermissiongui'
                    ],
                    'perm'
                )
            );
        }
    }

    protected function getMetadataForm(): EditFormInterface
    {
        /**
         * @var ilObjEmployeeTalkSeries $series
         */
        $series = $this->object->getParent();

        if ($this->isReadonly) {
            return $this->md_handler->getDisabledEditForm(
                $series->getType(),
                $series->getId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }

        return $this->md_handler->getEditForm(
            $series->getType(),
            $series->getId(),
            $this->object->getType(),
            $this->object->getId(),
            $this->ctrl->getFormAction($this, 'updateMetadata'),
            'updateMetadata',
            $this->lng->txt('save')
        );
    }

    public static function _goto(string $refId): void
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];
        if (!ilObject::_exists((int) $refId, true)) {
            $container["tpl"]->setOnScreenMessage(
                'failure',
                $container->language()->txt("permission_denied"),
                true
            );
            $container->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
        $container->ctrl()->setParameterByClass(strtolower(self::class), 'ref_id', $refId);
        $container->ctrl()->redirectByClass([
            strtolower(ilDashboardGUI::class),
            strtolower(ilMyStaffGUI::class),
            strtolower(ilEmployeeTalkMyStaffListGUI::class),
            strtolower(self::class),
        ], ControlFlowCommand::INDEX);
    }
}
