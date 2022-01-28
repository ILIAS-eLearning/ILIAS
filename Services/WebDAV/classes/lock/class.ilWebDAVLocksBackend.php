<?php declare(strict_types = 1);

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
    protected ilWebDAVLockUriPathResolver $webadav_path_resolver;
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
    
    public function getLocks($uri, $returnChildLocks) : array
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
    
    protected function getLocksRecursive($sabre_locks, $ref_id, $uri) : array
    {
        foreach ($this->webdav_repository_helper->getChildrenOfRefId($ref_id) as $child_ref) {
            try {
                $child_obj_id = $this->webdav_repository_helper->getObjectIdFromRefId($child_ref);
                $child_obj = $this->webdav_object_factory->retrieveDAVObjectByRefID($child_ref);
                
                $child_ilias_locks = $this->getLocksOnObjectId($child_obj_id);
                if (!is_null($child_ilias_locks)) {
                    foreach ($child_ilias_locks as $lock) {
                        $sabre_locks[] = $lock->getAsSabreDavLock($uri . '/' . $child_obj->getName());
                    }
                }

                $sabre_locks = $this->getLocksRecursive($sabre_locks, $child_ref, $uri . $child_obj->getName() . '/');
            } catch (ilWebDAVNotDavableException | NotFound | RuntimeException $e) {
            }
        }
        
        return $sabre_locks;
    }
    
    public function unlock($uri, LockInfo $lockInfo)
    {
        $ilias_lock = $this->wedav_locks_repository->getLockObjectWithTokenFromDB($lockInfo->token);

        if (!is_null($ilias_lock) && $ilias_lock->getIliasOwner() == $this->user->getId()) {
            $this->wedav_locks_repository->removeLockWithTokenFromDB($lockInfo->token);
        } else {
            throw new Exception\Forbidden();
        }
    }
    
    public function lock($uri, LockInfo $lock_info)
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
            } else {
                throw new Exception\Forbidden();
            }
        } catch (Exception\NotFound $e) {
            if ($e->getCode() == -1) {
                return;
            } else {
                throw $e;
            }
        }
    }
    
    public function getLocksOnObjectId(int $obj_id) : ?ilWebDAVLockObject
    {
        try {
            return $this->wedav_locks_repository->getLockObjectWithObjIdFromDB($obj_id);
        } catch (Exception $e) {
        }
    }
}
