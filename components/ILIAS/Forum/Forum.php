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

class Forum implements Component\Component
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
            new \ilForumSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "autosave_forum.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
        new Component\Resource\ComponentCSS($this, "forum_table.css");

        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/News'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/Course'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/Group'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/Forum'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/User'
            );

        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'createdPost');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'updatedPost');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'censoredPost');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'beforePostDeletion');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'afterPostDeletion');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'mergedThreads');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'movedThreads');
    }
}
