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

namespace ILIAS\Setup;

/**
 * Class ImplementationOfInterfaceFinder
 *
 * @package ILIAS\ArtifactBuilder\Generators
 */
class ImplementationOfInterfaceFinder extends AbstractOfFinder
{
    public function getMatchingClassNames(
        string $interface,
        array $additional_ignore = [],
        string $matching_path = null
    ): \Iterator {
        yield from $this->genericGetMatchingClassNames(
            fn (\ReflectionClass $r) => $this->isClassMatching($interface, $r),
            $additional_ignore,
            $matching_path
        );
    }

    public function isClassMatching(string $interface, \ReflectionClass $r): bool
    {
        return ($r->isInstantiable() && $r->implementsInterface($interface));
    }
}
