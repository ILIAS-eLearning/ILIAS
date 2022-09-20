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

use Monolog\Logger;

class ilCronDefinitionProcessor implements ilComponentDefinitionProcessor
{
    private ilDBInterface $db;
    private ilCronJobRepository $cronRepository;
    private ?string $component = null;
    /** @var string[] */
    private array $has_cron;

    public function __construct(
        ilDBInterface $db,
        ilSetting $setting,
        ilComponentRepository $componentRepository,
        ilComponentFactory $componentFactory
    ) {
        $this->db = $db;
        $this->has_cron = [];

        $this->cronRepository = new ilCronJobRepositoryImpl(
            $this->db,
            $setting,
            new class () extends ilLogger {
                public function __construct()
                {
                }

                public function isHandling(int $a_level): bool
                {
                    return false;
                }

                public function log(string $a_message, int $a_level = ilLogLevel::INFO): void
                {
                }

                public function dump($a_variable, int $a_level = ilLogLevel::INFO): void
                {
                }

                public function debug(string $a_message, array $a_context = []): void
                {
                }

                public function info(string $a_message): void
                {
                }

                public function notice(string $a_message): void
                {
                }

                public function warning(string $a_message): void
                {
                }

                public function error(string $a_message): void
                {
                }

                public function critical(string $a_message): void
                {
                }

                public function alert(string $a_message): void
                {
                }

                public function emergency(string $a_message): void
                {
                }

                /** @noinspection PhpInconsistentReturnPointsInspection */
                public function getLogger(): Logger
                {
                }

                public function write(string $a_message, $a_level = ilLogLevel::INFO): void
                {
                }

                public function writeLanguageLog(string $a_topic, string $a_lang_key): void
                {
                }

                public function logStack(?int $a_level = null, string $a_message = ''): void
                {
                }

                public function writeMemoryPeakUsage(int $a_level): void
                {
                }
            },
            $componentRepository,
            $componentFactory
        );
    }

    public function purge(): void
    {
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component = $type . "/" . $component;
        $this->has_cron = [];
    }

    public function endComponent(string $component, string $type): void
    {
        $this->component = null;
        $this->has_cron = [];
    }

    public function beginTag(string $name, array $attributes): void
    {
        if ($name !== "cron") {
            return;
        }

        $component = $attributes["component"] ?? null;
        if (!$component) {
            $component = $this->component;
        }

        $this->cronRepository->registerJob(
            $component,
            $attributes["id"],
            $attributes["class"],
            ($attributes["path"] ?? null)
        );

        $this->has_cron[] = $attributes["id"];
    }

    public function endTag(string $name): void
    {
        if ($name !== "module" && $name !== "service") {
            return;
        }

        $this->cronRepository->unregisterJob($this->component, $this->has_cron);
    }
}
