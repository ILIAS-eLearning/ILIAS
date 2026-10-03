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

use ILIAS\Cron\CronJob;
use ILIAS\Cron\Job\JobManager;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Factory;
use ILIAS\News\NewsPeriodInfo;

/**
 * Deletes news items older than a configurable threshold.
 */
class ilNewsCronDeleteOldItems extends CronJob
{
    private const DEFAULT_THRESHOLD = 730;
    private const BATCH_SIZE = 100;

    private const SCOPE_AUTO = 'auto';
    private const SCOPE_MANUAL = 'manual';
    private const SCOPE_BOTH = 'both';

    private readonly ilLanguage $lng;
    private readonly ilSetting $news_settings;
    private readonly NewsPeriodInfo $period_info;
    private readonly ilDBInterface $db;
    private readonly JobManager $cron_manager;
    private readonly ilLogger $logger;
    private readonly ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->news_settings = new ilSetting('news');
        $this->period_info = new NewsPeriodInfo($this->lng, $this->news_settings);
        $this->db = $DIC->database();
        $this->cron_manager = $DIC->cron()->manager();
        $this->logger = $DIC->logger()->news();
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng->loadLanguageModule('news');
    }

    public function getId(): string
    {
        return 'news_delete_old';
    }

    public function getTitle(): string
    {
        return $this->lng->txt('cron_news_delete_old');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('cron_news_delete_old_desc');
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    /**
     * @return list<JobScheduleType>
     */
    public function getValidScheduleTypes(): array
    {
        return [
            JobScheduleType::DAILY,
            JobScheduleType::IN_DAYS,
            JobScheduleType::WEEKLY,
            JobScheduleType::MONTHLY,
            JobScheduleType::QUARTERLY,
            JobScheduleType::YEARLY,
        ];
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::MONTHLY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function usesLegacyForms(): bool
    {
        return false;
    }

    public function getCustomConfigurationInput(Factory $ui_factory, RefineryFactory $factory, ilLanguage $lng): FormInput
    {
        $this->main_tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
            $this->period_info->getPeriodInfo(),
        );

        $threshold = $ui_factory
            ->input()
            ->field()
            ->numeric(
                "{$lng->txt('cron_news_delete_old_threshold')} ({$lng->txt('days')})",
                $lng->txt('cron_news_delete_old_threshold_info')
            )
            ->withAdditionalTransformation($factory->int()->isGreaterThanOrEqual(1))
            ->withRequired(true)
            ->withValue($this->getThreshold());

        $scope = $ui_factory
            ->input()
            ->field()
            ->radio($lng->txt('cron_news_delete_old_scope'))
            ->withOption(self::SCOPE_AUTO, $lng->txt('cron_news_delete_old_scope_auto'))
            ->withOption(self::SCOPE_MANUAL, $lng->txt('cron_news_delete_old_scope_manual'))
            ->withOption(self::SCOPE_BOTH, $lng->txt('cron_news_delete_old_scope_both'))
            ->withRequired(true)
            ->withValue($this->getScope());

        return $ui_factory->input()->field()->section(
            [
                'cron_delete_threshold' => $threshold,
                'cron_delete_scope' => $scope,
            ],
            $lng->txt('cron_news_delete_old'),
        );
    }

    public function saveCustomConfiguration(mixed $form_data): void
    {
        $this->persistCustomSettings(
            (int) ($form_data['cron_delete_threshold'] ?? self::DEFAULT_THRESHOLD),
            (string) ($form_data['cron_delete_scope'] ?? self::SCOPE_BOTH)
        );
    }

    public function ping(): void
    {
        $this->cron_manager->ping($this->getId());
    }

    public function run(): JobResult
    {
        $threshold = $this->getThreshold();
        $cutoff = date('Y-m-d H:i:s', time() - ($threshold * 86400));
        $scope_filter = $this->getScopeSqlFilter($this->getScope());
        $deleted = 0;

        while (true) {
            $this->db->setLimit(self::BATCH_SIZE, 0);
            $set = $this->db->queryF(
                "SELECT id FROM il_news_item WHERE creation_date < %s AND context_obj_type <> %s{$scope_filter}",
                [ilDBConstants::T_TIMESTAMP, ilDBConstants::T_TEXT],
                [$cutoff, 'mcst']
            );

            $batch_count = 0;
            while ($row = $this->db->fetchAssoc($set)) {
                $news_item = new ilNewsItem((int) $row['id']);
                $news_item->delete();
                $deleted++;
                $batch_count++;
            }

            if ($batch_count === 0) {
                break;
            }

            $this->ping();
        }

        $result = new JobResult();

        if ($deleted === 0) {
            $this->logger->info('No news items deleted');
            $result->setStatus(JobResult::STATUS_NO_ACTION);
            return $result;
        }

        $this->logger->info(sprintf('Deleted %d news items', $deleted));
        $result->setStatus(JobResult::STATUS_OK);
        $result->setMessage(sprintf($this->lng->txt('cron_news_delete_old_result'), $deleted));

        return $result;
    }

    private function persistCustomSettings(int $threshold, string $scope): void
    {
        if (!in_array($scope, [self::SCOPE_AUTO, self::SCOPE_MANUAL, self::SCOPE_BOTH], true)) {
            $scope = self::SCOPE_BOTH;
        }

        $this->news_settings->set('cron_delete_threshold', (string) $threshold);
        $this->news_settings->set('cron_delete_scope', $scope);
    }

    private function getThreshold(): int
    {
        $threshold = (int) $this->news_settings->get('cron_delete_threshold', (string) self::DEFAULT_THRESHOLD);

        return $threshold < 1 ? self::DEFAULT_THRESHOLD : $threshold;
    }

    private function getScope(): string
    {
        $default_scope = self::SCOPE_BOTH;
        $scope = $this->news_settings->get('cron_delete_scope', $default_scope);

        return in_array($scope, [self::SCOPE_AUTO, self::SCOPE_MANUAL, self::SCOPE_BOTH], true)
            ? $scope
            : $default_scope;
    }

    private function getScopeSqlFilter(string $scope): string
    {
        return match ($scope) {
            self::SCOPE_AUTO => " AND priority = {$this->db->quote(NEWS_NOTICE, ilDBConstants::T_INTEGER)}",
            self::SCOPE_MANUAL => " AND priority = {$this->db->quote(NEWS_MESSAGE, ilDBConstants::T_INTEGER)}",
            default => '',
        };
    }
}
