<?php



/**
 * CpNode
 */
class CpNode
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $nodename;

    /**
     * @var int|null
     */
    private $slmId;


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
     * Set nodename.
     *
     * @param string|null $nodename
     *
     * @return CpNode
     */
    public function setNodename($nodename = null)
    {
        $this->nodename = $nodename;

        return $this;
    }

    /**
     * Get nodename.
     *
     * @return string|null
     */
    public function getNodename()
    {
        return $this->nodename;
    }

    /**
     * Set slmId.
     *
     * @param int|null $slmId
     *
     * @return CpNode
     */
    public function setSlmId($slmId = null)
    {
        $this->slmId = $slmId;

        return $this;
    }

    /**
     * Get slmId.
     *
     * @return int|null
     */
    public function getSlmId()
    {
        return $this->slmId;
    }
}
