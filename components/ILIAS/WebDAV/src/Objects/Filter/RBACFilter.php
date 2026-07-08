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
use ILIAS\WebDAV\AccessCheck;
use ILIAS\WebDAV\Objects\Type;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RBACFilter implements Filter
{
    public function __construct(
        private AccessCheck $check
    ) {
    }

    public function checkName(string $name): bool
    {
        return true;
    }

    public function canUserIn(Action $action, Container $in): bool
    {
        return $this->check->hasCurrentUserAccess($action, $in->getObjectProxy()?->getRefId());
    }

    public function canUserFor(Action $action, Entity $for): bool
    {
        return $this->check->hasCurrentUserAccess($action, $for->getObjectProxy()?->getRefId());
    }

    public function filterEntity(Entity $entity): bool
    {
        return $this->check->hasCurrentUserAccess(Action::READ, $entity->getObjectProxy()?->getRefId());
    }

    public function filterProxy(Proxy $proxy): bool
    {
        return $this->check->hasCurrentUserAccess(Action::READ, $proxy->getRefId());
    }

    public function canUserCreate(Type $type, Container $in): bool
    {
        return $this->check->canUserCreate($type, $in->getObjectProxy()?->getRefId());
    }

}
