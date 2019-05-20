<?php



/**
 * PreviewData
 */
class PreviewData
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var \DateTime
     */
    private $renderDate = '1970-01-01 00:00:00';

    /**
     * @var string
     */
    private $renderStatus = '';


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set renderDate.
     *
     * @param \DateTime $renderDate
     *
     * @return PreviewData
     */
    public function setRenderDate($renderDate)
    {
        $this->renderDate = $renderDate;

        return $this;
    }

    /**
     * Get renderDate.
     *
     * @return \DateTime
     */
    public function getRenderDate()
    {
        return $this->renderDate;
    }

    /**
     * Set renderStatus.
     *
     * @param string $renderStatus
     *
     * @return PreviewData
     */
    public function setRenderStatus($renderStatus)
    {
        $this->renderStatus = $renderStatus;

        return $this;
    }

    /**
     * Get renderStatus.
     *
     * @return string
     */
    public function getRenderStatus()
    {
        return $this->renderStatus;
    }
}
