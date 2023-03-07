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

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\EmployeeTalk\UI\ControlFlowCommandHandler;
use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\Modules\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\Modules\EmployeeTalk\Talk\Repository\EmployeeTalkRepository;
use ILIAS\Modules\EmployeeTalk\Talk\EmployeeTalkPeriod;
use ILIAS\EmployeeTalk\Service\EmployeeTalkEmailNotificationService;
use ILIAS\EmployeeTalk\Service\VCalendarFactory;
use ILIAS\EmployeeTalk\Service\EmployeeTalkEmailNotification;

/**
 * Class ilEmployeeTalkAppointmentGUI
 *
 * @ilCtrl_IsCalledBy ilEmployeeTalkAppointmentGUI: ilObjEmployeeTalkGUI
 */
final class ilEmployeeTalkAppointmentGUI implements ControlFlowCommandHandler
{
    public const EDIT_MODE_APPOINTMENT = 'appointment';
    public const EDIT_MODE_SERIES = 'series';
    public const EDIT_MODE = 'edit-mode';

    private ilGlobalTemplateInterface $template;
    private ilLanguage $language;
    private ilCtrl $controlFlow;
    private HttpServices $http;
    private Refinery $refinery;
    private ilTabsGUI $tabs;
    private ilObjEmployeeTalk $talk;

    /**
     * ilEmployeeTalkAppointmentGUI constructor.
     * @param ilGlobalTemplateInterface $template
     * @param ilLanguage                $language
     * @param ilCtrl                    $controlFlow
     * @param ilTabsGUI                 $tabs
     * @param ilObjEmployeeTalk         $talk
     */
    public function __construct(
        ilGlobalTemplateInterface $template,
        ilLanguage $language,
        ilCtrl $controlFlow,
        HttpServices $http,
        Refinery $refinery,
        ilTabsGUI $tabs,
        ilObjEmployeeTalk $talk
    ) {
        $this->template = $template;
        $this->language = $language;
        $this->controlFlow = $controlFlow;
        $this->http = $http;
        $this->refinery = $refinery;
        $this->tabs = $tabs;
        $this->talk = $talk;

        $this->language->loadLanguageModule('cal');
    }

    public function executeCommand(): void
    {
        $cmd = $this->controlFlow->getCmd(ControlFlowCommand::DEFAULT);
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        } else {
            throw new ilEmployeeTalkAppointmentException(
                'No ref_id found'
            );
        }

        $backClass = strtolower(ilObjEmployeeTalkGUI::class);
        $this->controlFlow->setParameterByClass($backClass, 'ref_id', $ref_id);
        $this->tabs->setBackTarget($this->language->txt('back'), $this->controlFlow->getLinkTargetByClass(strtolower(ilObjEmployeeTalkGUI::class), ControlFlowCommand::DEFAULT));

