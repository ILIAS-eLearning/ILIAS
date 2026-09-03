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

namespace ILIAS\WebDAV\Lock;

use Sabre\DAV\Locks\LockInfo;
use Sabre\DAV\Locks\Backend\AbstractBackend;
use ILIAS\WebDAV\Entity\Factory;
use ILIAS\WebDAV\Objects\Type;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Backend extends AbstractBackend
{
    private int $user_id;

    public function __construct(
        private Factory $entity_factory,
        private LocksRepository $locks_repository
    ) {
        global $DIC; // TODO remove Service Locator
        $this->user_id = $DIC->user()->getId();
    }

    /**
     * @return LockInfo[]
     */
    public function getLocks($uri, $returnChildLocks): array
    {
        $entity = $this->entity_factory->getByFullPath($uri);
        if ($entity === null) {
            return [];
        }

        $lock_object = $this->locks_repository->maybeGetLockFromObjId($entity->getObjectProxy()->getObjId());
        if ($lock_object === null) {
            return [];
        }

        return [
            $lock_object->getAsSabreDavLock($uri),
        ];
    }

    public function lock($uri, LockInfo $lock_info): bool
    {
        $entity = $this->entity_factory->getByFullPath($uri);
        $obj_id = $entity?->getObjectProxy()->getObjId();
        if ($obj_id === null) {
            return false;
        }

        $ilias_lock = new Lock(
            $lock_info->token,
            $obj_id,
            $this->user_id,
            $lock_info->owner,
            time() + 3600,
            $lock_info->depth,
            'w',
            $lock_info->scope
        );
        $this->locks_repository->save($ilias_lock);

        $proxy = $entity?->getObjectProxy();
        if ($proxy !== null && $proxy->getType() === Type::FILE) {
            $proxy->getStreamHandler()?->publish();
        }
        return true;
    }

    public function unlock($uri, LockInfo $lock_info): bool
    {
        $ilias_lock = $this->locks_repository->maybeGetLockFromToken($lock_info->token);

        if ($ilias_lock !== null && $ilias_lock->getIliasOwner() === $this->user_id) {
            $this->locks_repository->remove($lock_info->token);
            return true;
        }
        return false;
    }
}
