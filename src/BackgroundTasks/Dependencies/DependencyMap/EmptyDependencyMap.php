<?php

namespace ILIAS\BackgroundTasks\Dependencies\DependencyMap;

use ILIAS\BackgroundTasks\Dependencies\Exceptions\NoSuchServiceException;
use ILIAS\DI\Container;

/**
 * Class BaseDependencyMap
 *
 * @package ILIAS\BackgroundTasks\Dependencies
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class EmptyDependencyMap implements DependencyMap
{

    /**
     * @var callable[]
     */
    protected $maps = [];


    /**
     * @inheritdoc
     */
    public function getDependencyWith(Container $DIC, $fullyQualifiedDomainName, $for, callable $map)
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
     *
     * @param callable $map (Container $DIC, string $fullyQualifiedDomainName, string $for) =>
     *                      mixed|null
     *
     * @return static
     */
    public function with(callable $map)
    {
        $dependencyMap = new static();
        $dependencyMap->maps = array_merge([$map], $this->maps);

        return $dependencyMap;
    }


    /**
     * @inheritdoc
     */
    public function getDependency(Container $DIC, $fullyQualifiedDomainName, $for)
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