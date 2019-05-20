<?php



/**
 * IlCldData
 */
class IlCldData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $isOnline;

    /**
     * @var string|null
     */
    private $service;

    /**
     * @var string|null
     */
    private $rootFolder;

    /**
     * @var string|null
     */
    private $rootId;

    /**
     * @var int
     */
    private $ownerId = '0';

    /**
     * @var bool|null
     */
    private $authComplete;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isOnline.
     *
     * @param bool|null $isOnline
     *
     * @return IlCldData
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set service.
     *
     * @param string|null $service
     *
     * @return IlCldData
     */
    public function setService($service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return string|null
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set rootFolder.
     *
     * @param string|null $rootFolder
     *
     * @return IlCldData
     */
    public function setRootFolder($rootFolder = null)
    {
        $this->rootFolder = $rootFolder;

        return $this;
    }

    /**
     * Get rootFolder.
     *
     * @return string|null
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Set rootId.
     *
     * @param string|null $rootId
     *
     * @return IlCldData
     */
    public function setRootId($rootId = null)
    {
        $this->rootId = $rootId;

        return $this;
    }

    /**
     * Get rootId.
     *
     * @return string|null
     */
    public function getRootId()
    {
        return $this->rootId;
    }

    /**
     * Set ownerId.
     *
     * @param int $ownerId
     *
     * @return IlCldData
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get ownerId.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Set authComplete.
     *
     * @param bool|null $authComplete
     *
     * @return IlCldData
     */
    public function setAuthComplete($authComplete = null)
    {
        $this->authComplete = $authComplete;

        return $this;
    }

    /**
     * Get authComplete.
     *
     * @return bool|null
     */
    public function getAuthComplete()
    {
        return $this->authComplete;
    }
}
