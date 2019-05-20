<?php



/**
 * SklTemplRef
 */
class SklTemplRef
{
    /**
     * @var int
     */
    private $sklNodeId = '0';

    /**
     * @var int
     */
    private $templId = '0';


    /**
     * Get sklNodeId.
     *
     * @return int
     */
    public function getSklNodeId()
    {
        return $this->sklNodeId;
    }

    /**
     * Set templId.
     *
     * @param int $templId
     *
     * @return SklTemplRef
     */
    public function setTemplId($templId)
    {
        $this->templId = $templId;

        return $this;
    }

    /**
     * Get templId.
     *
     * @return int
     */
    public function getTemplId()
    {
        return $this->templId;
    }
}
