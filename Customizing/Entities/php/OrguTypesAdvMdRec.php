<?php



/**
 * OrguTypesAdvMdRec
 */
class OrguTypesAdvMdRec
{
    /**
     * @var int
     */
    private $typeId = '0';

    /**
     * @var int
     */
    private $recId = '0';


    /**
     * Set typeId.
     *
     * @param int $typeId
     *
     * @return OrguTypesAdvMdRec
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set recId.
     *
     * @param int $recId
     *
     * @return OrguTypesAdvMdRec
     */
    public function setRecId($recId)
    {
        $this->recId = $recId;

        return $this;
    }

    /**
     * Get recId.
     *
     * @return int
     */
    public function getRecId()
    {
        return $this->recId;
    }
}
