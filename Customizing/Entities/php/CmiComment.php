<?php



/**
 * CmiComment
 */
class CmiComment
{
    /**
     * @var int
     */
    private $cmiCommentId = '0';

    /**
     * @var int|null
     */
    private $cmiNodeId;

    /**
     * @var string|null
     */
    private $cComment;

    /**
     * @var \DateTime|null
     */
    private $cTimestamp;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var bool|null
     */
    private $sourceislms;


    /**
     * Get cmiCommentId.
     *
     * @return int
     */
    public function getCmiCommentId()
    {
        return $this->cmiCommentId;
    }

    /**
     * Set cmiNodeId.
     *
     * @param int|null $cmiNodeId
     *
     * @return CmiComment
     */
    public function setCmiNodeId($cmiNodeId = null)
    {
        $this->cmiNodeId = $cmiNodeId;

        return $this;
    }

    /**
     * Get cmiNodeId.
     *
     * @return int|null
     */
    public function getCmiNodeId()
    {
        return $this->cmiNodeId;
    }

    /**
     * Set cComment.
     *
     * @param string|null $cComment
     *
     * @return CmiComment
     */
    public function setCComment($cComment = null)
    {
        $this->cComment = $cComment;

        return $this;
    }

    /**
     * Get cComment.
     *
     * @return string|null
     */
    public function getCComment()
    {
        return $this->cComment;
    }

    /**
     * Set cTimestamp.
     *
     * @param \DateTime|null $cTimestamp
     *
     * @return CmiComment
     */
    public function setCTimestamp($cTimestamp = null)
    {
        $this->cTimestamp = $cTimestamp;

        return $this;
    }

    /**
     * Get cTimestamp.
     *
     * @return \DateTime|null
     */
    public function getCTimestamp()
    {
        return $this->cTimestamp;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return CmiComment
     */
    public function setLocation($location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set sourceislms.
     *
     * @param bool|null $sourceislms
     *
     * @return CmiComment
     */
    public function setSourceislms($sourceislms = null)
    {
        $this->sourceislms = $sourceislms;

        return $this;
    }

    /**
     * Get sourceislms.
     *
     * @return bool|null
     */
    public function getSourceislms()
    {
        return $this->sourceislms;
    }
}
