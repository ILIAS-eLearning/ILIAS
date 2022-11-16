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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as RefineryFactory;

/**
 * Administrate calendar appointments
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarAppointmentGUI
{
    private ilPropertyFormGUI $form;
    private ilCalendarUserNotification $notification;

    protected ilDate $seed;
    protected ilDateTime $initialDate;
    protected bool $default_fulltime = true;
    protected ilCalendarEntry $app;
    protected ilCalendarRecurrence $rec;
    protected string $timezone;

    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilSetting $settings;
    protected ilHelpGUI $help;
    protected ilErrorHandling $error;
    private ilLogger $logger;
    protected HTTPServices $http;
    protected RefineryFactory $refinery;
    protected RequestInterface $request;

    /**
     * @todo make appointment_id required and remove all GET request
     */
    public function __construct(ilDate $seed, ilDate $initialDate, int $a_appointment_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->logger = $DIC->logger()->cal();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();
        $this->error = $DIC['ilErr'];

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();

        $this->initTimeZone();
        $this->initSeed($seed);
        $this->initInitialDate($initialDate);
        $this->initAppointment($a_appointment_id);
    }

    protected function getAppointmentIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('app_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function getRecurrenceExclusionFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('rexl')) {
            return $this->http->wrapper()->query()->retrieve(
                'rexl',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function getRecurrenceDateFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('dt')) {
            return $this->http->wrapper()->query()->retrieve(
                'dt',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function executeCommand(): void
    {
        // Clear tabs and set back target
        $this->tabs->clearTargets();
        if ($this->http->wrapper()->query()->has('app_id')) {
            $this->ctrl->saveParameter($this, 'app_id');
        }
        $this->tabs->setBackTarget(
            $this->lng->txt('cal_back_to_cal'),
            $this->ctrl->getLinkTarget($this, 'cancel')
        );

        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("add");
                $this->$cmd();
                break;
        }
    }

    public function getAppointment(): ilCalendarEntry
    {
        return $this->app;
    }

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function initForm(
        string $a_mode,
        bool $a_as_milestone = false,
        bool $a_edit_single_app = false
    ): ilPropertyFormGUI {
        $this->form = new ilPropertyFormGUI();
        ilYuiUtil::initDomEvent();
        $resp_info = false;
        switch ($a_mode) {
            case 'create':
                $this->ctrl->saveParameter($this, array('seed', 'idate'));
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                if ($a_as_milestone) {
                    $this->form->setTitle($this->lng->txt('cal_new_ms'));
                    $this->form->addCommandButton('saveMilestone', $this->lng->txt('cal_add_milestone'));
                } else {
                    $this->form->setTitle($this->lng->txt('cal_new_app'));
                    $this->form->addCommandButton('save', $this->lng->txt('cal_add_appointment'));
                }
                $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
                break;

            case 'edit':
                if ($a_as_milestone) {
                    $this->form->setTitle($this->lng->txt('cal_edit_milestone'));
                } else {
                    $this->form->setTitle($this->lng->txt('cal_edit_appointment'));
                }
                $this->ctrl->saveParameter($this, array('seed', 'app_id', 'idate'));
                $this->form->setFormAction($this->ctrl->getFormAction($this));

                $ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
                $cat = $ass->getFirstAssignment();
                $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat);
                $type = ilObject::_lookupType($cat_info['obj_id']);
                if ($a_as_milestone && $cat_info['type'] == ilCalendarCategory::TYPE_OBJ
                    && ($type == "grp" || $type == "crs")) {
                    $resp_info = true;
                    $this->form->addCommandButton(
                        'editResponsibleUsers',
                        $this->lng->txt('cal_change_responsible_users')
                    );
                }
                $this->form->addCommandButton('update', $this->lng->txt('save'));
                // $this->form->addCommandButton('askDelete',$this->lng->txt('delete'));
                $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
                break;
        }
        // title
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->app->getTitle());
        $title->setRequired(true);
        $title->setMaxLength(128);
        $title->setSize(32);
        $this->form->addItem($title);

        $category_id = 0;
        if ($this->http->wrapper()->query()->has('category_id')) {
            $category_id = $this->http->wrapper()->query()->retrieve(
                'category_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        // calendar selection
        $calendar = new ilSelectInputGUI($this->lng->txt('cal_category_selection'), 'calendar');

        $selected_calendar = 0;
        if ($this->http->wrapper()->post()->has('calendar')) {
            $selected_calendar = $this->http->wrapper()->post()->retrieve(
                'calendar',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($selected_calendar > 0) {
            $calendar->setValue($selected_calendar);
        } elseif ($category_id) {
            $calendar->setValue((int) $category_id);
            $selected_calendar = (int) $category_id;
        } elseif ($a_mode == 'edit') {
            $ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
            $cat = $ass->getFirstAssignment();
            $calendar->setValue($cat);
            $selected_calendar = $cat;
        } elseif ($ref_id) {
            $obj_cal = ilObject::_lookupObjId($ref_id);
            $calendar->setValue(ilCalendarCategories::_lookupCategoryIdByObjId($obj_cal));
            $selected_calendar = ilCalendarCategories::_lookupCategoryIdByObjId($obj_cal);
            $cats = ilCalendarCategories::_getInstance($this->user->getId());
            $cats->readSingleCalendar($selected_calendar);
        } else {
            $cats = ilCalendarCategories::_getInstance($this->user->getId());
            $categories = $cats->prepareCategoriesOfUserForSelection();
            $selected_calendar = key($categories);
            $calendar->setValue($selected_calendar);
        }
        $calendar->setRequired(true);
        $cats = ilCalendarCategories::_getInstance($this->user->getId());
        $calendar->setOptions($cats->prepareCategoriesOfUserForSelection());

        if (ilCalendarSettings::_getInstance()->isNotificationEnabled()) {
            $notification_cals = $cats->getNotificationCalendars();
            $notification_cals = count($notification_cals) ? implode(',', $notification_cals) : '';
            $calendar->addCustomAttribute("onchange=\"ilToggleNotification([" . $notification_cals . "]);\"");
        }
        $this->form->addItem($calendar);

        if (!$a_as_milestone) {
            $dur = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'), 'event');
            $dur->setRequired(true);
            $dur->enableToggleFullTime(
                $this->lng->txt('cal_fullday_title'),
                $this->app->isFullday()
            );
            $dur->setShowTime(true);
            $dur->setStart($this->app->getStart());
            $dur->setEnd($this->app->getEnd());
            $this->form->addItem($dur);

            // recurrence
            $rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
            $rec->setRecurrence($this->rec);
            $this->form->addItem($rec);

            // location
            $where = new ilTextInputGUI($this->lng->txt('cal_where'), 'location');
            $where->setValue($this->app->getLocation());
            $where->setMaxLength(128);
            $where->setSize(32);
            $this->form->addItem($where);
        } else {
            $deadline = new ilDateTimeInputGUI($this->lng->txt('cal_deadline'), 'event_start');
            $deadline->setDate($this->app->getStart());
            $deadline->setShowTime(false);
            $deadline->setMinuteStepSize(5);
            $this->form->addItem($deadline);

            // completion
            $completion_vals = array();
            for ($i = 0; $i <= 100; $i += 5) {
                $completion_vals[$i] = $i . " %";
            }
            $compl = new ilSelectInputGUI(
                $this->lng->txt('cal_task_completion'),
                'completion'
            );
            $compl->setOptions($completion_vals);
            $compl->setValue($this->app->getCompletion());
            $this->form->addItem($compl);
        }

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $desc->setValue($this->app->getDescription());
        $desc->setRows(5);
        $this->form->addItem($desc);

        if ($a_as_milestone && $a_mode == "edit" && $resp_info) {
            // users responsible
            $users = $this->app->readResponsibleUsers();
            $resp = new ilNonEditableValueGUI($this->lng->txt('cal_responsible'), "", true);
            $delim = "";
            $value = '';
            foreach ($users as $r) {
                $value .= $delim . $r["lastname"] . ", " . $r["firstname"] . " [" . $r["login"] . "]";
                $delim = "<br />";
            }
            if (count($users) > 0) {
                $resp->setValue($value);
            } else {
                $resp->setValue("-");
            }

            $this->form->addItem($resp);
        }

        if (ilCalendarSettings::_getInstance()->isUserNotificationEnabled()) {
            $ajax_url = $this->ctrl->getLinkTarget(
                $this,
                'doUserAutoComplete',
                '',
                true,
                false
            );

            $notu = new ilTextInputGUI(
                $this->lng->txt('cal_user_notification'),
                'notu'
            );
            $notu->setMulti(true, true);
            $notu->setInfo($this->lng->txt('cal_user_notification_info'));
            $notu->setDataSource($ajax_url, ',');

            $values = [];
            foreach ($this->notification->getRecipients() as $rcp) {
                switch ($rcp['type']) {
                    case ilCalendarUserNotification::TYPE_USER:
                        $values[] = ilObjUser::_lookupLogin($rcp['usr_id']);
                        break;

                    case ilCalendarUserNotification::TYPE_EMAIL:
                        $values[] = $rcp['email'];
                        break;
                }
            }
            $notu->setValue($values);
            $this->form->addItem($notu);
        }

        // Notifications
        if (ilCalendarSettings::_getInstance()->isNotificationEnabled() and count($cats->getNotificationCalendars())) {
            $selected_cal = new ilCalendarCategory($selected_calendar);
            $disabled = true;
            if ($selected_cal->getType() == ilCalendarCategory::TYPE_OBJ) {
                if (ilObject::_lookupType($selected_cal->getObjId()) == 'crs' or ilObject::_lookupType($selected_cal->getObjId()) == 'grp') {
                    $disabled = false;
                }
            }

            $this->tpl->addJavaScript('./Services/Calendar/js/toggle_notification.js');
            $not = new ilCheckboxInputGUI($this->lng->txt('cal_cg_notification'), 'not');
            $not->setInfo($this->lng->txt('cal_notification_info'));
            $not->setValue('1');
            $not->setChecked($this->app->isNotificationEnabled());
            $not->setDisabled($disabled);
            $this->form->addItem($not);
        }
        return $this->form;
    }


    protected function doUserAutoComplete(): ?string
    {
        // hide anonymout request
        if ($this->user->getId() == ANONYMOUS_USER_ID) {
            return json_encode(new stdClass(), JSON_THROW_ON_ERROR);
        }
        if (!$this->http->wrapper()->query()->has('autoCompleteField')) {
            $a_fields = [
                'login',
                'firstname',
                'lastname',
                'email'
            ];
            $result_field = 'login';
        } else {
            $auto_complete_field = $this->http->wrapper()->query()->retrieve(
                'autoCompleteField',
                $this->refinery->kindlyTo()->string()
            );
            $a_fields = [$auto_complete_field];
            $result_field = $auto_complete_field;
        }
        $auto = new ilUserAutoComplete();
        $auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);

        if ($this->http->wrapper()->query()->has('fetchall')) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        $auto->setMoreLinkAvailable(true);
        $auto->setSearchFields($a_fields);
        $auto->setResultField($result_field);
        $auto->enableFieldSearchableCheck(true);
        $query = '';
        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        echo $auto->getList($query);
        return null;
    }

    /**
     * add new appointment
     */
    protected function add(?ilPropertyFormGUI $form = null): void
    {
        $this->help->setScreenIdComponent("cal");
        $this->help->setScreenId("app");
        $this->help->setSubScreenId("create");

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initForm('create');
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * add milestone
     */
    protected function addMilestone(): void
    {
        $this->help->setScreenIdComponent("cal");
        $this->help->setScreenId("app");
        $this->help->setSubScreenId("create_milestone");

        $form = $this->initForm('create', true);
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveMilestone(): void
    {
        $this->save(true);
    }

    protected function save(bool $a_as_milestone = false): void
    {
        $form = $this->load('create', $a_as_milestone);

        if ($this->app->validate() and $this->notification->validate()) {
            if ((int) $form->getInput('calendar') === 0) {
                $cat_id = $this->createDefaultCalendar();
            } else {
                $cat_id = (int) $form->getInput('calendar');
            }

            $this->app->save();
            $this->notification->setEntryId($this->app->getEntryId());
            $this->notification->save();
            $this->rec->setEntryId($this->app->getEntryId());
            $this->saveRecurrenceSettings();

            $ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
            $ass->addAssignment($cat_id);

            // Send notifications
            if (
                ilCalendarSettings::_getInstance()->isNotificationEnabled() &&
                (int) $form->getInput('not')
            ) {
                $this->distributeNotifications($cat_id, $this->app->getEntryId(), true);
            }
            if (ilCalendarSettings::_getInstance()->isUserNotificationEnabled()) {
                $this->distributeUserNotifications();
            }

            $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
            $type = ilObject::_lookupType($cat_info['obj_id'] ?? 0);

            if (
                $a_as_milestone &&
                ($cat_info['type'] ??  ilCalendarCategory::TYPE_UNDEFINED) == ilCalendarCategory::TYPE_OBJ &&
                ($type == "grp" || $type == "crs")
            ) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_created_milestone_resp_q'), true);
                $this->showResponsibleUsersList($cat_info['obj_id'] ?? 0);
                return;
            } elseif ($a_as_milestone) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_created_milestone'), true);
                $this->ctrl->returnToParent($this);
            } else {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_created_appointment'), true);
                $this->ctrl->returnToParent($this);
            }
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->error->getMessage());
            $this->add($this->form);
            return;
        }
        if ($a_as_milestone) {
            $this->addMilestone();
        } else {
            $this->add();
        }
    }

    /**
     * Send mail to selected users
     */
    protected function distributeUserNotifications(): void
    {
        $notification = new ilCalendarMailNotification();
        $notification->setAppointmentId($this->app->getEntryId());

        foreach ($this->notification->getRecipients() as $rcp) {
            switch ($rcp['type']) {
                case ilCalendarUserNotification::TYPE_USER:
                    $notification->setSender(ANONYMOUS_USER_ID);
                    $notification->setRecipients(array($rcp['usr_id']));
                    $notification->setType(ilCalendarMailNotification::TYPE_USER);
                    break;

                case ilCalendarUserNotification::TYPE_EMAIL:
                    $notification->setSender(ANONYMOUS_USER_ID);
                    $notification->setRecipients(array($rcp['email']));
                    $notification->setType(ilCalendarMailNotification::TYPE_USER_ANONYMOUS);
                    break;
            }
            $notification->send();
        }
    }

    protected function distributeNotifications(int $a_cat_id, int $app_id, bool $a_new_appointment = true): void
    {
        $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($a_cat_id);

        $notification = new ilCalendarMailNotification();
        $notification->setAppointmentId($app_id);

        switch ($cat_info['type']) {
            case ilCalendarCategory::TYPE_OBJ:

                switch ($cat_info['obj_type']) {
                    case 'crs':
                        $ref_ids = ilObject::_getAllReferences($cat_info['obj_id']);
                        $ref_id = current($ref_ids);
                        $notification->setRefId($ref_id);
                        $notification->setType(
                            $a_new_appointment ?
                                ilCalendarMailNotification::TYPE_CRS_NEW_NOTIFICATION :
                                ilCalendarMailNotification::TYPE_CRS_NOTIFICATION
                        );
                        break;

                    case 'grp':
                        $ref_ids = ilObject::_getAllReferences($cat_info['obj_id']);
                        $ref_id = current($ref_ids);
                        $notification->setRefId($ref_id);
                        $notification->setType(
                            $a_new_appointment ?
                                ilCalendarMailNotification::TYPE_GRP_NEW_NOTIFICATION :
                                ilCalendarMailNotification::TYPE_GRP_NOTIFICATION
                        );
                        break;
                }
                break;
        }
        $notification->send();
    }

    public function editResponsibleUsers(): void
    {
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->app->getEntryId());
        $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
        $this->showResponsibleUsersList($cat_info['obj_id']);
    }

    /**
     * Show responsible uses of a milestone (default set is participants
     * of group)
     */
    public function showResponsibleUsersList(int $a_grp_id): void
    {
        $table_gui = new ilMilestoneResponsiblesTableGUI(
            $this,
            "",
            $a_grp_id,
            $this->app->getEntryId()
        );
        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
     * Save milestone responsibilites
     */
    public function saveMilestoneResponsibleUsers(): void
    {
        $user_ids = [];
        if ($this->http->wrapper()->post()->has('user_id')) {
            $user_ids = $this->http->wrapper()->post()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $this->app->writeResponsibleUsers($user_ids);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Check edit single apppointment / edit all appointments for recurring appointments.
     * @todo works with milestones???
     */
    protected function askEdit(): void
    {
        // check for recurring entries
        $rec = ilCalendarRecurrences::_getRecurrences($this->getAppointment()->getEntryId());
        if (!$rec) {
            $this->edit(true);
            return;
        }
        // Show edit single/all appointments
        $this->ctrl->saveParameter($this, array('seed', 'app_id', 'dt', 'idate'));

        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->lng->txt('cal_delete_cal'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');
        $confirm->addItem('appointments[]', (string) $this->app->getEntryId(), $this->app->getTitle());
        $confirm->addButton($this->lng->txt('cal_edit_single'), 'editSingle');
        $confirm->setConfirm($this->lng->txt('cal_edit_recurrences'), 'edit');

        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Edit one single appointment
     * ^ */
    protected function editSingle(): void
    {
        $this->ctrl->setParameter($this, 'rexl', "1");
        $this->edit(true);
    }

    /**
     * edit appointment
     */
    protected function edit(bool $a_edit_single_app = false, ilPropertyFormGUI $form = null): void
    {
        $this->help->setScreenIdComponent("cal");
        $this->help->setScreenId("app");
        if ($this->app->isMilestone()) {
            $this->help->setSubScreenId("edit_milestone");
        } else {
            $this->help->setSubScreenId("edit");
        }

        $this->ctrl->saveParameter($this, array('seed', 'app_id', 'dt', 'idate'));
        if ($this->getRecurrenceExclusionFromQuery()) {
            $this->ctrl->setParameter($this, 'rexl', 1);

            // Calculate new appointment time
            $duration = $this->getAppointment()->getEnd()->get(IL_CAL_UNIX) - $this->getAppointment()->getStart()->get(IL_CAL_UNIX);
            $calc = new ilCalendarRecurrenceCalculator($this->getAppointment(), $this->rec);

            $current_date = new ilDateTime($this->getRecurrenceDateFromQuery(), IL_CAL_UNIX);

            $yesterday = clone $current_date;
            $yesterday->increment(IL_CAL_DAY, -1);
            $tomorrow = clone $current_date;
            $tomorrow->increment(IL_CAL_DAY, 1);

            foreach ($calc->calculateDateList($current_date, $tomorrow, 1) as $date_entry) {
                if (ilDateTime::_equals($current_date, $date_entry, IL_CAL_DAY)) {
                    $this->getAppointment()->setStart(new ilDateTime($date_entry->get(IL_CAL_UNIX), IL_CAL_UNIX));
                    $this->getAppointment()->setEnd(new ilDateTime(
                        $date_entry->get(IL_CAL_UNIX) + $duration,
                        IL_CAL_UNIX
                    ));
                    break;
                }
            }
            // Finally reset recurrence
            $this->rec = new ilCalendarRecurrence();
        }

        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->app->getEntryId());
        $cats = ilCalendarCategories::_getInstance($this->user->getId());

        if (!$cats->isVisible($cat_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
            return;
        }
        if (!$cats->isEditable($cat_id) or $this->app->isAutoGenerated()) {
            $this->showInfoScreen();
            return;
        }
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initForm('edit', $this->app->isMilestone(), $a_edit_single_app);
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function showInfoScreen(): void
    {
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));

        if ($this->app->isMilestone()) {
            $info->addSection($this->lng->txt('cal_ms_details'));
        } else {
            $info->addSection($this->lng->txt('cal_details'));
        }

        // Appointment
        $info->addProperty(
            $this->lng->txt('appointment'),
            ilDatePresentation::formatPeriod(
                $this->app->getStart(),
                $this->app->getEnd()
            )
        );
        $info->addProperty($this->lng->txt('title'), $this->app->getPresentationTitle());

        // Description
        if (strlen($desc = $this->app->getDescription())) {
            $info->addProperty($this->lng->txt('description'), $desc);
        }

        // Location
        if (strlen($loc = $this->app->getLocation())) {
            $info->addProperty($this->lng->txt('cal_where'), $loc);
        }

        // completion
        if ($this->app->isMilestone() && $this->app->getCompletion() > 0) {
            $info->addProperty(
                $this->lng->txt('cal_task_completion'),
                $this->app->getCompletion() . " %"
            );
        }

        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->app->getEntryId());
        $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
        $type = ilObject::_lookupType($cat_info['obj_id']);
        if ($this->app->isMilestone() && $cat_info['type'] == ilCalendarCategory::TYPE_OBJ
            && ($type == "grp" || $type == "crs")) {
            // users responsible
            $users = $this->app->readResponsibleUsers();
            $delim = "";
            $value = '';
            foreach ($users as $r) {
                $value .= $delim . $r["lastname"] . ", " . $r["firstname"] . " [" . $r["login"] . "]";
                $delim = "<br />";
            }
            if (count($users) > 0) {
                $info->addProperty(
                    $this->lng->txt('cal_responsible'),
                    $value
                );
            }
        }

        $category = new ilCalendarCategory($cat_id);

        if ($category->getType() == ilCalendarCategory::TYPE_OBJ) {
            $info->addSection($this->lng->txt('additional_info'));

            $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
            $refs = ilObject::_getAllReferences($cat_info['obj_id']);

            $href = ilLink::_getStaticLink(current($refs), ilObject::_lookupType($cat_info['obj_id']), true);
            $info->addProperty(
                $this->lng->txt('perma_link'),
                '<a class="small" href="' . $href . '" target="_top">' . $href . '</a>'
            );
        }
        $this->tpl->setContent($info->getHTML());
    }

    protected function update(): void
    {
        $single_editing = (bool) $this->getRecurrenceExclusionFromQuery();

        $form = $this->load('edit', $this->app->isMilestone());

        if ($this->app->validate() and $this->notification->validate()) {
            if (!(int) $form->getInput('calendar')) {
                $cat_id = $this->createDefaultCalendar();
            } else {
                $cat_id = (int) $form->getInput('calendar');
            }

            if ($single_editing) {
                $original_id = $this->getAppointment()->getEntryId();
                $this->getAppointment()->save();
                $selected_ut = $this->getRecurrenceDateFromQuery();
                if ($selected_ut > 0) {
                    $exclusion = new ilCalendarRecurrenceExclusion();
                    $exclusion->setEntryId($original_id);
                    $exclusion->setDate(new ilDate($selected_ut, IL_CAL_UNIX));
                    $this->logger->dump($this->getAppointment()->getEntryId());
                    $this->logger->dump(ilDatePresentation::formatDate(new ilDate($selected_ut, IL_CAL_UNIX)));
                    $exclusion->save();
                }
                $this->rec = new ilCalendarRecurrence();
                $this->rec->setEntryId($this->getAppointment()->getEntryId());
            } else {
                $this->getAppointment()->update();
            }
            $this->notification->save();
            $this->saveRecurrenceSettings();
            $ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
            $ass->deleteAssignments();
            $ass->addAssignment($cat_id);

            // Send notifications
            $notification = (bool) $form->getInput('not');
            if (
                ilCalendarSettings::_getInstance()->isNotificationEnabled() &&
                $notification
            ) {
                $this->distributeNotifications($cat_id, $this->app->getEntryId(), false);
            }
            if (ilCalendarSettings::_getInstance()->isUserNotificationEnabled()) {
                $this->distributeUserNotifications();
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->returnToParent($this);
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->error->getMessage());
        }
        $this->edit(false, $this->form);
    }

    protected function askDelete(): void
    {
        $this->ctrl->saveParameter(
            $this,
            [
                'seed',
                'app_id',
                'dt',
                'idate'
            ]
        );

        $app_id = (int) ($this->request->getQueryParams()['app_id'] ?? 0);
        if (!$app_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->ctrl->returnToParent($this);
        }

        $entry = new ilCalendarEntry($app_id);
        $recs = ilCalendarRecurrences::_getRecurrences($app_id);
        if (
            !count($recs) ||
            $this->app->isMilestone()
        ) {
            $confirm = new ilConfirmationGUI();
            $confirm->setFormAction($this->ctrl->getFormAction($this));
            $confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
            $confirm->setCancel($this->lng->txt('cancel'), 'cancel');
            $confirm->addItem('appointments[]', (string) $this->app->getEntryId(), $this->app->getTitle());
            $confirm->setConfirm($this->lng->txt('delete'), 'delete');
            $this->tpl->setContent($confirm->getHTML());
        } else {
            $table = new ilCalendarRecurrenceTableGUI(
                $this->app,
                $this,
                'askDelete'
            );
            $table->init();
            $table->parse();
            $this->tpl->setContent($table->getHTML());
            $this->tpl->setOnScreenMessage('question', $this->lng->txt('cal_delete_app_sure'));
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cal_recurrence_confirm_deletion'));
        }
    }

    protected function delete(): void
    {
        $app_ids = (array) ($this->request->getParsedBody()['appointment_ids'] ?? []);
        if (!$app_ids) {
            $this->logger->dump($app_ids);
            $app_ids = (array) ($this->request->getQueryParams()['app_id'] ?? []);
        }
        if (!$app_ids) {
            $this->ctrl->returnToParent($this);
        }
        foreach ($app_ids as $app_id) {
            $app_id = (int) $app_id;
            $app = new ilCalendarEntry($app_id);
            $app->delete();
            ilCalendarCategoryAssignments::_deleteByAppointmentId($app_id);
            ilCalendarUserNotification::deleteCalendarEntry($app_id);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_deleted_app'), true);
        $this->ctrl->returnToParent($this);
    }

    protected function deleteExclude(bool $a_return = true): void
    {
        $recurrence_ids = (array) ($this->request->getParsedBody()['recurrence_ids'] ?? []);
        $app_id = (int) ($this->request->getQueryParams()['app_id'] ?? 0);
        if (!count($recurrence_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'askDelete');
        }
        if (!$app_id) {
            $this->ctrl->returnToParent($this);
        }
        foreach ($recurrence_ids as $rdate) {
            $exclusion = new ilCalendarRecurrenceExclusion();
            $exclusion->setEntryId($app_id);
            $exclusion->setDate(new ilDate($rdate, IL_CAL_UNIX));
            $exclusion->save();
        }
        if ($a_return) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_deleted_app'), true);
            $this->ctrl->returnToParent($this);
        }
    }

    protected function initTimeZone(): void
    {
        $this->timezone = $this->user->getTimeZone();
    }

    protected function initInitialDate(ilDate $initialDate): void
    {
        $hour = 0;
        if ($this->http->wrapper()->query()->has('hour')) {
            $hour = $this->http->wrapper()->query()->retrieve(
                'hour',
                $this->refinery->kindlyTo()->int()
            );
        }

        if (!$hour) {
            $this->initialDate = clone $initialDate;
            $this->default_fulltime = true;
        } else {
            if ($hour < 10) {
                $time = '0' . $hour . ':00:00';
            } else {
                $time = (int) $hour . ':00:00';
            }
            $this->initialDate = new ilDateTime(
                $initialDate->get(IL_CAL_DATE) . ' ' . $time,
                IL_CAL_DATETIME,
                $this->timezone
            );
            $this->default_fulltime = false;
        }
    }

    protected function initSeed(ilDate $seed): void
    {
        $this->seed = clone $seed;
        $this->default_fulltime = true;
    }

    protected function initAppointment(int $a_app_id = 0): void
    {
        $this->app = new ilCalendarEntry($a_app_id);
        $this->notification = new ilCalendarUserNotification($this->app->getEntryId());

        if (!$a_app_id) {
            $start = clone $this->initialDate;
            $this->app->setStart($start);

            $seed_end = clone $this->initialDate;
            if ($this->default_fulltime) {
                #$seed_end->increment(IL_CAL_DAY,1);
            } else {
                $seed_end->increment(IL_CAL_HOUR, 1);
            }
            $this->app->setEnd($seed_end);
            $this->app->setFullday($this->default_fulltime);

            $this->rec = new ilCalendarRecurrence();
        } else {
            $this->rec = ilCalendarRecurrences::_getFirstRecurrence($this->app->getEntryId());
        }
    }

    protected function load($a_mode, $a_as_milestone = false): ilPropertyFormGUI
    {
        // needed for date handling
        $form = $this->initForm($a_mode, $a_as_milestone);
        $this->form->checkInput();

        if ($a_as_milestone) {
            $this->app->setMilestone(true);
            $this->app->setCompletion((int) $form->getInput('completion'));
        }

        $this->app->setTitle($form->getInput('title'));
        $this->app->setLocation($form->getInput('location'));
        $this->app->setDescription($form->getInput('description'));
        $this->app->enableNotification((bool) $form->getInput('not'));

        if ($a_as_milestone) {    // milestones are always fullday events
            $start = $this->form->getItemByPostVar('event_start');
            $start = $start->getDate();
            $this->app->setFullday(true);
            // for milestones is end date = start date
            $this->app->setStart($start);
            $this->app->setEnd($start);
        } else {
            $period = $this->form->getItemByPostVar('event');
            $start = $period->getStart();
            $end = $period->getEnd();

            $this->app->setFullday($start instanceof ilDate);
            $this->app->setStart($start);
            $this->app->setEnd($end);
        }

        $this->loadNotificationRecipients($form);
        $this->loadRecurrenceSettings($form, $a_as_milestone = false);
        return $form;
    }

    protected function loadNotificationRecipients(ilPropertyFormGUI $form): void
    {
        $this->notification->setRecipients(array());
        $map = [];
        foreach ((array) $form->getInput('notu') as $rcp) {
            $rcp = trim($rcp);
            $usr_id = (int) ilObjUser::_loginExists($rcp);
            if ($rcp === '') {
                continue;
            }
            if (in_array($rcp, $map)) {
                continue;
            }
            $map[] = $rcp;
            if ($usr_id) {
                $this->notification->addRecipient(
                    ilCalendarUserNotification::TYPE_USER,
                    $usr_id
                );
            } else {
                $this->notification->addRecipient(
                    ilCalendarUserNotification::TYPE_EMAIL,
                    0,
                    $rcp
                );
            }
        }
    }

    protected function loadRecurrenceSettings(ilPropertyFormGUI $form, bool $a_as_milestone = false): void
    {
        if ($form->getItemByPostVar('frequence') instanceof ilRecurrenceInputGUI) {
            $this->rec = $form->getItemByPostVar('frequence')->getRecurrence();
        } else {
            $this->rec = new ilCalendarRecurrence();
        }
    }

    protected function saveRecurrenceSettings(): void
    {
        switch ($this->rec->getFrequenceType()) {
            case '':
            case ilCalendarRecurrence::FREQ_NONE:
                // No recurrence => delete if there is an recurrence rule
                if ($this->rec->getRecurrenceId()) {
                    $this->rec->delete();
                }
                break;

            default:
                if ($this->rec->getRecurrenceId()) {
                    $this->rec->update();
                } else {
                    $this->rec->save();
                }
                break;
        }
    }

    protected function createDefaultCalendar(): int
    {
        $cat = new ilCalendarCategory();
        $cat->setColor(ilCalendarCategory::DEFAULT_COLOR);
        $cat->setType(ilCalendarCategory::TYPE_USR);
        $cat->setTitle($this->lng->txt('cal_default_calendar'));
        $cat->setObjId($this->user->getId());

        // delete calendar cache
        ilCalendarCache::getInstance()->deleteUserEntries($this->user->getId());

        return $cat->add();
    }

    /**
     * Register to an appointment
     */
    protected function confirmRegister(): void
    {
        $dstart = 0;
        if ($this->http->wrapper()->query()->has('dstart')) {
            $dstart = $this->http->wrapper()->query()->retrieve(
                'dstart',
                $this->refinery->kindlyTo()->int()
            );
        }
        $dend = 0;
        if ($this->http->wrapper()->query()->has('dend')) {
            $dend = $this->http->wrapper()->query()->retrieve(
                'dend',
                $this->refinery->kindlyTo()->int()
            );
        }

        $app_id = $this->getAppointmentIdFromQuery();
        $entry = new ilCalendarEntry($app_id);
        $start = ilDatePresentation::formatPeriod(
            new ilDateTime($dstart, IL_CAL_UNIX),
            new ilDateTime($dend, IL_CAL_UNIX)
        );

        $conf = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, 'dstart', $dstart);
        $this->ctrl->setParameter($this, 'dend', $dend);

        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('cal_confirm_reg_info'));
        $conf->setConfirm($this->lng->txt('cal_reg_register'), 'register');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');
        $conf->addItem('app_id', (string) $entry->getEntryId(), $entry->getTitle() . ' (' . $start . ')');
        $this->tpl->setContent($conf->getHTML());
    }

    protected function register(): void
    {
        $dstart = 0;
        if ($this->http->wrapper()->query()->has('dstart')) {
            $dstart = $this->http->wrapper()->query()->retrieve(
                'dstart',
                $this->refinery->kindlyTo()->int()
            );
        }
        $dend = 0;
        if ($this->http->wrapper()->query()->has('dend')) {
            $dend = $this->http->wrapper()->query()->retrieve(
                'dend',
                $this->refinery->kindlyTo()->int()
            );
        }
        $app_id = 0;
        if ($this->http->wrapper()->post()->has('app_id')) {
            $app_id = $this->http->wrapper()->post()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $reg = new ilCalendarRegistration($app_id);
        $reg->register(
            $this->user->getId(),
            new ilDateTime($dstart, IL_CAL_UNIX),
            new ilDateTime((int) $dend, IL_CAL_UNIX)
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_reg_registered'), true);
        $this->ctrl->returnToParent($this);
    }

    public function confirmUnregister(): void
    {
        $dstart = 0;
        if ($this->http->wrapper()->query()->has('dstart')) {
            $dstart = $this->http->wrapper()->query()->retrieve(
                'dstart',
                $this->refinery->kindlyTo()->int()
            );
        }
        $dend = 0;
        if ($this->http->wrapper()->query()->has('dend')) {
            $dend = $this->http->wrapper()->query()->retrieve(
                'dend',
                $this->refinery->kindlyTo()->int()
            );
        }

        $app_id = $this->getAppointmentIdFromQuery();
        $entry = new ilCalendarEntry($app_id);
        $start = ilDatePresentation::formatPeriod(
            $dstart = new ilDateTime($dstart, IL_CAL_UNIX),
            $dend = new ilDateTime($dend, IL_CAL_UNIX)
        );

        $this->ctrl->setParameter($this, 'dstart', (int) $dstart);
        $this->ctrl->setParameter($this, 'dend', (int) $dend);

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('cal_confirm_unreg_info'));
        $conf->setConfirm($this->lng->txt('cal_reg_unregister'), 'unregister');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');
        $conf->addItem('app_id', (string) $entry->getEntryId(), $entry->getTitle() . ' (' . $start . ')');

        $this->tpl->setContent($conf->getHTML());
    }

    /**
     * Unregister calendar, was confirmed
     */
    protected function unregister(): void
    {
        $dstart = 0;
        if ($this->http->wrapper()->query()->has('dstart')) {
            $dstart = $this->http->wrapper()->query()->retrieve(
                'dstart',
                $this->refinery->kindlyTo()->int()
            );
        }
        $dend = 0;
        if ($this->http->wrapper()->query()->has('dend')) {
            $dend = $this->http->wrapper()->query()->retrieve(
                'dend',
                $this->refinery->kindlyTo()->int()
            );
        }
        $app_id = 0;
        if ($this->http->wrapper()->post()->has('app_id')) {
            $app_id = $this->http->wrapper()->post()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $reg = new ilCalendarRegistration($app_id);
        $reg->unregister(
            $this->user->getId(),
            new ilDateTime((int) $dstart, IL_CAL_UNIX),
            new ilDateTime((int) $dend, IL_CAL_UNIX)
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_reg_unregistered'), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Confirmation screen for booking of consultation appointment
     */
    public function book(): void
    {
        $entry_id = $this->getAppointmentIdFromQuery();
        $this->ctrl->saveParameter($this, 'app_id');

        $entry = new ilCalendarEntry($entry_id);
        $booking = new \ilBookingEntry($entry->getContextId());
        $user = $booking->getObjId();

        $form = $this->initFormConfirmBooking();
        $form->getItemByPostVar('date')->setValue(ilDatePresentation::formatPeriod(
            $entry->getStart(),
            $entry->getEnd()
        ));
        $form->getItemByPostVar('title')->setValue($entry->getTitle() . " (" . ilObjUser::_lookupFullname($user) . ')');

        $this->tpl->setContent($form->getHTML());
    }

    protected function initFormConfirmBooking(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('bookconfirmed', $this->lng->txt('cal_confirm_booking'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $date = new ilNonEditableValueGUI($this->lng->txt('appointment'), 'date');
        $form->addItem($date);

        $title = new ilNonEditableValueGUI($this->lng->txt('title'), 'title');
        $form->addItem($title);

        $message = new ilTextAreaInputGUI($this->lng->txt('cal_ch_booking_message_tbl'), 'comment');
        $message->setRows(5);
        $form->addItem($message);

        return $form;
    }

    /**
     * Book consultation appointment, was confirmed
     */
    public function bookconfirmed()
    {
        $entry = $this->getAppointmentIdFromQuery();
        $form = $this->initFormConfirmBooking();
        if ($form->checkInput()) {
            // check if appointment is bookable
            $cal_entry = new ilCalendarEntry($entry);

            $booking = new ilBookingEntry($cal_entry->getContextId());

            if (!$booking->isAppointmentBookableForUser($entry, $GLOBALS['DIC']['ilUser']->getId())) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cal_booking_failed_info'), true);
                $this->ctrl->returnToParent($this);
            }

            ilConsultationHourUtils::bookAppointment($this->user->getId(), $entry);
            ilBookingEntry::writeBookingMessage($entry, $this->user->getId(), $form->getInput('comment'));
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_booking_confirmed'), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Confirmation screen to cancel consultation appointment or ressource booking
     * depends on calendar category
     */
    public function cancelBooking(): void
    {
        $entry = $this->getAppointmentIdFromQuery();
        $entry = new ilCalendarEntry($entry);

        $category = $this->calendarEntryToCategory($entry);
        if ($category->getType() == ilCalendarCategory::TYPE_CH) {
            $booking = new ilBookingEntry($entry->getContextId());
            if (!$booking->hasBooked($entry->getEntryId())) {
                $this->ctrl->returnToParent($this);
                return;
            }

            $entry_title = ' ' . $entry->getTitle() . " (" . ilObjUser::_lookupFullname($booking->getObjId()) . ')';
        } elseif ($category->getType() == ilCalendarCategory::TYPE_BOOK) {
            $entry_title = ' ' . $entry->getTitle();
        } else {
            $this->ctrl->returnToParent($this);
            return;
        }

        $title = ilDatePresentation::formatPeriod($entry->getStart(), $entry->getEnd());

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('cal_cancel_booking_info'));
        $conf->setConfirm($this->lng->txt('cal_cancel_booking'), 'cancelconfirmed');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');
        $conf->addItem('app_id', (string) $entry->getEntryId(), $title . ' - ' . $entry_title);

        $this->tpl->setContent($conf->getHTML());
    }

    /**
     * Cancel consultation appointment or ressource booking, was confirmed
     * This will delete the calendar entry
     */
    public function cancelConfirmed(): void
    {
        $app_id = 0;
        if ($this->http->wrapper()->post()->has('app_id')) {
            $app_id = $this->http->wrapper()->post()->retrieve(
                'app_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $entry = new ilCalendarEntry($app_id);
        $category = $this->calendarEntryToCategory($entry);
        if ($category->getType() == ilCalendarCategory::TYPE_CH) {
            // find cloned calendar entry in user calendar
            $apps = ilConsultationHourAppointments::getAppointmentIds(
                $this->user->getId(),
                $entry->getContextId(),
                $entry->getStart(),
                ilCalendarCategory::TYPE_CH,
                false
            );

            // Fix for wrong, old entries
            foreach ($apps as $own_app) {
                $ref_entry = new ilCalendarEntry($own_app);
                $ref_entry->delete();
            }

            $booking = new ilBookingEntry($entry->getContextId());
            $booking->cancelBooking($entry->getEntryId());

        // do NOT delete original entry
        } elseif ($category->getType() == ilCalendarCategory::TYPE_BOOK) {
            $booking = new ilBookingReservation($entry->getContextId());
            $booking->setStatus(ilBookingReservation::STATUS_CANCELLED);
            $booking->update();

            $entry->delete();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_cancel_booking_confirmed'), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Get category object of given calendar entry
     */
    protected function calendarEntryToCategory(ilCalendarEntry $entry): ilCalendarCategory
    {
        $assignment = new ilCalendarCategoryAssignments($entry->getEntryId());
        $assignment = $assignment->getFirstAssignment();
        return new ilCalendarCategory($assignment);
    }
}
