<?php



/**
 * CalCatAssignments
 */
class CalCatAssignments
{
    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var int
     */
    private $catId = '0';


    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalCatAssignments
     */
    public function setCalId($calId)
    {
        $this->calId = $calId;

        return $this;
    }

    /**
     * Get calId.
     *
     * @return int
     */
    public function getCalId()
    {
        return $this->calId;
    }

    /**
     * Set catId.
     *
     * @param int $catId
     *
     * @return CalCatAssignments
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;

        return $this;
    }

    /**
     * Get catId.
     *
     * @return int
     */
    public function getCatId()
    {
        return $this->catId;
    }
}
