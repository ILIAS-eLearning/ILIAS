<?php



/**
 * EcsNodeMappingA
 */
class EcsNodeMappingA
{
    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var int
     */
    private $csRoot = '0';

    /**
     * @var int
     */
    private $csId = '0';

    /**
     * @var int|null
     */
    private $refId;

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var bool|null
     */
    private $titleUpdate;

    /**
     * @var bool|null
     */
    private $positionUpdate;

    /**
     * @var bool|null
     */
    private $treeUpdate;


    /**
     * Set serverId.
     *
     * @param int $serverId
     *
     * @return EcsNodeMappingA
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
     * @return EcsNodeMappingA
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
     * Set csRoot.
     *
     * @param int $csRoot
     *
     * @return EcsNodeMappingA
     */
    public function setCsRoot($csRoot)
    {
        $this->csRoot = $csRoot;

        return $this;
    }

    /**
     * Get csRoot.
     *
     * @return int
     */
    public function getCsRoot()
    {
        return $this->csRoot;
    }

    /**
     * Set csId.
     *
     * @param int $csId
     *
     * @return EcsNodeMappingA
     */
    public function setCsId($csId)
    {
        $this->csId = $csId;

        return $this;
    }

    /**
     * Get csId.
     *
     * @return int
     */
    public function getCsId()
    {
        return $this->csId;
    }

    /**
     * Set refId.
     *
     * @param int|null $refId
     *
     * @return EcsNodeMappingA
     */
    public function setRefId($refId = null)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int|null
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set objId.
     *
     * @param int|null $objId
     *
     * @return EcsNodeMappingA
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set titleUpdate.
     *
     * @param bool|null $titleUpdate
     *
     * @return EcsNodeMappingA
     */
    public function setTitleUpdate($titleUpdate = null)
    {
        $this->titleUpdate = $titleUpdate;

        return $this;
    }

    /**
     * Get titleUpdate.
     *
     * @return bool|null
     */
    public function getTitleUpdate()
    {
        return $this->titleUpdate;
    }

    /**
     * Set positionUpdate.
     *
     * @param bool|null $positionUpdate
     *
     * @return EcsNodeMappingA
     */
    public function setPositionUpdate($positionUpdate = null)
    {
        $this->positionUpdate = $positionUpdate;

        return $this;
    }

    /**
     * Get positionUpdate.
     *
     * @return bool|null
     */
    public function getPositionUpdate()
    {
        return $this->positionUpdate;
    }

    /**
     * Set treeUpdate.
     *
     * @param bool|null $treeUpdate
     *
     * @return EcsNodeMappingA
     */
    public function setTreeUpdate($treeUpdate = null)
    {
        $this->treeUpdate = $treeUpdate;

        return $this;
    }

    /**
     * Get treeUpdate.
     *
     * @return bool|null
     */
    public function getTreeUpdate()
    {
        return $this->treeUpdate;
    }
}
