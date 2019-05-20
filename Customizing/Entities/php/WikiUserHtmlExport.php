<?php



/**
 * WikiUserHtmlExport
 */
class WikiUserHtmlExport
{
    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $progress = '0';

    /**
     * @var \DateTime|null
     */
    private $startTs;

    /**
     * @var bool
     */
    private $status = '0';


    /**
     * Get wikiId.
     *
     * @return int
     */
    public function getWikiId()
    {
        return $this->wikiId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return WikiUserHtmlExport
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set progress.
     *
     * @param int $progress
     *
     * @return WikiUserHtmlExport
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress.
     *
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set startTs.
     *
     * @param \DateTime|null $startTs
     *
     * @return WikiUserHtmlExport
     */
    public function setStartTs($startTs = null)
    {
        $this->startTs = $startTs;

        return $this;
    }

    /**
     * Get startTs.
     *
     * @return \DateTime|null
     */
    public function getStartTs()
    {
        return $this->startTs;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return WikiUserHtmlExport
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }
}
