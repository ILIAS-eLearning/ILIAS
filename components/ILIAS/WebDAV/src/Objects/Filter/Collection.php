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

namespace ILIAS\WebDAV\Objects\Filter;

use ILIAS\WebDAV\Entity\Entity;
use ILIAS\WebDAV\Entity\Container;
use ILIAS\WebDAV\Objects\Proxy;
use ILIAS\WebDAV\Objects\Type;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Collection implements Filter
{
    /**
     * @var Filter[]
     */
    private array $filters;

    public function __construct(
        Filter ...$filters
    ) {
        $this->filters = $filters;
    }

    public function checkName(string $name): bool
    {
        $return = true;
        foreach ($this->filters as $filter) {
            $return = $return && $filter->checkName($name);
        }
        return $return;
    }

    public function canUserIn(Action $action, Container $in): bool
    {
        $return = true;
        foreach ($this->filters as $filter) {
            $return = $return && $filter->canUserIn($action, $in);
        }
        return $return;
    }

    public function canUserFor(Action $action, Entity $for): bool
    {
        $return = true;
        foreach ($this->filters as $filter) {
            $return = $return && $filter->canUserFor($action, $for);
        }
        return $return;
    }

    public function filterEntity(Entity $entity): bool
    {
        $return = true;
        foreach ($this->filters as $filter) {
            $return = $return && $filter->filterEntity($entity);
        }
        return $return;
    }

    public function filterProxy(Proxy $proxy): bool
    {
        $return = true;
        foreach ($this->filters as $filter) {
            $return = $return && $filter->filterProxy($proxy);
        }
        return $return;
    }

    public function canUserCreate(Type $type, Container $in): bool
    {
        $return = true;
        foreach ($this->filters as $filter) {
            $return = $return && $filter->canUserCreate($type, $in);
        }
        return $return;
    }

}
