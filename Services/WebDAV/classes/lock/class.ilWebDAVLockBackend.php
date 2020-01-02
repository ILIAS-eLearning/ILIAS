<?php
use Sabre\DAV\Locks;
use Sabre\DAV\Exception;

require_once 'libs/composer/vendor/autoload.php';

require_once 'Services/WebDAV/classes/lock/class.ilWebDAVLockObject.php';
require_once 'Services/WebDAV/classes/db/class.ilWebDAVDBManager.php';
require_once 'Services/WebDAV/classes/tree/class.ilWebDAVUriPathResolver.php';
require_once 'Services/WebDAV/classes/class.ilWebDAVRepositoryHelper.php';
require_once 'Services/WebDAV/classes/class.ilWebDAVObjDAVHelper.php';

/**
 * Class ilWebDAVLockBackend
 *
 * Implementation for WebDAV locking mechanism. Extends the LockBackend from sabreDAV and saves sabreDAV locks as ILIAS
 * locks to DB and returns ILIAS locks in DB as sabreDAV locks. Also removes existing locks
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends Sabre\DAV\Locks\Backend\AbstractBackend
 */
class ilWebDAVLockBackend extends Sabre\DAV\Locks\Backend\AbstractBackend
{
    /** @var ilWebDAVDBManager */
    protected $db_manager;

    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    /** @var ilWebDAVObjDAVHelper */
    protected $obj_dav_helper;

    /** @var ilWebDAVUriPathResolver */
    protected $uri_path_resolver;

    /**
     * Constructor with dependency injection
     * @param unknown $db_manager
     * @param ilWebDAVRepositoryHelper|null $repo_helper
     */
    public function __construct($db_manager = null, ilWebDAVRepositoryHelper $repo_helper = null)
    {
        global $DIC;

        $this->db_manager = $db_manager == null ? new ilWebDAVDBManager($DIC->database()) : $db_manager;
        $this->repo_helper = $repo_helper == null ? new ilWebDAVRepositoryHelper($DIC->access(), $DIC->repositoryTree()) : $repo_helper;
        $this->obj_dav_helper = new ilWebDAVObjDAVHelper($this->repo_helper);
        $this->uri_path_resolver = new ilWebDAVUriPathResolver($this->repo_helper);
        $this->user = $DIC->user();
    }
    
    /**
     * This function returns all locks and child locks as SabreDAV lock objects
     * It is needed for sabreDAV to see if there are any locks
     *
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::getLocks()
     */
    public function getLocks($uri, $returnChildLocks)
    {
        $sabre_locks = array();

        // Get locks on given uri
        try {
            $ref_id = $this->uri_path_resolver->getRefIdForWebDAVPath($uri);

            $obj_id = $this->repo_helper->getObjectIdFromRefId($ref_id);
            $lock_on_obj = $this->getLocksOnObjectId($obj_id);

            if ($lock_on_obj != false) {
                $sabre_locks[] = $lock_on_obj->getAsSabreDavLock($uri);
            }

            // Get locks on childs
            if ($returnChildLocks) {
                $sabre_locks = $this->getLocksRecursive($sabre_locks, $ref_id, $uri);
            }
        } catch (Exception\NotFound $e) {
            return $sabre_locks;
        }

        return $sabre_locks;
    }
    
    /**
     * Iterates recursive through the ilias tree to search for locked objects
     *
     * @param array $sabre_locks
     * @param integer $ref_id
     * @param string $uri
     * @return array
     */
    protected function getLocksRecursive($sabre_locks, $ref_id, $uri)
    {
        foreach ($this->repo_helper->getChildrenOfRefId($ref_id) as $child_ref) {
            // Only get locks of DAVable objects. Because not DAVable objects won't be lockable anyway
            $child_obj_id = $this->repo_helper->getObjectIdFromRefId($child_ref);
            if ($this->obj_dav_helper->isDAVableObject($child_obj_id, false)) {
                // Get Locks of this object
                $title = $this->repo_helper->getObjectTitleFromObjId($child_obj_id, true);
                $child_ilias_locks = $this->getLocksOnObjectId($child_obj_id);
                if ($child_ilias_locks != false) {
                    foreach ($child_ilias_locks as $lock) {
                        $sabre_locks[] = $lock->getAsSabreDavLock($uri . '/' . $title);
                    }
                }

                // Get locks of child objects
                $sabre_locks = $this->getLocksRecursive($sabre_locks, $child_ref, $uri . $title . '/');
            }
        }
        
        return $sabre_locks;
    }

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::unlock()
     */
    public function unlock($uri, Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        $ilias_lock = $this->db_manager->getLockObjectWithTokenFromDB($lockInfo->token);

        if ($ilias_lock && $ilias_lock->getIliasOwner() == $this->user->getId()) {
            $this->db_manager->removeLockWithTokenFromDB($lockInfo->token);
        } else {
            throw new Exception\Forbidden();
        }
    }

    /**
     * Function for the sabreDAV interface
     *
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::lock()
     */
    public function lock($uri, Sabre\DAV\Locks\LockInfo $lock_info)
    {
        try {
            $ref_id = $this->uri_path_resolver->getRefIdForWebDAVPath($uri);

            if ($ref_id > 0 && $this->repo_helper->checkAccess('write', $ref_id)) {
                $obj_id = $this->repo_helper->getObjectIdFromRefId($ref_id);
                $ilias_lock = ilWebDAVLockObject::createFromSabreLock($lock_info, $obj_id);
                $this->db_manager->saveLockToDB($ilias_lock);
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

    
    /**
     * Returns lock on given object
     *
     * @param int $obj_id
     * @return array
     */
    public function getLocksOnObjectId(int $obj_id)
    {
        try {
            return $this->db_manager->getLockObjectWithObjIdFromDB($obj_id);
        } catch (Exception $e) {
        }
    }
}
