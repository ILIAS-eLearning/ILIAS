<?php



/**
 * DavLock
 */
class DavLock
{
    /**
     * @var string
     */
    private $token = ' ';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $nodeId = '0';

    /**
     * @var int
     */
    private $iliasOwner = '0';

    /**
     * @var string|null
     */
    private $davOwner;

    /**
     * @var int
     */
    private $expires = '0';

    /**
     * @var int
     */
    private $depth = '0';

    /**
     * @var string|null
     */
    private $type = 'w';

    /**
     * @var string|null
     */
    private $scope = 's';


    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return DavLock
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set nodeId.
     *
     * @param int $nodeId
     *
     * @return DavLock
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;

        return $this;
    }

    /**
     * Get nodeId.
     *
     * @return int
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Set iliasOwner.
     *
     * @param int $iliasOwner
     *
     * @return DavLock
     */
    public function setIliasOwner($iliasOwner)
    {
        $this->iliasOwner = $iliasOwner;

        return $this;
    }

    /**
     * Get iliasOwner.
     *
     * @return int
     */
    public function getIliasOwner()
    {
        return $this->iliasOwner;
    }

    /**
     * Set davOwner.
     *
     * @param string|null $davOwner
     *
     * @return DavLock
     */
    public function setDavOwner($davOwner = null)
    {
        $this->davOwner = $davOwner;

        return $this;
    }

    /**
     * Get davOwner.
     *
     * @return string|null
     */
    public function getDavOwner()
    {
        return $this->davOwner;
    }

    /**
     * Set expires.
     *
     * @param int $expires
     *
     * @return DavLock
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires.
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set depth.
     *
     * @param int $depth
     *
     * @return DavLock
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return DavLock
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set scope.
     *
     * @param string|null $scope
     *
     * @return DavLock
     */
    public function setScope($scope = null)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope.
     *
     * @return string|null
     */
    public function getScope()
    {
        return $this->scope;
    }
}
