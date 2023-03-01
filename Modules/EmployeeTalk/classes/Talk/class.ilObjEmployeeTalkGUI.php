<?php

declare(strict_types=1);

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

use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\Modules\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\EmployeeTalk\Service\EmployeeTalkEmailNotificationService;
use ILIAS\EmployeeTalk\Service\VCalendarFactory;
use ILIAS\EmployeeTalk\Service\EmployeeTalkEmailNotification;
use ILIAS\Modules\EmployeeTalk\TalkSeries\Repository\IliasDBEmployeeTalkSeriesRepository;

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
    private \ILIAS\DI\Container $container;
    private ilPropertyFormGUI $form;
    private bool $isReadonly;
    private ilObjEmployeeTalkAccess $talkAccess;
    private IliasDBEmployeeTalkSeriesRepository $repository;

    public function __construct()
    {
        $this->container = $GLOBALS["DIC"];

        $refId = $this->container
            ->http()
            ->wrapper()
            ->query()
            ->retrieve("ref_id", $this->container->refinery()->kindlyTo()->int());
        parent::__construct([], $refId, true, false);

        $this->container->language()->loadLanguageModule('mst');
        $this->container->language()->loadLanguageModule('trac');
        $this->container->language()->loadLanguageModule('etal');
        $this->container->language()->loadLanguageModule('dateplaner');

        $this->type = 'etal';

        $this->setReturnLocation("save", strtolower(ilEmployeeTalkMyStaffListGUI::class));

        $this->omitLocator();
        $this->container->ui()->mainTemplate()->setTitle($this->container->language()->txt('mst_my_staff'));
        $this->talkAccess = ilObjEmployeeTalkAccess::getInstance();
        $this->repository = new IliasDBEmployeeTalkSeriesRepository($this->user, $this->container->database());
    }

    private function checkAccessOrFail(): void
    {
        if (!$this->talkAccess->canRead(intval($this->object->getRefId()))) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    public function executeCommand(): void
    {
        $this->checkAccessOrFail();
        $this->isReadonly = !$this->talkAccess->canEdit(intval($this->object->getRefId()));

        // determine next class in the call structure
        $next_class = $this->container->ctrl()->getNextClass($this);

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
                $this->container->ctrl()->forwardCommand($repo);
                break;
            case strtolower(ilEmployeeTalkAppointmentGUI::class):
                $appointmentGUI = new ilEmployeeTalkAppointmentGUI(
                    $this->tpl,
                    $this->lng,
                    $this->ctrl,
                    $this->container->http(),
                    $this->container->refinery(),
                    $this->container->tabs(),
                    $this->object
                );
                $this->container->ctrl()->forwardCommand($appointmentGUI);
                break;
            default:
                parent::executeCommand();
        }
    }

    public function editObject(): void
    {
        $this->tabs_gui->activateTab('view_content');

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values);
        }

        $this->addExternalEditFormCustom($form);

        $this->tpl->setContent($form->getHTML());
    }

    protected function validateCustom(ilPropertyFormGUI $a_form): bool
    {
        $refId = intval($this->object->getRefId());
        $settings = $this->repository->readEmployeeTalkSerieSettings(intval($this->object->getId()));
        $oldLockSettings = $settings->isLockedEditing();
        $lockEdititngForOthers = boolval(
            intval($a_form->getInput('etal_settings_locked_for_others'))
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
        $this->tabs_gui->activateTab("view_content");
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    public function confirmedDeleteObject(): void
    {
        if ($this->isReadonly) {
            ilSession::clear("saved_post");
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);

            return;
        }

        if ($this->post_wrapper->has("mref_id")) {
            $mref_id = $this->post_wrapper->retrieve(
                "mref_id",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            $saved_post = array_unique(array_merge(ilSession::get('saved_post'), $mref_id));
            ilSession::set('saved_post', $saved_post);
        }

        $ru = new ilRepositoryTrashGUI($this);
        $refIds = ilSession::get("saved_post");
        $talks = [];

        foreach ($refIds as $refId) {
            $talks[] = new ilObjEmployeeTalk(intval($refId), true);
        }

        $ru->deleteObjects($this->requested_ref_id, $refIds);
        $trashEnabled = boolval($this->container->settings()->get('enable_trash'));

        $this->sendNotification($talks);
        if ($trashEnabled) {
            foreach ($talks as $talk) {
                $talk->delete();
            }
        }

        ilSession::clear("saved_post");

        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);
    }

    /**
     * @param ilObjEmployeeTalk[] $talks
     */
    private function sendNotification(array $talks): void
    {
        $firstTalk = $talks[0];
        $talk_title = $firstTalk->getTitle();
        $superior = new ilObjUser($firstTalk->getOwner());
        $employee = new ilObjUser($firstTalk->getData()->getEmployee());
        $superiorName = $superior->getFullname();
        $series = $firstTalk->getParent();

        $dates = array_map(
            fn (ilObjEmployeeTalk $t) => $t->getData()->getStartDate(),
            $talks
        );
        usort($dates, function (ilDateTime $a, ilDateTime $b) {
            $a = $a->getUnixTime();
            $b = $b->getUnixTime();
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        });

        $add_time = $firstTalk->getData()->isAllDay() ? 0 : 1;
        $format = ilCalendarUtil::getUserDateFormat($add_time, true);
        $timezone = $employee->getTimeZone();
        $dates = array_map(function (ilDateTime $d) use ($add_time, $format, $timezone) {
            return $d->get(IL_CAL_FKT_DATE, $format, $timezone);
        }, $dates);

        $message = new EmployeeTalkEmailNotification(
            $firstTalk->getRefId(),
            $talk_title,
            $firstTalk->getDescription(),
            $firstTalk->getData()->getLocation(),
            'notification_talks_subject_update',
            'notification_talks_removed',
            $superiorName,
            $dates,
            false
        );

        // Check if we deleted the last talk of the series
        $vCalSender = null;
        if ($series->hasChildren()) {
            $vCalSender = new EmployeeTalkEmailNotificationService(
                $message,
                $talk_title,
                $employee,
                $superior,
                VCalendarFactory::getInstanceFromTalks($series)
            );
        } else {
            $vCalSender = new EmployeeTalkEmailNotificationService(
                $message,
                $talk_title,
                $employee,
                $superior,
                VCalendarFactory::getEmptyInstance($series, $talk_title)
            );
        }

        $vCalSender->send();
    }

    /**
     * @param ilObjEmployeeTalk[] $talks
     */
    private function sendUpdateNotification(array $talks): void
    {
        if (count($talks) === 0) {
            return;
        }

        $firstTalk = $talks[0];
        $talk_title = $firstTalk->getTitle();
        $superior = new ilObjUser($firstTalk->getOwner());
        $employee = new ilObjUser($firstTalk->getData()->getEmployee());
        $superiorName = $superior->getFullname();

        $dates = array_map(
            fn (ilObjEmployeeTalk $t) => $t->getData()->getStartDate(),
            $talks
        );
        usort($dates, function (ilDateTime $a, ilDateTime $b) {
            $a = $a->getUnixTime();
            $b = $b->getUnixTime();
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        });

        $add_time = $firstTalk->getData()->isAllDay() ? 0 : 1;
        $format = ilCalendarUtil::getUserDateFormat($add_time, true);
        $timezone = $employee->getTimeZone();
        $dates = array_map(function (ilDateTime $d) use ($add_time, $format, $timezone) {
            return $d->get(IL_CAL_FKT_DATE, $format, $timezone);
        }, $dates);

        $message = new EmployeeTalkEmailNotification(
            $firstTalk->getRefId(),
            $talk_title,
            $firstTalk->getDescription(),
            $firstTalk->getData()->getLocation(),
            'notification_talks_subject_update',
            'notification_talks_updated',
            $superiorName,
            $dates
        );

        $vCalSender = new EmployeeTalkEmailNotificationService(
            $message,
            $talk_title,
            $employee,
            $superior,
            VCalendarFactory::getInstanceFromTalks($firstTalk->getParent())
        );

        $vCalSender->send();
    }

    public function cancelDeleteObject(): void
    {
        ilSession::clear("saved_post");

        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);
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

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
        $writeLockForOthers->setDisabled($this->isReadonly || !$this->talkAccess->canEditTalkLockStatus(intval($this->object->getRefId())));
        $form->addItem($writeLockForOthers);

        $form->addItem($generalSection);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $ta->setDisabled($this->isReadonly);
        $form->addItem($ta);

        $this->initEditCustomForm($form);

        if (!$this->isReadonly) {
            $form->addCommandButton("update", $this->lng->txt("save"));
        }
        //$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));

        return $form;
    }

    public function addExternalEditFormCustom(ilPropertyFormGUI $a_form): void
    {
        /**
         * @var EmployeeTalk $data
         */
        $data = $this->object->getData();

        $location = new ilTextInputGUI($this->lng->txt('location'), 'etal_location');
        $location->setMaxLength(200);
        $location->setValue($data->getLocation());
        $location->setDisabled($this->isReadonly);
        $a_form->addItem($location);

        $completed = new ilCheckboxInputGUI($this->lng->txt('etal_status_completed'), 'etal_completed');
        $completed->setChecked($data->isCompleted());
        $completed->setDisabled($this->isReadonly);
        $a_form->addItem($completed);

        $this->container->ctrl()->setParameterByClass(strtolower(ilEmployeeTalkAppointmentGUI::class), 'ref_id', $this->ref_id);

        if (!$this->isReadonly) {
            $btnChangeThis = ilLinkButton::getInstance();
            $btnChangeThis->setCaption("change_date_of_talk");
            $editMode = '&' . ilEmployeeTalkAppointmentGUI::EDIT_MODE . '=' . ilEmployeeTalkAppointmentGUI::EDIT_MODE_APPOINTMENT;
            $btnChangeThis->setUrl($this->ctrl->getLinkTargetByClass(strtolower(ilEmployeeTalkAppointmentGUI::class), ControlFlowCommand::UPDATE_INDEX) . $editMode);
            $this->toolbar->addButtonInstance($btnChangeThis);

            $btnChangeAll = ilLinkButton::getInstance();
            $btnChangeAll->setCaption("change_date_of_series");
            $editMode = '&' . ilEmployeeTalkAppointmentGUI::EDIT_MODE . '=' . ilEmployeeTalkAppointmentGUI::EDIT_MODE_SERIES;
            $btnChangeAll->setUrl($this->ctrl->getLinkTargetByClass(strtolower(ilEmployeeTalkAppointmentGUI::class), ControlFlowCommand::UPDATE_INDEX) . $editMode);
            $this->toolbar->addButtonInstance($btnChangeAll);
        }

        $md = $this->initMetaDataForm($a_form);
        $md->parse();

        // this is necessary to disable the md fields
        if ($this->isReadonly) {
            foreach ($a_form->getInputItemsRecursive() as $item) {
                if ($item instanceof ilCombinationInputGUI) {
                    $item->__call('setValue', ['']);
                    $item->__call('setDisabled', [true]);
                }
                if (method_exists($item, 'setDisabled')) {
                    /** @var $item ilFormPropertyGUI */
                    $item->setDisabled(true);
                }
            }
        }

        parent::addExternalEditFormCustom($a_form);
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        /**
         * @var EmployeeTalk $data
         */
        $data = $this->object->getData();
        $parent = $this->object->getParent();
        $settings = $this->repository->readEmployeeTalkSerieSettings(intval($parent->getId()));

        $a_values['etal_superior'] = ilObjUser::_lookupLogin(intval($this->object->getOwner()));
        $a_values['etal_employee'] = ilObjUser::_lookupLogin($data->getEmployee());
        $a_values['etal_settings_locked_for_others'] = $settings->isLockedEditing();
    }

    protected function updateCustom(ilPropertyFormGUI $a_form): void
    {
        /**
         * @var ilObjEmployeeTalkSeries $series
         */
        $series = $this->object->getParent();
        $updated_series = false;

        $md = $this->initMetaDataForm($a_form);
        $md->parse();
        $md->importEditFormPostValues();
        $md->writeEditForm($series->getId(), $this->object->getId());

        $location = $a_form->getInput('etal_location');
        $completed = boolval(
            intval($a_form->getInput('etal_completed'))
        );
        $lockEdititngForOthers = boolval(
            intval($a_form->getInput('etal_settings_locked_for_others'))
        );

        $settings = $this->repository->readEmployeeTalkSerieSettings(intval($series->getId()));
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

        parent::updateCustom($a_form);

        $this->sendUpdateNotification($talks);
    }

    public function viewObject(): void
    {
        $this->tabs_gui->activateTab('view_content');
        $this->editObject();
    }

    protected function getTabs(): void
    {
        $this->tabs_gui->addTab('view_content', $this->lng->txt("content"), $this->ctrl->getLinkTarget($this, ControlFlowCommand::UPDATE));
        $this->tabs_gui->addTab("info_short", "Info", $this->ctrl->getLinkTargetByClass(strtolower(ilInfoScreenGUI::class), "showSummary"));
        //$this->tabs_gui->addTab('settings', $this->lng->txt("settings"), $this->ctrl->getLinkTarget($this, "edit"));
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

    private function initMetaDataForm(ilPropertyFormGUI $form): ilAdvancedMDRecordGUI
    {
        /**
         * @var ilObjEmployeeTalkSeries $series
         */
        $series = $this->object->getParent();
        $md = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            $series->getType(),
            $series->getId(),
            $this->object->getType(),
            $this->object->getId(),
            false
        );
        $md->setPropertyForm($form);
        return $md;
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
