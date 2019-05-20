<?php



/**
 * EcsExport
 */
class EcsExport
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
    private $econtentId = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return EcsExport
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
     * @return EcsExport
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
     * Set econtentId.
     *
     * @param int $econtentId
     *
     * @return EcsExport
     */
    public function setEcontentId($econtentId)
    {
        $this->econtentId = $econtentId;

        return $this;
    }

    /**
     * Get econtentId.
     *
     * @return int
     */
    public function getEcontentId()
    {
        return $this->econtentId;
    }
}
