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

use ILIAS\components\Logging\NullLogger;

class ilCronDefinitionProcessor implements ilComponentDefinitionProcessor
{
    private readonly ilCronJobRepository $cronRepository;
    private ?string $component = null;
    /** @var string[] */
    private array $has_cron;

    public function __construct(
        private readonly ilDBInterface $db,
        ilSetting $setting,
        ilComponentRepository $componentRepository,
        ilComponentFactory $componentFactory
    ) {
        $this->has_cron = [];

        $this->cronRepository = new ilCronJobRepositoryImpl(
            $this->db,
            $setting,
            new NullLogger(),
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
