<?php



/**
 * CpAuxilaryresource
 */
class CpAuxilaryresource
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $auxiliaryresourceid;

    /**
     * @var string|null
     */
    private $purpose;


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
     * Set auxiliaryresourceid.
     *
     * @param string|null $auxiliaryresourceid
     *
     * @return CpAuxilaryresource
     */
    public function setAuxiliaryresourceid($auxiliaryresourceid = null)
    {
        $this->auxiliaryresourceid = $auxiliaryresourceid;

        return $this;
    }

    /**
     * Get auxiliaryresourceid.
     *
     * @return string|null
     */
    public function getAuxiliaryresourceid()
    {
        return $this->auxiliaryresourceid;
    }

    /**
     * Set purpose.
     *
     * @param string|null $purpose
     *
     * @return CpAuxilaryresource
     */
    public function setPurpose($purpose = null)
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * Get purpose.
     *
     * @return string|null
     */
    public function getPurpose()
    {
        return $this->purpose;
    }
}
