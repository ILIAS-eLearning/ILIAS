<?php



/**
 * CpDependency
 */
class CpDependency
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $resourceid;


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
     * Set resourceid.
     *
     * @param string|null $resourceid
     *
     * @return CpDependency
     */
    public function setResourceid($resourceid = null)
    {
        $this->resourceid = $resourceid;

        return $this;
    }

    /**
     * Get resourceid.
     *
     * @return string|null
     */
    public function getResourceid()
    {
        return $this->resourceid;
    }
}
