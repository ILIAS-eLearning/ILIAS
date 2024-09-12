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

class COPage implements Component\Component
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
            new \ILIAS\COPage\Setup\Agent(
                $pull[\ILIAS\Refinery\Factory::class]
            );

        // This is included via anonymous classes as a testament to the fact, that
        // the js and css of the COPage should be restructured according to the target
        // structure in the component directory and the public directory.
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/PC/InteractiveImage/js";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/PC/InteractiveImage/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/Editor/js";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/Editor/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/js";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/css";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/css";
            }
        };
    }
}
