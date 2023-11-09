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

use ILIAS\Cron\Schedule\CronJobScheduleType;
use ILIAS\components\WOPI\Discovery\Crawler;
use ILIAS\components\WOPI\Discovery\AppDBRepository;
use ILIAS\components\WOPI\Discovery\ActionDBRepository;
use ILIAS\components\WOPI\Discovery\AppRepository;
use ILIAS\components\WOPI\Discovery\ActionRepository;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPICrawler extends ilCronJob
{
    private ilLanguage $language;
    private ilSetting $settings;
    private Crawler $crawler;
    private AppRepository $app_repository;
    private ActionRepository $action_repository;

    public function __construct()
    {
        global $DIC;
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('wopi');
        $this->settings = $DIC->settings();
        $this->crawler = new Crawler();

        $this->app_repository = new AppDBRepository($DIC->database());
        $this->action_repository = new ActionDBRepository($DIC->database());
    }

    public function getId(): string
    {
        return 'wopi_crawler';
    }

    public function getTitle(): string
    {
        return $this->language->txt('wopi_crawler_cronjob_title');
    }

    public function getDescription(): string
    {
        return $this->language->txt('wopi_crawler_cronjob_description');
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_WEEKLY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        if (!(bool) $this->settings->get('wopi_activated', '0')) {
            $result->setMessage($this->language->txt('wopi_crawler_cronjob_not_activated'));
            return $result;
        }
        $discovery_url = $this->settings->get('wopi_discovery_url');

        if (!$this->crawler->validate(new URI($discovery_url))) {
            $result->setStatus(ilCronJobResult::STATUS_FAIL);
            $result->setMessage($this->language->txt('msg_error_wopi_invalid_discorvery_url'));
            return $result;
        }

        $apps = $this->crawler->crawl(new URI($discovery_url));
        if ($apps === null) {
            $result->setStatus(ilCronJobResult::STATUS_FAIL);
            $result->setMessage($this->language->txt('wopi_crawler_cronjob_no_apps'));
            return $result;
        }
        $result->setMessage($this->language->txt('wopi_crawler_cronjob_success'));
        $this->app_repository->storeCollection($apps, $this->action_repository);

        return $result;
    }

}
