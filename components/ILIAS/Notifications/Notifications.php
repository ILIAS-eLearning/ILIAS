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

class Notifications implements Component\Component
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
            new \ilNotificationUpdateAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "notifications.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "browser_notifications.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "osd.css");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "receive.mp3", "assets/sounds");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "receive.ogg", "assets/sounds");
    }
}
