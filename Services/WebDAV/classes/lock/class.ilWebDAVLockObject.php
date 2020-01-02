<?php

require_once('Services/ActiveRecord/Connector/class.arConnectorSession.php');

/**
 * Represents a lock on an ilias object. Objects from this class are immutable!
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 */
class ilWebDAVLockObject
{
    /**
     *
     * @param string $token     example:
     * @param int $obj_id       example: 1111
     * @param int $ilias_owner  example: 2222
     * @param string $dav_owner example: 'Desktop\Raphi'
     * @param int $expires      example: '795596280'
     * @param int $depth        example: '-1'
     * @param string $type      example: 'w'
     * @param string $scope     example: 'x'
     */
    public function __construct($token, $obj_id, $ilias_owner, $dav_owner, $expires, $depth, $type, $scope)
    {
        $this->token = $token;
        $this->obj_id = $obj_id;
        $this->ilias_owner = $ilias_owner;
        $this->dav_owner = $dav_owner;
        $this->expires = $expires;
        $this->depth = $depth;
        $this->type = $type;
        $this->scope = $scope;
    }
    
    protected $token;
    protected $obj_id;
    protected $ilias_owner;
    protected $dav_owner;
    protected $expires;
    protected $depth;
    protected $type;
    protected $scope;
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    /**
     * @return int
     */
    public function getIliasOwner()
    {
        return $this->ilias_owner;
    }
      
    /**
     * @return string
     */
    public function getDavOwner()
    {
        return $this->dav_owner;
    }
        
    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }
    
    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }
    
    public static function createFromAssocArray($assoc_array)
    {
        return new ilWebDAVLockObject(
            $assoc_array['token'],
            $assoc_array['obj_id'],
            $assoc_array['ilias_owner'],
            $assoc_array['dav_owner'],
            $assoc_array['expires'],
            $assoc_array['depth'],
            $assoc_array['type'],
            $assoc_array['scope']
        );
    }
    
    /**
     * Creates an ILIAS lock object from a sabreDAV lock object
     *
     * IMPORTANT: This method just creates and initializes an object. It does not
     * create any record in the database!
     *
     * @param Sabre\DAV\Locks\LockInfo $lock_info
     */
    public static function createFromSabreLock(Sabre\DAV\Locks\LockInfo $lock_info, $obj_id)
    {
        global $DIC;

        $ilias_lock = new ilWebDAVLockObject(
            $lock_info->token,                  // token
            $obj_id,                            // obj_id
            $DIC->user()->getId(),              // ilias_owner
            $lock_info->owner,                  // dav_owner
            time() + 360,              // expires (hard coded like in the old webdav)
            $lock_info->depth,                  // depth
            'w',                          // type
            $lock_info->scope
        );                 // scope
            
        return $ilias_lock;
    }
    
    public function getAsSabreDavLock($uri)
    {
        global $DIC;
        
        $timestamp = time();
        
        $sabre_lock = new Sabre\DAV\Locks\LockInfo();
        $sabre_lock->created;
        $sabre_lock->depth = $this->depth;
        $sabre_lock->owner = $this->dav_owner;
        $sabre_lock->scope = $this->scope;
        $sabre_lock->timeout = $this->expires - $timestamp;
        $sabre_lock->created = $this->expires - 3600;
        $sabre_lock->token = $this->token;
        $sabre_lock->uri = $uri;
        
        return $sabre_lock;
    }
}
