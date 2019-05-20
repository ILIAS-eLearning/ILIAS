<?php



/**
 * DidacticTplA
 */
class DidacticTplA
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $tplId = '0';

    /**
     * @var bool
     */
    private $typeId = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tplId.
     *
     * @param int $tplId
     *
     * @return DidacticTplA
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
     * Set typeId.
     *
     * @param bool $typeId
     *
     * @return DidacticTplA
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return bool
     */
    public function getTypeId()
    {
        return $this->typeId;
    }
}
