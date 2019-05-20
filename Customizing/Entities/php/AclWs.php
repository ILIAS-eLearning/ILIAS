<?php



/**
 * AclWs
 */
class AclWs
{
    /**
     * @var int
     */
    private $nodeId = '0';

    /**
     * @var int
     */
    private $objectId = '0';

    /**
     * @var string|null
     */
    private $extendedData;

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Set nodeId.
     *
     * @param int $nodeId
     *
     * @return AclWs
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
     * Set objectId.
     *
     * @param int $objectId
     *
     * @return AclWs
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set extendedData.
     *
     * @param string|null $extendedData
     *
     * @return AclWs
     */
    public function setExtendedData($extendedData = null)
    {
        $this->extendedData = $extendedData;

        return $this;
    }

    /**
     * Get extendedData.
     *
     * @return string|null
     */
    public function getExtendedData()
    {
        return $this->extendedData;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return AclWs
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
