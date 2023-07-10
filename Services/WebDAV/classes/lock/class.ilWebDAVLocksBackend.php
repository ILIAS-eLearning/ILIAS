<?php

declare(strict_types=1);

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

use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Locks\Backend\AbstractBackend;
use Sabre\DAV\Exception;
use Sabre\DAV\Locks\LockInfo;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilWebDAVLocksBackend extends AbstractBackend
{
    protected ilWebDAVLocksRepository $wedav_locks_repository;
    protected ilWebDAVRepositoryHelper $webdav_repository_helper;
    protected ilWebDAVObjFactory $webdav_object_factory;
    protected ilWebDAVLockUriPathResolver $webdav_path_resolver;
    protected ilObjUser $user;

    public function __construct(
        ilWebDAVLocksRepository $wedav_locks_repository,
        ilWebDAVRepositoryHelper $webdav_repository_helper,
        ilWebDAVObjFactory $webdav_object_factory,
        ilWebDAVLockUriPathResolver $webdav_path_resolver,
        ilObjUser $user
    ) {
        $this->wedav_locks_repository = $wedav_locks_repository;
        $this->webdav_repository_helper = $webdav_repository_helper;
        $this->webdav_object_factory = $webdav_object_factory;
        $this->webdav_path_resolver = $webdav_path_resolver;
        $this->user = $user;
    }

    /**
     * @param string $uri
     * @param bool $returnChildLocks
     * @return LockInfo[]
     */
    public function getLocks($uri, $returnChildLocks): array
    {
        $sabre_locks = [];

        try {
            $ref_id = $this->webdav_path_resolver->getRefIdForWebDAVPath($uri);

            $obj_id = $this->webdav_repository_helper->getObjectIdFromRefId($ref_id);
            $lock_on_obj = $this->getLocksOnObjectId($obj_id);

            if (!is_null($lock_on_obj)) {
                $sabre_locks[] = $lock_on_obj->getAsSabreDavLock($uri);
            }

            if ($returnChildLocks) {
                $sabre_locks = $this->getLocksRecursive($sabre_locks, $ref_id, $uri);
            }
        } catch (Exception\NotFound $e) {
        }

        return $sabre_locks;
    }

    /**
     * @param LockInfo[] $sabre_locks
     * @return LockInfo[]
     */
    protected function getLocksRecursive(array $sabre_locks, int $ref_id, string $uri): array
    {
        foreach ($this->webdav_repository_helper->getChildrenOfRefId($ref_id) as $child_ref) {
            try {
                $child_obj_id = $this->webdav_repository_helper->getObjectIdFromRefId($child_ref);
                $child_obj = $this->webdav_object_factory->retrieveDAVObjectByRefID($child_ref);

                $child_ilias_lock = $this->getLocksOnObjectId($child_obj_id);
                if (!is_null($child_ilias_lock)) {
                    $sabre_locks[] = $child_ilias_lock->getAsSabreDavLock($uri . '/' . $child_obj->getName());
                }

                $sabre_locks = $this->getLocksRecursive($sabre_locks, $child_ref, $uri . $child_obj->getName() . '/');
            } catch (ilWebDAVNotDavableException | NotFound | RuntimeException $e) {
            }
        }

        return $sabre_locks;
    }

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::unlock()
     */
    public function unlock($uri, LockInfo $lockInfo): bool
    {
        $ilias_lock = $this->wedav_locks_repository->getLockObjectWithTokenFromDB($lockInfo->token);

        if (!is_null($ilias_lock) && $ilias_lock->getIliasOwner() == $this->user->getId()) {
            $this->wedav_locks_repository->removeLockWithTokenFromDB($lockInfo->token);
            return true;
        } else {
            throw new Exception\Forbidden();
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::lock()
     */
    public function lock($uri, LockInfo $lock_info): bool
    {
        try {
            $ref_id = $this->webdav_path_resolver->getRefIdForWebDAVPath($uri);

            if ($ref_id > 0 && $this->webdav_repository_helper->checkAccess('write', $ref_id)) {
                $obj_id = $this->webdav_repository_helper->getObjectIdFromRefId($ref_id);
                $ilias_lock = new ilWebDAVLockObject(
                    $lock_info->token,
                    $obj_id,
                    $this->user->getId(),
                    $lock_info->owner,
                    time() + 360,
                    $lock_info->depth,
                    'w',
                    $lock_info->scope
                );
                $this->wedav_locks_repository->saveLockToDB($ilias_lock);
                return true;
            } else {
                throw new Exception\Forbidden();
            }
        } catch (Exception\NotFound $e) {
            if ($e->getCode() == -1) {
                return false;
            } else {
                throw $e;
            }
        }
    }

    public function getLocksOnObjectId(int $obj_id): ?ilWebDAVLockObject
    {
        try {
            return $this->wedav_locks_repository->getLockObjectWithObjIdFromDB($obj_id);
        } catch (Exception $e) {
        }
    }
}
