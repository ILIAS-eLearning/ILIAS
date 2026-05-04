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

namespace ILIAS;

class StudyProgramme implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
        new \ilStudyProgrammeSetupAgent(
            $pull[\ILIAS\Refinery\Factory::class]
        );
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "css/ilStudyProgramme.css");

        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilPrgInvalidateExpiredProgressesCronJob(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilPrgRestartAssignmentsCronJob(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilPrgUserNotRestartedCronJob(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilPrgUserRiskyToFailCronJob(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilPrgUpdateProgressCronJob(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
    }
}
