<?php



/**
 * FrmUserRead
 */
class FrmUserRead
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $threadId = '0';

    /**
     * @var int
     */
    private $postId = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return FrmUserRead
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return FrmUserRead
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

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
     * Set threadId.
     *
     * @param int $threadId
     *
     * @return FrmUserRead
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * Get threadId.
     *
     * @return int
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * Set postId.
     *
     * @param int $postId
     *
     * @return FrmUserRead
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * Get postId.
     *
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }
}
