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

class Scorm2004 implements Component\Component
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
            new \ilScorm2004SetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "storeScorm2004.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "storeScorm.php");

        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/Scorm2004/scripts";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/Scorm2004/scripts";
            }
        };

        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/images";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/images";
            }
        };

        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/images";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/UI/resources/images/scorm2004";
            }
        };

        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/player.css";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/player.css";
            }
        };

        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/session_timeout.html";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/Scorm2004/templates/default/session_timeout.html";
            }
        };
    }
}
