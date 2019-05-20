<?php



/**
 * EcsImport
 */
class EcsImport
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var string|null
     */
    private $subId;

    /**
     * @var int|null
     */
    private $ecsId = '0';

    /**
     * @var string|null
     */
    private $contentId;

    /**
     * @var string|null
     */
    private $econtentId;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return EcsImport
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
     * Set serverId.
     *
     * @param int $serverId
     *
     * @return EcsImport
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;

        return $this;
    }

    /**
     * Get serverId.
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return EcsImport
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set subId.
     *
     * @param string|null $subId
     *
     * @return EcsImport
     */
    public function setSubId($subId = null)
    {
        $this->subId = $subId;

        return $this;
    }

    /**
     * Get subId.
     *
     * @return string|null
     */
    public function getSubId()
    {
        return $this->subId;
    }

    /**
     * Set ecsId.
     *
     * @param int|null $ecsId
     *
     * @return EcsImport
     */
    public function setEcsId($ecsId = null)
    {
        $this->ecsId = $ecsId;

        return $this;
    }

    /**
     * Get ecsId.
     *
     * @return int|null
     */
    public function getEcsId()
    {
        return $this->ecsId;
    }

    /**
     * Set contentId.
     *
     * @param string|null $contentId
     *
     * @return EcsImport
     */
    public function setContentId($contentId = null)
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * Get contentId.
     *
     * @return string|null
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Set econtentId.
     *
     * @param string|null $econtentId
     *
     * @return EcsImport
     */
    public function setEcontentId($econtentId = null)
    {
        $this->econtentId = $econtentId;

        return $this;
    }

    /**
     * Get econtentId.
     *
     * @return string|null
     */
    public function getEcontentId()
    {
        return $this->econtentId;
    }
}
