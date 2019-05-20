<?php



/**
 * PrgTypeAdvMdRec
 */
class PrgTypeAdvMdRec
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $typeId;

    /**
     * @var int|null
     */
    private $recId;


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
     * Set typeId.
     *
     * @param int|null $typeId
     *
     * @return PrgTypeAdvMdRec
     */
    public function setTypeId($typeId = null)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return int|null
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set recId.
     *
     * @param int|null $recId
     *
     * @return PrgTypeAdvMdRec
     */
    public function setRecId($recId = null)
    {
        $this->recId = $recId;

        return $this;
    }

    /**
     * Get recId.
     *
     * @return int|null
     */
    public function getRecId()
    {
        return $this->recId;
    }
}
