<?php declare(strict_types = 1);

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
 
use Sabre\DAV\Locks\LockInfo;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 */
class ilWebDAVLockObject
{
    protected string $token;
    protected int $obj_id;
    protected int $ilias_owner;
    protected string $dav_owner;
    protected int $expires;
    protected int $depth;
    protected string $type;
    protected int $scope;
    
    public function __construct(
        string $token,
        int $obj_id,
        int $ilias_owner,
        string $dav_owner,
        int $expires,
        int $depth,
        string $type,
        int $scope
    ) {
        $this->token = $token;
        $this->obj_id = $obj_id;
        $this->ilias_owner = $ilias_owner;
        $this->dav_owner = $dav_owner;
        $this->expires = $expires;
        $this->depth = $depth;
        $this->type = $type;
        $this->scope = $scope;
    }
    
    public function getToken() : string
    {
        return $this->token;
    }
    
    public function getObjId() : int
    {
        return $this->obj_id;
    }
    
    public function getIliasOwner() : int
    {
        return $this->ilias_owner;
    }
    
    public function getDavOwner() : string
    {
        return $this->dav_owner;
    }
    
    public function getExpires() : int
    {
        return $this->expires;
    }
    
    public function getDepth() : int
    {
        return $this->depth;
    }
    
    public function getType() : string
    {
        return $this->type;
    }
    
    public function getScope() : int
    {
        return $this->scope;
    }
    
    public function getAsSabreDavLock(string $uri) : LockInfo
    {
        $timestamp = time();
        
        $sabre_lock = new LockInfo();
        $sabre_lock->created = $timestamp;
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
