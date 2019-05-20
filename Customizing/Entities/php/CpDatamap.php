<?php



/**
 * CpDatamap
 */
class CpDatamap
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var int
     */
    private $scoNodeId = '0';

    /**
     * @var int
     */
    private $slmId = '0';

    /**
     * @var string
     */
    private $targetId = '';

    /**
     * @var bool|null
     */
    private $readSharedData = '1';

    /**
     * @var bool|null
     */
    private $writeSharedData = '1';


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
     * Set scoNodeId.
     *
     * @param int $scoNodeId
     *
     * @return CpDatamap
     */
    public function setScoNodeId($scoNodeId)
    {
        $this->scoNodeId = $scoNodeId;

        return $this;
    }

    /**
     * Get scoNodeId.
     *
     * @return int
     */
    public function getScoNodeId()
    {
        return $this->scoNodeId;
    }

    /**
     * Set slmId.
     *
     * @param int $slmId
     *
     * @return CpDatamap
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
     * @return CpDatamap
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
     * Set readSharedData.
     *
     * @param bool|null $readSharedData
     *
     * @return CpDatamap
     */
    public function setReadSharedData($readSharedData = null)
    {
        $this->readSharedData = $readSharedData;

        return $this;
    }

    /**
     * Get readSharedData.
     *
     * @return bool|null
     */
    public function getReadSharedData()
    {
        return $this->readSharedData;
    }

    /**
     * Set writeSharedData.
     *
     * @param bool|null $writeSharedData
     *
     * @return CpDatamap
     */
    public function setWriteSharedData($writeSharedData = null)
    {
        $this->writeSharedData = $writeSharedData;

        return $this;
    }

    /**
     * Get writeSharedData.
     *
     * @return bool|null
     */
    public function getWriteSharedData()
    {
        return $this->writeSharedData;
    }
}
