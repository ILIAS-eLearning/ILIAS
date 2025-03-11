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

use ILIAS\Setup;

class ilCronjobsRegisteredObjective implements Setup\Objective
{
    public function __construct(
        private readonly array $cronjobs
    ) {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "CronJobs are registered.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilSettingsFactoryExistsObjective(),
            new \ilComponentFactoryExistsObjective(),
            new \ilDatabaseUpdatedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        /** @var ilComponentRepository $component_repository  */
        $component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);
        /** @var ilComponentFactory $component_factory  */
        $component_factory = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_FACTORY);
        /** @var ilSettingsFactory $settings_factory */
        $settings_factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);

        $mock_logger_factory = new class () implements \ILIAS\Logging\LoggerFactory {
        };

        $registry = new ILIAS\Cron\CronRegistry($this->cronjobs);
        $language = new ilSetupLanguage('en');

        $repo = new ilCronJobRepositoryImpl(
            $registry,
            $db,
            $settings_factory->settingsFor(),
            new ILIAS\components\Logging\NullLogger(),
            $component_repository,
            $component_factory,
            $language,
            $mock_logger_factory
        );

        $repo->unregisterAllJobs();

        foreach ($this->cronjobs as $class => $job) {
            $repo->registerJob(
                $job->getComponent(),
                $job->getId(),
                get_class($job),
                null //path!
            );
        }

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
