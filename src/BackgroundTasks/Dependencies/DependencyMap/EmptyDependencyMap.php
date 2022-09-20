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

namespace ILIAS\BackgroundTasks\Dependencies\DependencyMap;

use ILIAS\BackgroundTasks\Dependencies\Exceptions\NoSuchServiceException;
use ILIAS\DI\Container;

/**
 * Class BaseDependencyMap
 * @package ILIAS\BackgroundTasks\Dependencies
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class EmptyDependencyMap implements DependencyMap
{
    protected array $maps = [];

    /**
     * @inheritdoc
     */
    public function getDependencyWith(Container $DIC, string $fullyQualifiedDomainName, string $for, callable $map)
    {
        $result = $map($DIC, $fullyQualifiedDomainName, $for);
        if ($result) {
            return $result;
        } else {
            return $this->getDependency($DIC, $fullyQualifiedDomainName, $for);
        }
    }

    /**
     * Returns a new dependency map with the given mapping. The newer mapping always comes first!
     * @param callable $map (Container $DIC, string $fullyQualifiedDomainName, string $for) =>
     *                      mixed|null
     */
    public function with(callable $map): DependencyMap
    {
        $dependency_map = new static();
        $dependency_map->maps = array_merge([$map], $this->maps);

        return $dependency_map;
    }

    /**
     * @inheritdoc
     */
    public function getDependency(Container $DIC, string $fullyQualifiedDomainName, string $for)
    {
        foreach ($this->maps as $map) {
            $result = $map($DIC, $fullyQualifiedDomainName, $for);
            if ($result) {
                return $result;
            }
        }

        throw new NoSuchServiceException("The requested service " . $fullyQualifiedDomainName
            . " could not be resolved.");
    }
}
