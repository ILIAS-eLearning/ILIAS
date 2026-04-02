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

use ILIAS\Component\Resource\ComponentCSS;

class Mail implements Component\Component
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
            new \ilMailSetupAgent();
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, 'ilMailComposeFunctions.js');
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new ComponentCSS($this, '/../../../../templates/default/mail.css');
        $contribute[User\Settings\UserSettings::class] = static fn() =>
            new Mail\UserSettings\Settings();
        $contribute[User\Profile\ChangeListeners\UserFieldAttributesChangeListener::class] = static fn() =>
            new Mail\ilMailUserFieldChangeListener();

        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilMailCronNotification(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilMailCronOrphanedMails(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ILIAS\Mail\Cron\ScheduledMailsCron(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );
    }
}
