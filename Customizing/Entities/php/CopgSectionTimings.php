<?php



/**
 * CopgSectionTimings
 */
class CopgSectionTimings
{
    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var string
     */
    private $parentType = '';

    /**
     * @var int
     */
    private $unixTs = '0';


    /**
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return CopgSectionTimings
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Get pageId.
     *
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set parentType.
     *
     * @param string $parentType
     *
     * @return CopgSectionTimings
     */
    public function setParentType($parentType)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set unixTs.
     *
     * @param int $unixTs
     *
     * @return CopgSectionTimings
     */
    public function setUnixTs($unixTs)
    {
        $this->unixTs = $unixTs;

        return $this;
    }

    /**
     * Get unixTs.
     *
     * @return int
     */
    public function getUnixTs()
    {
        return $this->unixTs;
    }
}
