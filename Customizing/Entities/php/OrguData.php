<?php



/**
 * OrguData
 */
class OrguData
{
    /**
     * @var int
     */
    private $orguId = '0';

    /**
     * @var int|null
     */
    private $orguTypeId;


    /**
     * Get orguId.
     *
     * @return int
     */
    public function getOrguId()
    {
        return $this->orguId;
    }

    /**
     * Set orguTypeId.
     *
     * @param int|null $orguTypeId
     *
     * @return OrguData
     */
    public function setOrguTypeId($orguTypeId = null)
    {
        $this->orguTypeId = $orguTypeId;

        return $this;
    }

    /**
     * Get orguTypeId.
     *
     * @return int|null
     */
    public function getOrguTypeId()
    {
        return $this->orguTypeId;
    }
}
