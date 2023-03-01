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
use ILIAS\Modules\EmployeeTalk\Talk\EmployeeTalkPeriod;
use ILIAS\EmployeeTalk\Service\EmployeeTalkEmailNotificationService;
use ILIAS\EmployeeTalk\Service\VCalendarFactory;
use ILIAS\EmployeeTalk\Service\EmployeeTalkEmailNotification;

/**
 * Class ilObjEmployeeTalkGUI
 *
 * @ilCtrl_IsCalledBy ilObjEmployeeTalkSeriesGUI: ilEmployeeTalkMyStaffListGUI
 * @ilCtrl_IsCalledBy ilObjEmployeeTalkSeriesGUI: ilEmployeeTalkMyStaffUserGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilColumnGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilUserTableGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilObjFileGUI
 * @ilCtrl_Calls      ilObjEmployeeTalkSeriesGUI: ilObjFileUploadHandlerGUI
 */
final class ilObjEmployeeTalkSeriesGUI extends ilContainerGUI
{
    private \ILIAS\DI\Container $container;
    protected ilPropertyFormGUI $form;
    private int $userId = -1;

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

        $this->type = ilObjEmployeeTalkSeries::TYPE;

        $this->setReturnLocation("save", strtolower(ilEmployeeTalkMyStaffListGUI::class));
        $wrapper = $this->container->http()->wrapper()->query();

        if ($wrapper->has('usr_id')) {
            $this->userId = $wrapper->retrieve('usr_id', $this->container->refinery()->kindlyTo()->int());
        }

