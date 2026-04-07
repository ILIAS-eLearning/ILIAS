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

namespace ILIAS\Cron\Job\Manager\UI;

use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Factory;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobEntity;
use ilLanguage;
use ilStr;
use ILIAS\Cron\Job\JobResult;
use ilUIService;

class JobTableFilterMediator
{
    private const string FILTER_PROPERTY_NAME_TITLE_AND_DESC = 'title';
    private const string FILTER_PROPERTY_NAME_COMPONENT = 'component';
    private const string FILTER_PROPERTY_NAME_SCHEDULE = 'schedule';
    private const string FILTER_PROPERTY_NAME_STATUS = 'status';
    private const string FILTER_PROPERTY_NAME_RESULT = 'result';

    private const int FILTER_STATUS_ACTIVE = 1;
    private const int FILTER_STATUS_INACTIVE = 2;

    public function __construct(
        private readonly \ILIAS\Cron\Job\JobCollection $items,
        private readonly Factory $uiFactory,
        private readonly ilUIService $uiService,
        private readonly ilLanguage $lng
    ) {
    }

    public function filter(string $action): Standard
    {
        $componentOptions = array_unique(
            array_map(function (JobEntity $entity): string {
                if ($entity->isPlugin()) {
                    return $this->lng->txt('cmps_plugin') . '/' . $entity->getComponent();
                }

                return $entity->getComponent();
            }, $this->items->toArray())
        );
        asort($componentOptions);

        $title_and_desc = $this->uiFactory->input()->field()->text(
            $this->lng->txt('title') . ' / ' . $this->lng->txt('description')
        );
        $components = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_component'),
            array_combine($componentOptions, $componentOptions)
        );
        $schedule = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_schedule'),
            [
                (string) JobScheduleType::DAILY->value => $this->lng->txt('cron_schedule_daily'),
                (string) JobScheduleType::WEEKLY->value => $this->lng->txt('cron_schedule_weekly'),
                (string) JobScheduleType::MONTHLY->value => $this->lng->txt('cron_schedule_monthly'),
                (string) JobScheduleType::QUARTERLY->value => $this->lng->txt('cron_schedule_quarterly'),
                (string) JobScheduleType::YEARLY->value => $this->lng->txt('cron_schedule_yearly'),
                (string) JobScheduleType::IN_MINUTES->value => \sprintf(
                    $this->lng->txt('cron_schedule_in_minutes'),
                    'x'
                ),
                (string) JobScheduleType::IN_HOURS->value => \sprintf($this->lng->txt('cron_schedule_in_hours'), 'x'),
                (string) JobScheduleType::IN_DAYS->value => \sprintf($this->lng->txt('cron_schedule_in_days'), 'x')
            ]
        );
        $status = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_status'),
            [
                (string) self::FILTER_STATUS_ACTIVE => $this->lng->txt('cron_status_active'),
                (string) self::FILTER_STATUS_INACTIVE => $this->lng->txt('cron_status_inactive'),
            ]
        );
        $result = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_result'),
            [
                (string) JobResult::STATUS_INVALID_CONFIGURATION => $this->lng->txt(
                    'cron_result_status_invalid_configuration'
                ),
                (string) JobResult::STATUS_NO_ACTION => $this->lng->txt(
                    'cron_result_status_no_action'
                ),
                (string) JobResult::STATUS_OK => $this->lng->txt(
                    'cron_result_status_ok'
                ),
                (string) JobResult::STATUS_CRASHED => $this->lng->txt(
                    'cron_result_status_crashed'
                ),
                (string) JobResult::STATUS_RESET => $this->lng->txt(
                    'cron_result_status_reset'
                ),
                (string) JobResult::STATUS_FAIL => $this->lng->txt(
                    'cron_result_status_fail'
                ),
            ]
        );

        $fields = [
            self::FILTER_PROPERTY_NAME_TITLE_AND_DESC => $title_and_desc,
            self::FILTER_PROPERTY_NAME_COMPONENT => $components,
            self::FILTER_PROPERTY_NAME_SCHEDULE => $schedule,
            self::FILTER_PROPERTY_NAME_STATUS => $status,
            self::FILTER_PROPERTY_NAME_RESULT => $result,
        ];

        $initially_rendered = array_map(
            // https://mantis.ilias.de/view.php?id=47562
            static fn(string $key): bool => true,
            array_keys($fields)
        );

        return $this->uiService->filter()->standard(
            'cron_job_adm_table',
            $action,
            $fields,
            $initially_rendered,
            true,
            true
        );
    }

    public function filteredJobs(Standard $filter): \ILIAS\Cron\Job\JobCollection
    {
        $filter_values = $this->uiService->filter()->getData($filter);

        return $this->items->filter(function (JobEntity $entity) use ($filter_values): bool {
            if (isset($filter_values[self::FILTER_PROPERTY_NAME_TITLE_AND_DESC]) &&
                \is_string($filter_values[self::FILTER_PROPERTY_NAME_TITLE_AND_DESC]) &&
                $filter_values[self::FILTER_PROPERTY_NAME_TITLE_AND_DESC] !== '') {
                $title_and_desc_filter_value = $filter_values[self::FILTER_PROPERTY_NAME_TITLE_AND_DESC];
                if (ilStr::strIPos($entity->getEffectiveTitle(), $title_and_desc_filter_value) === false &&
                    ilStr::strIPos($entity->getJob()->getDescription(), $title_and_desc_filter_value) === false) {
                    return false;
                }
            }

            if (isset($filter_values[self::FILTER_PROPERTY_NAME_COMPONENT]) &&
                \is_string($filter_values[self::FILTER_PROPERTY_NAME_COMPONENT]) &&
                $filter_values[self::FILTER_PROPERTY_NAME_COMPONENT] !== '') {
                $component = $entity->getComponent();
                if ($entity->isPlugin()) {
                    $component = $this->lng->txt('cmps_plugin') . '/' . $component;
                }

                if ($filter_values[self::FILTER_PROPERTY_NAME_COMPONENT] !== $component) {
                    return false;
                }
            }

            if (isset($filter_values[self::FILTER_PROPERTY_NAME_SCHEDULE]) &&
                \is_string($filter_values[self::FILTER_PROPERTY_NAME_SCHEDULE]) &&
                $filter_values[self::FILTER_PROPERTY_NAME_SCHEDULE] !== '') {
                if ((int) $filter_values[self::FILTER_PROPERTY_NAME_SCHEDULE] !== $entity->getEffectiveScheduleType(
                )->value) {
                    return false;
                }
            }

            if (isset($filter_values[self::FILTER_PROPERTY_NAME_STATUS]) &&
                \is_string($filter_values[self::FILTER_PROPERTY_NAME_STATUS]) &&
                $filter_values[self::FILTER_PROPERTY_NAME_STATUS] !== '') {
                if ((int) $filter_values[self::FILTER_PROPERTY_NAME_STATUS] === self::FILTER_STATUS_ACTIVE &&
                    !$entity->getJobStatus()) {
                    return false;
                }

                if (
                    (int) $filter_values[self::FILTER_PROPERTY_NAME_STATUS] === self::FILTER_STATUS_INACTIVE &&
                    $entity->getJobStatus()
                ) {
                    return false;
                }
            }

            if (isset($filter_values[self::FILTER_PROPERTY_NAME_RESULT]) &&
                \is_string($filter_values[self::FILTER_PROPERTY_NAME_RESULT]) &&
                $filter_values[self::FILTER_PROPERTY_NAME_RESULT] !== '') {
                if ((int) $filter_values[self::FILTER_PROPERTY_NAME_RESULT] !== $entity->getJobResultStatus()) {
                    return false;
                }
            }

            return true;
        });
    }
}