        switch ($this->editMode()) {
            case self::EDIT_MODE_SERIES:
                $this->executeSeriesCommand($cmd);
                break;
            case self::EDIT_MODE_APPOINTMENT:
                $this->executeAppointmentCommand($cmd);
                break;
            default:
                $this->controlFlow->redirectByClass(strtolower(ilObjEmployeeTalkGUI::class), ControlFlowCommand::DEFAULT);
                break;
        }
    }

    private function executeSeriesCommand(string $cmd): bool
    {
        $this->template->setTitle($this->language->txt('etal_date_series_edit'));

        switch ($cmd) {
            case ControlFlowCommand::UPDATE_INDEX:
                $this->editSeries();
                return true;
            case ControlFlowCommand::UPDATE:
                $this->updateSeries();
                return true;
        }

        return false;
    }

    private function executeAppointmentCommand(string $cmd): bool
    {
        $this->template->setTitle($this->language->txt('etal_date_appointment_edit'));

        switch ($cmd) {
            case ControlFlowCommand::UPDATE_INDEX:
                $this->editAppointment();
                return true;
            case ControlFlowCommand::UPDATE:
                $this->updateAppointment();
                return true;
        }

        return false;
    }

    private function editSeries(): void
    {
        $form = $this->initSeriesEditForm($this->talk->getData());

        $this->template->setContent($form->getHTML());
    }

    private function updateSeries(): void
    {
        $form = $this->initSeriesEditForm();
        if ($form->checkInput()) {
            $reoccurrence = $this->loadRecurrenceSettings($form);
            $parent = $this->talk->getParent();
            $this->deletePendingTalks($parent);
            $this->createRecurringTalks($form, $reoccurrence, $parent);

            $this->template->setOnScreenMessage('success', $this->language->txt('saved_successfully'), true);
        }

        $this->controlFlow->redirectToURL(
            $this->controlFlow->getLinkTargetByClass(
                strtolower(ilEmployeeTalkMyStaffListGUI::class),
                ControlFlowCommand::UPDATE_INDEX
            ) . $this->getEditModeParameter(ilEmployeeTalkAppointmentGUI::EDIT_MODE_SERIES)
        );
    }

    private function initTalkEditForm(?EmployeeTalk $employeeTalk = null): ilPropertyFormGUI
    {
        // Init dom events or ui will break on page load
        ilYuiUtil::initDomEvent();

        $form = new ilPropertyFormGUI();
        $editMode = $this->getEditModeParameter(ilEmployeeTalkAppointmentGUI::EDIT_MODE_APPOINTMENT);
        $form->setFormAction($this->controlFlow->getFormActionByClass(
            strtolower(self::class)
        ) . $editMode);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->language->txt('appointment'));
        $form->addItem($header);

        $dur = new ilDateDurationInputGUI($this->language->txt('cal_fullday'), 'event');
        $dur->setRequired(true);
        $dur->setShowTime(true);

        if ($employeeTalk !== null) {
            $dur->enableToggleFullTime(
                $this->language->txt('cal_fullday_title'),
                $employeeTalk->isAllDay()
            );

            $dur->setStart($employeeTalk->getStartDate());
            $dur->setEnd($employeeTalk->getEndDate());
        }

        $form->addItem($dur);
        $form->addCommandButton(ControlFlowCommand::UPDATE, $this->language->txt('save'), 'etal_date_save');

        return $form;
    }

    private function initSeriesEditForm(?EmployeeTalk $employeeTalk = null): ilPropertyFormGUI
    {
        // Init dom events or ui will break on page load
        ilYuiUtil::initDomEvent();

        $form = new ilPropertyFormGUI();
        $editMode = $this->getEditModeParameter(ilEmployeeTalkAppointmentGUI::EDIT_MODE_SERIES);
        $form->setFormAction($this->controlFlow->getFormActionByClass(
            strtolower(self::class)
        ) . $editMode);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->language->txt('appointments'));
        $form->addItem($header);

        $dur = new ilDateDurationInputGUI($this->language->txt('cal_fullday'), 'event');
        $dur->setRequired(true);
        $dur->setShowTime(true);

        if ($employeeTalk !== null) {
            $dur->enableToggleFullTime(
                $this->language->txt('cal_fullday_title'),
                $employeeTalk->isAllDay()
            );

            $dur->setStart($employeeTalk->getStartDate());
            $dur->setEnd($employeeTalk->getEndDate());
        }

        $form->addItem($dur);

        // Recurrence
        $cal = new ilRecurrenceInputGUI($this->language->txt('cal_recurrences'), "frequence");
        $event = new ilCalendarRecurrence();

        $cal->allowUnlimitedRecurrences(false);
        $cal->setRecurrence($event);

        $form->addItem($cal);
        $form->addCommandButton(ControlFlowCommand::UPDATE, $this->language->txt('save'), 'etal_series_save');

        return $form;
    }

    private function editAppointment(): void
    {
        $form = $this->initTalkEditForm($this->talk->getData());

        $this->template->setContent($form->getHTML());
    }

    private function updateAppointment(): void
    {
        $form = $this->initTalkEditForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            /**
             * @var ilDateDurationInputGUI $dateTimeInput
             */
            $dateTimeInput = $form->getItemByPostVar('event');
            $tgl = $form->getInput('event')['tgl'] ?? 0;
            ['start' => $start, 'end' => $end] = $dateTimeInput->getValue();
            if (intval($tgl)) {
                $start_date = new ilDate($start, IL_CAL_UNIX);
                $end_date = new ilDate($end, IL_CAL_UNIX);
            } else {
                $start_date = new ilDateTime($start, IL_CAL_UNIX, ilTimeZone::UTC);
                $end_date = new ilDateTime($end, IL_CAL_UNIX, ilTimeZone::UTC);
            }

            $data = $this->talk->getData();
            $data->setAllDay(boolval(intval($tgl)));
            $data->setStartDate($start_date);
            $data->setEndDate($end_date);
            $data->setStandalone(true);

            $this->talk->setData($data);
            $this->talk->update();

            $this->sendNotification([$this->talk]);

            $this->template->setOnScreenMessage('success', $this->language->txt('saved_successfully'), true);
        }


        $this->controlFlow->redirectToURL(
            $this->controlFlow->getLinkTargetByClass(
                strtolower(self::class),
                ControlFlowCommand::UPDATE_INDEX
            ) . $this->getEditModeParameter(ilEmployeeTalkAppointmentGUI::EDIT_MODE_APPOINTMENT)
        );
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

    private function editMode(): string
    {
        return filter_input(INPUT_GET, self::EDIT_MODE, FILTER_CALLBACK, ['options' => function (string $value) {
            if ($value === self::EDIT_MODE_SERIES || $value === self::EDIT_MODE_APPOINTMENT) {
                return $value;
            }

            return 'invalid';
        }]) ?? 'invalid';
    }

    private function getEditModeParameter(string $mode): string
    {
        return '&' . ilEmployeeTalkAppointmentGUI::EDIT_MODE . '=' . $mode;
    }

    /**
     * load recurrence settings
     *
     * @access protected
     * @return
     */
    private function loadRecurrenceSettings(ilPropertyFormGUI $form): ilCalendarRecurrence
    {
        $rec = new ilCalendarRecurrence();

        switch ($form->getInput('frequence')) {
            case ilCalendarRecurrence::FREQ_DAILY:
                $rec->setFrequenceType($form->getInput('frequence'));
                $rec->setInterval((int) $form->getInput('count_DAILY'));
                break;

            case ilCalendarRecurrence::FREQ_WEEKLY:
                $rec->setFrequenceType($form->getInput('frequence'));
                $rec->setInterval((int) $form->getInput('count_WEEKLY'));
                if (is_array($form->getInput('byday_WEEKLY'))) {
                    $rec->setBYDAY(ilUtil::stripSlashes(implode(',', $form->getInput('byday_WEEKLY'))));
                }
                break;

            case ilCalendarRecurrence::FREQ_MONTHLY:
                $rec->setFrequenceType($form->getInput('frequence'));
                $rec->setInterval((int) $form->getInput('count_MONTHLY'));
                switch ((int) $form->getInput('subtype_MONTHLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        switch ((int) $form->getInput('monthly_byday_day')) {
                            case 8:
                                // Weekday
                                $rec->setBYSETPOS($form->getInput('monthly_byday_num'));
                                $rec->setBYDAY('MO,TU,WE,TH,FR');
                                break;

                            case 9:
                                // Day of month
                                $rec->setBYMONTHDAY($form->getInput('monthly_byday_num'));
                                break;

                            default:
                                $rec->setBYDAY(($form->getInput('monthly_byday_num') . $form->getInput('monthly_byday_day')));
                                break;
                        }
                        break;

                    case 2:
                        $rec->setBYMONTHDAY($form->getInput('monthly_bymonthday'));
                        break;
                }
                break;

            case ilCalendarRecurrence::FREQ_YEARLY:
                $rec->setFrequenceType($form->getInput('frequence'));
                $rec->setInterval((int) $form->getInput('count_YEARLY'));
                switch ((int) $form->getInput('subtype_YEARLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        $rec->setBYMONTH($form->getInput('yearly_bymonth_byday'));
                        $rec->setBYDAY(($form->getInput('yearly_byday_num') . $form->getInput('yearly_byday')));
                        break;

                    case 2:
                        $rec->setBYMONTH($form->getInput('yearly_bymonth_by_monthday'));
                        $rec->setBYMONTHDAY($form->getInput('yearly_bymonthday'));
                        break;
                }
                break;
        }

        // UNTIL
        switch ((int) $form->getInput('until_type')) {
            case 1:
                $rec->setFrequenceUntilDate(null);
                // nothing to do
                break;

            case 2:
                $rec->setFrequenceUntilDate(null);
                $rec->setFrequenceUntilCount((int) $form->getInput('count'));
                break;

            case 3:
                $frequence = $form->getItemByPostVar('frequence');
                $end = $frequence->getRecurrence()->getFrequenceUntilDate();
                $rec->setFrequenceUntilCount(0);
                $rec->setFrequenceUntilDate($end);
                break;
        }

        return $rec;
    }

    /**
     * create recurring talks
     * @param ilPropertyFormGUI       $form
     * @param ilCalendarRecurrence    $recurrence
     * @param ilObjEmployeeTalkSeries $series
     *
     * @return bool true if successful otherwise false
     * @throws ilDateTimeException
     */
    private function createRecurringTalks(ilPropertyFormGUI $form, ilCalendarRecurrence $recurrence, ilObjEmployeeTalkSeries $series): bool
    {
        $data = $this->loadEtalkData($form);

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
        $talkSession->setTitle($this->talk->getTitle());
        $talkSession->setDescription($this->talk->getLongDescription());
        $talkSession->setType(ilObjEmployeeTalk::TYPE);
        $talkSession->setOwner($series->getOwner());
        $talkSession->create();

        $talkSession->createReference();
        $talkSession->putInTree($series->getRefId());

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
            $cloneObject = $talkSession->cloneObject($series->getRefId());
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

            $cloneObject->setOwner($series->getOwner());
            $cloneObject->updateOwner();

            $talks[] = $cloneObject;
        }

        $this->sendNotification($talks);

        return true;
    }

    private function deletePendingTalks(ilObjEmployeeTalkSeries $series): void
    {
        $subItems = $series->getSubItems()['_all'];

        foreach ($subItems as $subItem) {
            if ($subItem['type'] === 'etal') {
                $refId = intval($subItem['ref_id']);
                $talk = new ilObjEmployeeTalk($refId, true);
                $talkData = $talk->getData();
                if ($talkData->isStandalone() || $talkData->isCompleted()) {
                    continue;
                }

                $talk->delete();
            }
        }
    }

    private function loadEtalkData(ilPropertyFormGUI $form): EmployeeTalk
    {
        $data = $this->talk->getData();
        $tgl = $form->getInput('event')['tgl'] ?? 0;

        /**
         * @var ilDateDurationInputGUI $dateTimeInput
         */
        $dateTimeInput = $form->getItemByPostVar('event');
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
            $data->getLocation(),
            $data->getEmployee(),
            false,
            false
        );
    }
}
