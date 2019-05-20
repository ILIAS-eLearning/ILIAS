<?php



/**
 * AdlSharedData
 */
class AdlSharedData
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var int
     */
    private $slmId = '0';

    /**
     * @var string
     */
    private $targetId = '';

    /**
     * @var string|null
     */
    private $store;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return AdlSharedData
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
     * Set cpNodeId.
     *
     * @param int $cpNodeId
     *
     * @return AdlSharedData
     */
    public function setCpNodeId($cpNodeId)
    {
        $this->cpNodeId = $cpNodeId;

        return $this;
    }

    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set slmId.
     *
     * @param int $slmId
     *
     * @return AdlSharedData
     */
    public function setSlmId($slmId)
    {
        $this->slmId = $slmId;

        return $this;
    }

    /**
     * Get slmId.
     *
     * @return int
     */
    public function getSlmId()
    {
        return $this->slmId;
    }

    /**
     * Set targetId.
     *
     * @param string $targetId
     *
     * @return AdlSharedData
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Get targetId.
     *
     * @return string
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Set store.
     *
     * @param string|null $store
     *
     * @return AdlSharedData
     */
    public function setStore($store = null)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Get store.
     *
     * @return string|null
     */
    public function getStore()
    {
        return $this->store;
    }
}
