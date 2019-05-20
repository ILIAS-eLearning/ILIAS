<?php



/**
 * EcsCmsData
 */
class EcsCmsData
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int|null
     */
    private $serverId;

    /**
     * @var int|null
     */
    private $mid;

    /**
     * @var int|null
     */
    private $treeId;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $term;

    /**
     * @var int
     */
    private $status = '1';

    /**
     * @var bool
     */
    private $deleted = '0';

    /**
     * @var string|null
     */
    private $cmsId;


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
     * @param int|null $serverId
     *
     * @return EcsCmsData
     */
    public function setServerId($serverId = null)
    {
        $this->serverId = $serverId;

        return $this;
    }

    /**
     * Get serverId.
     *
     * @return int|null
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Set mid.
     *
     * @param int|null $mid
     *
     * @return EcsCmsData
     */
    public function setMid($mid = null)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int|null
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set treeId.
     *
     * @param int|null $treeId
     *
     * @return EcsCmsData
     */
    public function setTreeId($treeId = null)
    {
        $this->treeId = $treeId;

        return $this;
    }

    /**
     * Get treeId.
     *
     * @return int|null
     */
    public function getTreeId()
    {
        return $this->treeId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return EcsCmsData
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set term.
     *
     * @param string|null $term
     *
     * @return EcsCmsData
     */
    public function setTerm($term = null)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Get term.
     *
     * @return string|null
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return EcsCmsData
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return EcsCmsData
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set cmsId.
     *
     * @param string|null $cmsId
     *
     * @return EcsCmsData
     */
    public function setCmsId($cmsId = null)
    {
        $this->cmsId = $cmsId;

        return $this;
    }

    /**
     * Get cmsId.
     *
     * @return string|null
     */
    public function getCmsId()
    {
        return $this->cmsId;
    }
}
