<?php



/**
 * AccUserAccessKey
 */
class AccUserAccessKey
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $functionId = '0';

    /**
     * @var string|null
     */
    private $accessKey;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return AccUserAccessKey
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set functionId.
     *
     * @param int $functionId
     *
     * @return AccUserAccessKey
     */
    public function setFunctionId($functionId)
    {
        $this->functionId = $functionId;

        return $this;
    }

    /**
     * Get functionId.
     *
     * @return int
     */
    public function getFunctionId()
    {
        return $this->functionId;
    }

    /**
     * Set accessKey.
     *
     * @param string|null $accessKey
     *
     * @return AccUserAccessKey
     */
    public function setAccessKey($accessKey = null)
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * Get accessKey.
     *
     * @return string|null
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }
}
