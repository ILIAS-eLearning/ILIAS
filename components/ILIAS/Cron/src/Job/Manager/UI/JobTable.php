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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobRepository;
use ILIAS\Cron\Job\JobEntity;
use ILIAS\Cron\Job\Collection\OrderedJobEntities;
use ILIAS\Data\URI;

class JobTable implements \ILIAS\UI\Component\Table\DataRetrieval
{
    private readonly \ILIAS\UI\URLBuilder $url_builder;
    private readonly \ILIAS\UI\URLBuilderToken $action_parameter_token;
    private readonly \ILIAS\UI\URLBuilderToken $row_id_token;

    /**
     * @param list<string> $table_action_namespace
     */
    public function __construct(
        URI $form_action,
        array $table_action_namespace,
        string $table_action_param_name,
        string $table_row_identifier_name,
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \Psr\Http\Message\ServerRequestInterface $request,
        private readonly \ilLanguage $lng,
        private readonly \ILIAS\Cron\Job\JobCollection $job_collection,
        private readonly JobRepository $job_repository,
        private readonly bool $mayWrite = false
    ) {
        [
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token
        ] = (new \ILIAS\UI\URLBuilder($form_action))->acquireParameters(
            $table_action_namespace,
            $table_action_param_name,
            $table_row_identifier_name
        );
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        foreach ($this->getRecords($range, $order) as $item) {
            $record = [
                'title' => $this->formatTitle($item),
                'component' => $this->formatComponent($item),
                'schedule' => $this->formatSchedule($item),
                'status' => (bool) $item->getJobStatus(),
                'status_info' => $this->formatStatusInfo($item),
                'result' => $this->formatResult($item),
                'result_info' => $this->formatResultInfo($item),
                'last_run' => $this->formatLastRun($item)
            ];

            if ($item->getJob()->hasFlexibleSchedule()) {
                if ($item->getScheduleType() === null) {
                    $this->job_repository->updateJobSchedule(
                        $item->getJob(),
                        $item->getEffectiveScheduleType(),
                        $item->getEffectiveScheduleValue()
                    );
                }
            } elseif ($item->getScheduleType() !== null) {
                $this->job_repository->updateJobSchedule($item->getJob(), null, null);
            }

            $actions_executable = $this->mayWrite && !$item->getRunningTimestamp();
            $is_crashed = \ILIAS\Cron\Job\JobResult::STATUS_CRASHED === $item->getJobResultStatus();
            $is_acivated = (bool) $item->getJobStatus();

            $may_reset = $actions_executable && $is_crashed;
            $may_activate = $actions_executable && !$is_crashed && !$is_acivated;
            $may_deactivate = $actions_executable && !$is_crashed && $is_acivated;
            $may_run = $actions_executable && !$is_crashed && $is_acivated && $item->getJob()->isManuallyExecutable();
            $may_edit = $actions_executable && (
                $item->getJob()->hasFlexibleSchedule() || $item->getJob()->hasCustomSettings()
            );

            yield $row_builder
                ->buildDataRow($item->getEffectiveJobId(), $record)
                ->withDisabledAction('run', !$may_run)
                ->withDisabledAction('activate', !$may_activate)
                ->withDisabledAction('deactivate', !$may_deactivate)
                ->withDisabledAction('reset', !$may_reset)
                ->withDisabledAction('edit', !$may_edit);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return \count($this->job_collection);
    }

    /**
     * @return list<JobEntity>
     */
    private function getRecords(\ILIAS\Data\Range $range, \ILIAS\Data\Order $order): array
    {
        [$order_field, $order_direction] = $order->join([], static function ($ret, $key, $value) {
            return [$key, $value];
        });

        $collection = new OrderedJobEntities(
            $this->job_collection,
            match ($order_field) {
                'title' => OrderedJobEntities::ORDER_BY_NAME,
                'status' => OrderedJobEntities::ORDER_BY_STATUS,
                default => function (JobEntity $left, JobEntity $right) use ($order_field): int {
                    if ($order_field === 'component') {
                        return \ilStr::strCmp($this->formatComponent($left), $this->formatComponent($right));
                    }

                    if ($order_field === 'schedule') {
                        return \ilStr::strCmp($this->formatSchedule($left), $this->formatSchedule($right));
                    }

                    if ($order_field === 'result') {
                        return \ilStr::strCmp($this->formatResult($left), $this->formatResult($right));
                    }

                    if ($order_field === 'last_run') {
                        $left_last_run = 0;
                        if ($left->getRunningTimestamp()) {
                            $left_last_run = strtotime('+1year', $left->getRunningTimestamp());
                        } elseif ($left->getJobResultTimestamp()) {
                            $left_last_run = $left->getJobResultTimestamp();
                        }

                        $right_last_run = 0;
                        if ($right->getRunningTimestamp()) {
                            $right_last_run = strtotime('+1year', $right->getRunningTimestamp());
                        } elseif ($right->getJobResultTimestamp()) {
                            $right_last_run = $right->getJobResultTimestamp();
                        }

                        return $left_last_run <=> $right_last_run;
                    }

                    return 0;
                }
            },
            $order_direction === \ILIAS\Data\Order::DESC
        );

        $records = \array_slice($collection->toArray(), $range->getStart(), $range->getLength());

        return $records;
    }

    private function formatTitle(JobEntity $entity): string
    {
        $title = implode('', [
            '<span class="cron-title" id="job-' . $entity->getEffectiveJobId() . '">',
            $entity->getEffectiveTitle(),
            '</span>'
        ]);
        if ($entity->getJob()->getDescription()) {
            $title .= implode('', [
                '<div class="il_Description_no_margin">',
                $entity->getJob()->getDescription(),
                '</div>'
            ]);
        }

        return $title;
    }

    private function formatSchedule(JobEntity $entity): string
    {
        $schedule = match ($entity->getEffectiveScheduleType()) {
            JobScheduleType::DAILY => $this->lng->txt('cron_schedule_daily'),
            JobScheduleType::WEEKLY => $this->lng->txt('cron_schedule_weekly'),
            JobScheduleType::MONTHLY => $this->lng->txt('cron_schedule_monthly'),
            JobScheduleType::QUARTERLY => $this->lng->txt('cron_schedule_quarterly'),
            JobScheduleType::YEARLY => $this->lng->txt('cron_schedule_yearly'),
            JobScheduleType::IN_MINUTES => \sprintf(
                $this->lng->txt('cron_schedule_in_minutes'),
                $entity->getEffectiveScheduleValue()
            ),
            JobScheduleType::IN_HOURS => \sprintf(
                $this->lng->txt('cron_schedule_in_hours'),
                $entity->getEffectiveScheduleValue()
            ),
            JobScheduleType::IN_DAYS => \sprintf(
                $this->lng->txt('cron_schedule_in_days'),
                $entity->getEffectiveScheduleValue()
            )
        };

        return $schedule;
    }

    public function formatComponent(JobEntity $entity): string
    {
        $component = $entity->getComponent();
        if ($entity->isPlugin()) {
            $component = $this->lng->txt('cmps_plugin') . '/' . $component;
        }

        return $component;
    }

    private function formatStatusInfo(JobEntity $entity): string
    {
        $status_info = [];
        if ($entity->getJobStatusTimestamp()) {
            $status_info[] = \ilDatePresentation::formatDate(
                new \ilDateTime($entity->getJobStatusTimestamp(), IL_CAL_UNIX)
            );
        }

        if ($entity->getJobStatusType()) {
            $status_info[] = \ilUserUtil::getNamePresentation($entity->getJobStatusUsrId());
        } else {
            $status_info[] = $this->lng->txt('cron_changed_by_crontab');
        }

        return implode('<br />', $status_info);
    }

    private function formatResult(JobEntity $entity): string
    {
        $result = '-';
        if ($entity->getJobResultStatus()) {
            switch ($entity->getJobResultStatus()) {
                case \ILIAS\Cron\Job\JobResult::STATUS_INVALID_CONFIGURATION:
                    $result = $this->lng->txt('cron_result_status_invalid_configuration');
                    break;

                case \ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION:
                    $result = $this->lng->txt('cron_result_status_no_action');
                    break;

                case \ILIAS\Cron\Job\JobResult::STATUS_OK:
                    $result = $this->lng->txt('cron_result_status_ok');
                    break;

                case \ILIAS\Cron\Job\JobResult::STATUS_CRASHED:
                    $result = $this->lng->txt('cron_result_status_crashed');
                    break;

                case \ILIAS\Cron\Job\JobResult::STATUS_RESET:
                    $result = $this->lng->txt('cron_result_status_reset');
                    break;

                case \ILIAS\Cron\Job\JobResult::STATUS_FAIL:
                    $result = $this->lng->txt('cron_result_status_fail');
                    break;
            }
        }

        return $result;
    }

    private function formatResultInfo(JobEntity $entity): string
    {
        $result_info = [];
        if ($entity->getJobResultDuration()) {
            $result_info[] = ($entity->getJobResultDuration() / 1000) . ' sec';
        }

        // #23391 / #11866
        $resultCode = $entity->getJobResultCode();
        if (\in_array($resultCode, \ILIAS\Cron\Job\JobResult::getCoreCodes(), true)) {
            $result_info[] = $this->lng->txt('cro_job_rc_' . $resultCode);
        } elseif ($entity->getJobResultMessage()) {
            $result_info[] = $entity->getJobResultMessage();
        }

        if (\defined('DEVMODE') && DEVMODE && $resultCode) {
            $result_info[] = $resultCode;
        }

        if ($entity->getJobResultType()) {
            $result_info[] = \ilUserUtil::getNamePresentation($entity->getJobResultUsrId());
        } else {
            $result_info[] = $this->lng->txt('cron_changed_by_crontab');
        }

        return implode('<br />', $result_info);
    }

    private function formatLastRun(JobEntity $entity): string
    {
        $last_run = null;
        if ($entity->getRunningTimestamp()) {
            $last_run = strtotime('+1year', $entity->getRunningTimestamp());
        } elseif ($entity->getJobResultTimestamp()) {
            $last_run = $entity->getJobResultTimestamp();
        }

        if ($last_run > time()) {
            $last_run = $this->lng->txt('cron_running_since') . ' ' .
                \ilDatePresentation::formatDate(new \ilDateTime($entity->getRunningTimestamp(), IL_CAL_UNIX));

            // job has pinged
            if ($entity->getAliveTimestamp() !== $entity->getRunningTimestamp()) {
                $last_run .= '<br />(Ping: ' .
                    \ilDatePresentation::formatDate(new \ilDateTime($entity->getAliveTimestamp(), IL_CAL_UNIX)) . ')';
            }
        } elseif ($last_run) {
            $last_run = \ilDatePresentation::formatDate(new \ilDateTime($last_run, IL_CAL_UNIX));
        }

        return $last_run ?: '-';
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    public function getColumns(): array
    {
        return [
            'title' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('title') . ' / ' . $this->lng->txt('description'))
                ->withIsSortable(true),
            'component' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('cron_component'))
                ->withIsSortable(true)
                ->withIsOptional(true, true),
            'schedule' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('cron_schedule'))
                ->withIsSortable(true),
            'status' => $this->ui_factory
                ->table()
                ->column()
                ->boolean(
                    $this->lng->txt('cron_status'),
                    $this->ui_factory->symbol()->icon()->custom(
                        'assets/images/standard/icon_ok.svg',
                        $this->lng->txt('cron_status_active'),
                        \ILIAS\UI\Component\Symbol\Icon\Icon::SMALL
                    ),
                    $this->ui_factory->symbol()->icon()->custom(
                        'assets/images/standard/icon_not_ok.svg',
                        $this->lng->txt('cron_status_inactive'),
                        \ILIAS\UI\Component\Symbol\Icon\Icon::SMALL
                    )
                )
                ->withIsSortable(true),
            'status_info' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('cron_status_info'))
                ->withIsSortable(false),
            'result' => $this->ui_factory
                ->table()
                ->column()
                ->status($this->lng->txt('cron_result'))
                ->withIsSortable(true),
            'result_info' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('cron_result_info'))
                ->withIsSortable(false),
            'last_run' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('cron_last_run'))
                ->withIsSortable(true)
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    public function getActions(): array
    {
        if (!$this->mayWrite) {
            return [];
        }

        return [
            'run' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('cron_action_run'),
                $this->url_builder->withParameter($this->action_parameter_token, 'run'),
                $this->row_id_token
            ),
            'activate' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('cron_action_activate'),
                $this->url_builder->withParameter($this->action_parameter_token, 'activate'),
                $this->row_id_token
            ),
            'deactivate' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('cron_action_deactivate'),
                $this->url_builder->withParameter($this->action_parameter_token, 'deactivate'),
                $this->row_id_token
            ),
            'reset' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('cron_action_reset'),
                $this->url_builder->withParameter($this->action_parameter_token, 'reset'),
                $this->row_id_token
            ),
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('cron_action_edit'),
                $this->url_builder->withParameter($this->action_parameter_token, 'edit'),
                $this->row_id_token
            )
        ];
    }

    public function getComponent(): \ILIAS\UI\Component\Table\Table
    {
        return $this->ui_factory
            ->table()
            ->data($this, $this->lng->txt('cron_jobs'), $this->getColumns())
            ->withActions($this->getActions())
            ->withId(str_replace('\\', '', self::class))
            ->withRequest($this->request)
            ->withOrder(new \ILIAS\Data\Order('title', \ILIAS\Data\Order::ASC))
            ->withRange(new \ILIAS\Data\Range(0, 100));
    }
}
