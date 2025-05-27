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

        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'userAssigned');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'userDeassigned');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'userReAssigned');
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(self::class, 'raise', 'userSuccessful');

        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/User'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/Tracking'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/Tree'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/Object'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/ContainerReference'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/AccessControl'
            );
        $contribute[\ILIAS\EventHandling\Definition::class] = static fn() =>
            new \ILIAS\EventHandling\Definition(
                self::class,
                'listen',
                'components/ILIAS/StudyProgramme'
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
                'components/ILIAS/OrgUnit'
            );
    }
}
