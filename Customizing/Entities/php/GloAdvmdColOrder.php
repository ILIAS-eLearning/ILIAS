<?php



/**
 * GloAdvmdColOrder
 */
class GloAdvmdColOrder
{
    /**
     * @var int
     */
    private $gloId = '0';

    /**
     * @var int
     */
    private $fieldId = '0';

    /**
     * @var int
     */
    private $orderNr = '0';


    /**
     * Set gloId.
     *
     * @param int $gloId
     *
     * @return GloAdvmdColOrder
     */
    public function setGloId($gloId)
    {
        $this->gloId = $gloId;

        return $this;
    }

    /**
     * Get gloId.
     *
     * @return int
     */
    public function getGloId()
    {
        return $this->gloId;
    }

    /**
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return GloAdvmdColOrder
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Get fieldId.
     *
     * @return int
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return GloAdvmdColOrder
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }
}
