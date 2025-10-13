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

namespace ILDemo;

use ILIAS\Component\Component;
use ILIAS\Repository\RepositoryObject;

class DemoRepoObject implements Component
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

        $contribute[RepositoryObject::class] = fn() =>
            new \ilDemoRepoObjPlugin(
                'xdmo',
                'DemoRepoObj',
                ['cat', 'adm'], //parent types
                true, //allow_copy
                false, //use_orgu_permissions
                false, //supports_lp
                false, //supports_export
            );

        $contribute[RepositoryObject::class] = fn() =>
            new RepositoryObject(
                'other',
                'some other RepoObj',
            );

        $contribute[\ILIAS\Component\Resource\PublicAsset::class] = static fn() =>
            new class () implements \ILIAS\Component\Resource\PublicAsset {
                public function getSource(): string
                {
                    $dir = __DIR__;
                    $parts = explode(DIRECTORY_SEPARATOR, $dir);
                    $parts = array_slice($parts, array_search('components', $parts));
                    return implode(DIRECTORY_SEPARATOR, $parts) . '/templates/images';
                }
                public function getTarget(): string
                {
                    return "assets/xdmo/images";
                }
            };
    }
}