        $this->omitLocator();
        $this->container->ui()->mainTemplate()->setTitle($this->container->language()->txt('mst_my_staff'));
    }

    private function checkAccessOrFail(): void
    {
        $talkAccess = new ilObjEmployeeTalkAccess();
        if (!$talkAccess->canCreate()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    public function executeCommand(): void
    {
        $this->checkAccessOrFail();

        // determine next class in the call structure
        $next_class = $this->container->ctrl()->getNextClass($this);

        switch ($next_class) {
            case strtolower(ilRepositorySearchGUI::class):
                $repo = new ilRepositorySearchGUI();
                $repo->addUserAccessFilterCallable(function ($userIds) {
                    /**
                     * @var ilAccess $access
                     */
                    $access = $GLOBALS['DIC']->access();

                    //this method does not check permissions (ILIAS 6.7)
                    return $access->filterUserIdsForUsersPositionsAndPermission(
                        $userIds,
                        $this->user->getId(),
                        ''
                    );
                });
                $this->container->ctrl()->forwardCommand($repo);
                break;
            default:
                parent::executeCommand();
        }
    }

    /**
     * Talk Series does not use RBAC and therefore does not require the usual permission checks.
     * Talk series it self can no longer be edited after creation.
     *
     * @param string $a_perm
     * @param string $a_cmd
     * @param string $a_type
     * @param null   $a_ref_id
     * @return bool
     */
    protected function checkPermissionBool(string $a_perm, string $a_cmd = "", string $a_type = "", ?int $a_ref_id = null): bool
    {
        if ($a_perm === 'create') {
            return true;
        }
        return false;
    }

    public function confirmedDeleteObject(): void
    {
        if ($this->post_wrapper->has("mref_id")) {
            $mref_id = $this->post_wrapper->retrieve(
                "mref_id",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            $saved_post = array_unique(array_merge(ilSession::get('saved_post'), $mref_id));
            ilSession::set('saved_post', $saved_post);
        }

        $ru = new ilRepositoryTrashGUI($this);
        $ru->deleteObjects($this->requested_ref_id, ilSession::get("saved_post"));
        ilSession::clear("saved_post");

        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);
    }

    public function cancelDeleteObject(): void
    {
        ilSession::clear("saved_post");

        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);
    }

    public function cancelObject(): void
    {
        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);
    }

    /**
     * Redirect to etalk mystaff list instead of parent which is not accessible by most users.
     *
     * @param ilObject $a_new_object
     */
    protected function afterSave(ilObject $a_new_object): void
    {
        /**
         * @var ilObjEmployeeTalkSeries $newObject
         */
        $newObject = $a_new_object;

        // Create clones of the first one
        $event = $this->loadRecurrenceSettings();
        $this->copyTemplateValues($newObject);
        $this->createRecurringTalks($newObject, $event);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->redirectByClass(strtolower(ilEmployeeTalkMyStaffListGUI::class), ControlFlowCommand::DEFAULT, "", false);
    }

    public function saveObject(): void
    {
        $this->ctrl->setParameter($this, "new_type", $this->requested_new_type);

        $form = $this->initCreateForm($this->requested_new_type);
        if ($form->checkInput()) {
            $userName = (string) $form->getInput('etal_employee');
            $userId = (int) ilObjUser::_lookupId($userName);
            $talkAccess = new ilObjEmployeeTalkAccess();
            if (
                !ilObjUser::_loginExists($userName) ||
                !$talkAccess->canCreate(new ilObjUser($userId))
            ) {
                $form->getItemByPostVar('etal_employee')
                     ->setAlert($this->lng->txt('etal_invalid_user'));
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('form_input_not_valid')
                );
                $form->setValuesByPost();
                $this->tpl->setContent($form->getHTML());
                return;
            }

            $this->ctrl->setParameter($this, "new_type", "");

            $class_name = "ilObj" . $this->obj_definition->getClassName($this->requested_new_type);
            $newObj = new $class_name();
            $newObj->setType($this->requested_new_type);
            $newObj->setTitle($form->getInput("title"));
            $newObj->setDescription($form->getInput("desc"));
            $newObj->create();

            $this->putObjectInTree($newObj);

            $this->afterSave($newObj);
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function initCreateForm(string $new_type): ilPropertyFormGUI
    {
        // Init dom events or ui will break on page load
        ilYuiUtil::initDomEvent();

        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save") . '&template=' . $this->getTemplateRefId());
        $form->setTitle($this->lng->txt($new_type . "_new"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        // Start & End Date
        $dur = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'), 'etal_event');
        $dur->setRequired(true);
        $dur->enableToggleFullTime(
            $this->lng->txt('cal_fullday_title'),
            false
        );
        $dur->setShowTime(true);
        $form->addItem($dur);

        // Recurrence
        $cal = new ilRecurrenceInputGUI("Calender", "frequence");
        $event = new ilCalendarRecurrence();
        //$event->setRecurrence(ilEventRecurrence::REC_EXCLUSION);
        //$event->setFrequenceType(ilEventRecurrence::FREQ_DAILY);
        $cal->allowUnlimitedRecurrences(false);
        $cal->setRecurrence($event);
        $form->addItem($cal);

        //Location
        $location = new ilTextInputGUI("Location", "etal_location");
        $location->setMaxLength(200);
        $form->addItem($location);

        //Employee
        $login = new ilTextInputGUI($this->lng->txt("employee"), "etal_employee");
        $login->setRequired(true);
        $login->setDataSource($this->ctrl->getLinkTargetByClass([
            strtolower(self::class),
            strtolower(ilRepositorySearchGUI::class)
        ], 'doUserAutoComplete', '', true));

        if ($this->userId !== -1) {
            $user = new ilObjUser($this->userId);
            $login->setValue($user->getLogin());
        }

        $form->addItem($login);

        $form = $this->initDidacticTemplate($form);

        $form->addCommandButton("save", $this->lng->txt($new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        $this->form = $form;

        return $form;
    }

    protected function initCreationForms($new_type): array
    {
        return [
            self::CFORM_NEW => $this->initCreateForm($new_type)
        ];
    }

    public function viewObject(): void
    {
        $this->tabs_gui->activateTab('view_content');
    }

    public function getTabs(): void
    {
    }

    public function getAdminTabs(): void
    {
    }

    /**
     * @param ilObjEmployeeTalk[] $talks
     */
    private function sendNotification(array $talks): void
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
            'notification_talks_subject',
            'notification_talks_created',
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

    /**
     * load recurrence settings
     *
     * @access protected
     * @return
     */
    protected function loadRecurrenceSettings(): ilCalendarRecurrence
    {
        $rec = new ilCalendarRecurrence();

        switch ($this->form->getInput('frequence')) {
            case ilCalendarRecurrence::FREQ_DAILY:
                $rec->setFrequenceType($this->form->getInput('frequence'));
                $rec->setInterval((int) $this->form->getInput('count_DAILY'));
                break;

            case ilCalendarRecurrence::FREQ_WEEKLY:
                $rec->setFrequenceType($this->form->getInput('frequence'));
                $rec->setInterval((int) $this->form->getInput('count_WEEKLY'));
                if (is_array($this->form->getInput('byday_WEEKLY'))) {
                    $rec->setBYDAY(ilUtil::stripSlashes(implode(',', $this->form->getInput('byday_WEEKLY'))));
                }
                break;

            case ilCalendarRecurrence::FREQ_MONTHLY:
                $rec->setFrequenceType($this->form->getInput('frequence'));
                $rec->setInterval((int) $this->form->getInput('count_MONTHLY'));
                switch ((int) $this->form->getInput('subtype_MONTHLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        switch ((int) $this->form->getInput('monthly_byday_day')) {
                            case 8:
                                // Weekday
                                $rec->setBYSETPOS($this->form->getInput('monthly_byday_num'));
                                $rec->setBYDAY('MO,TU,WE,TH,FR');
                                break;

                            case 9:
                                // Day of month
                                $rec->setBYMONTHDAY($this->form->getInput('monthly_byday_num'));
                                break;

                            default:
                                $rec->setBYDAY(($this->form->getInput('monthly_byday_num') . $this->form->getInput('monthly_byday_day')));
                                break;
                        }
                        break;

                    case 2:
                        $rec->setBYMONTHDAY($this->form->getInput('monthly_bymonthday'));
                        break;
                }
                break;

            case ilCalendarRecurrence::FREQ_YEARLY:
                $rec->setFrequenceType($this->form->getInput('frequence'));
                $rec->setInterval((int) $this->form->getInput('count_YEARLY'));
                switch ((int) $this->form->getInput('subtype_YEARLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        $rec->setBYMONTH($this->form->getInput('yearly_bymonth_byday'));
                        $rec->setBYDAY(($this->form->getInput('yearly_byday_num') . $this->form->getInput('yearly_byday')));
                        break;

                    case 2:
                        $rec->setBYMONTH($this->form->getInput('yearly_bymonth_by_monthday'));
                        $rec->setBYMONTHDAY($this->form->getInput('yearly_bymonthday'));
                        break;
                }
                break;
        }

        // UNTIL
        switch ((int) $this->form->getInput('until_type')) {
            case 1:
                $rec->setFrequenceUntilDate(null);
                // nothing to do
                break;

            case 2:
                $rec->setFrequenceUntilDate(null);
                $rec->setFrequenceUntilCount((int) $this->form->getInput('count'));
                break;

            case 3:
                $frequence = $this->form->getItemByPostVar('frequence');
                $end = $frequence->getRecurrence()->getFrequenceUntilDate();
                $rec->setFrequenceUntilCount(0);
                $rec->setFrequenceUntilDate($end);
                break;
        }

        return $rec;
    }



    private function loadEtalkData(): EmployeeTalk
    {
        $location = $this->form->getInput('etal_location');
        $employee = $this->form->getInput('etal_employee');
        ['fullday' => $tgl] = $this->form->getInput('etal_event');

        /**
         * @var ilDateDurationInputGUI $dateTimeInput
         */
        $dateTimeInput = $this->form->getItemByPostVar('etal_event');
        ['start' => $start, 'end' => $end] = $dateTimeInput->getValue();
        if (intval($tgl)) {
            $start_date = new ilDate($start, IL_CAL_UNIX);
            $end_date = new ilDate($end, IL_CAL_UNIX);
        } else {
            $start_date = new ilDateTime($start, IL_CAL_UNIX, ilTimeZone::UTC);
            $end_date = new ilDateTime($end, IL_CAL_UNIX, ilTimeZone::UTC);
        }

        return new EmployeeTalk(
            -1,
            $start_date,
            $end_date,
            boolval(intval($tgl)),
            '',
            $location ?? '',
            ilObjUser::getUserIdByLogin($employee),
            false,
            false
        );
    }

    /**
     * Copy the template values, into the talk series object.
     *
     * @param ilObjEmployeeTalkSeries $talk
     */
    private function copyTemplateValues(ilObjEmployeeTalkSeries $talk)
    {
        $template = new ilObjTalkTemplate($this->getTemplateRefId(), true);
        $talk->setTitle($template->getTitle());
        $talk->setDescription($template->getLongDescription());
        $template->cloneMetaData($talk);
        $talk->update();

        // assign talk series type to adv md records of the template
        foreach (ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $template->getType(),
            $template->getId(),
            'etal',
            false
        ) as $rec) {
            if (!$rec->isAssignedObjectType($talk->getType(), 'etal')) {
                $rec->appendAssignedObjectType(
                    $talk->getType(),
                    'etal',
                    true
                );
                $rec->update();
            }
        }

        ilAdvancedMDRecord::saveObjRecSelection(
            $talk->getId(),
            'etal',
            ilAdvancedMDRecord::getObjRecSelection($template->getId(), 'etal')
        );

        ilAdvancedMDValues::_cloneValues(
            0,
            $template->getId(),
            $talk->getId(),
            ilObjEmployeeTalk::TYPE
        );
    }

    /**
     * create recurring talks
     * @param ilObjEmployeeTalkSeries    $talk
     * @param ilCalendarRecurrence $recurrence
     *
     * @return bool true if successful otherwise false
     */
    private function createRecurringTalks(ilObjEmployeeTalkSeries $talk, ilCalendarRecurrence $recurrence): bool
    {
        $data = $this->loadEtalkData();

        $firstAppointment = new EmployeeTalkPeriod(
            $data->getStartDate(),
            $data->getEndDate(),
            $data->isAllDay()
        );
        $calc = new ilCalendarRecurrenceCalculator($firstAppointment, $recurrence);

        $periodStart = clone $data->getStartDate();

        $periodEnd = clone $data->getStartDate();
        $periodEnd->increment(IL_CAL_YEAR, 5);
        $dateIterator = $calc->calculateDateList($periodStart, $periodEnd);

        $periodDiff = $data->getEndDate()->get(IL_CAL_UNIX) -
            $data->getStartDate()->get(IL_CAL_UNIX);

        $talkSession = new ilObjEmployeeTalk();
        $talkSession->setTitle($this->form->getInput('title'));
        $talkSession->setDescription($this->form->getInput('desc'));
        $talkSession->setType(ilObjEmployeeTalk::TYPE);
        $talkSession->create();

        $talkSession->createReference();
        $talkSession->putInTree($talk->getRefId());

        $data->setObjectId($talkSession->getId());
        $talkSession->setData($data);
        $talkSession->update();
        $talks = [];
        $talks[] = $talkSession;

        if (!$recurrence->getFrequenceType()) {
            $this->sendNotification($talks);
            return true;
        }

        // Remove start date
        $dateIterator->removeByDAY($periodStart);
        $dateIterator->rewind();

        /**
         * @var ilDateTime $date
         */
        foreach ($dateIterator as $date) {
            $cloneObject = $talkSession->cloneObject($talk->getRefId());
            $cloneData = $cloneObject->getData();

            $cloneData->setStartDate($date);
            $endDate = $date->get(IL_CAL_UNIX) + $periodDiff;
            if ($cloneData->isAllDay()) {
                $cloneData->setEndDate(new ilDate($endDate, IL_CAL_UNIX));
            } else {
                $cloneData->setEndDate(new ilDateTime($endDate, IL_CAL_UNIX, ilTimeZone::UTC));
            }
            $cloneObject->setData($cloneData);
            $cloneObject->update();
            $talks[] = $cloneObject;
        }

        $this->sendNotification($talks);

        return true;
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

    private function getTemplateRefId(): int
    {
        $template = filter_input(INPUT_GET, 'template', FILTER_VALIDATE_INT);
        $refId = intval($template);
        if (
            $template === null ||
            $template === false ||
            !ilObjTalkTemplate::_exists($refId, true) ||
            ilObjTalkTemplate::lookupOfflineStatus(ilObjTalkTemplate::_lookupObjectId($refId)) ?? true
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('etal_create_invalid_template_ref'), true);
            $this->ctrl->redirectByClass([
                strtolower(ilDashboardGUI::class),
                strtolower(ilMyStaffGUI::class),
                strtolower(ilEmployeeTalkMyStaffListGUI::class)
            ], ControlFlowCommand::INDEX);
        }

        return intval($template);
    }
}
