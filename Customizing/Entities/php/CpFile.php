<?php



/**
 * CpFile
 */
class CpFile
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $href;


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
     * Set href.
     *
     * @param string|null $href
     *
     * @return CpFile
     */
    public function setHref($href = null)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get href.
     *
     * @return string|null
     */
    public function getHref()
    {
        return $this->href;
    }
}
