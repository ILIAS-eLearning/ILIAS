<?php



/**
 * DidacticTplObjs
 */
class DidacticTplObjs
{
    /**
     * @var int
     */
    private $tplId = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $objId = '0';


    /**
     * Set tplId.
     *
     * @param int $tplId
     *
     * @return DidacticTplObjs
     */
    public function setTplId($tplId)
    {
        $this->tplId = $tplId;

        return $this;
    }

    /**
     * Get tplId.
     *
     * @return int
     */
    public function getTplId()
    {
        return $this->tplId;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return DidacticTplObjs
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return DidacticTplObjs
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
}
