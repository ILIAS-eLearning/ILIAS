<?php



/**
 * CalRecExclusion
 */
class CalRecExclusion
{
    /**
     * @var int
     */
    private $exclId = '0';

    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var \DateTime|null
     */
    private $exclDate;


    /**
     * Get exclId.
     *
     * @return int
     */
    public function getExclId()
    {
        return $this->exclId;
    }

    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalRecExclusion
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
     * Set exclDate.
     *
     * @param \DateTime|null $exclDate
     *
     * @return CalRecExclusion
     */
    public function setExclDate($exclDate = null)
    {
        $this->exclDate = $exclDate;

        return $this;
    }

    /**
     * Get exclDate.
     *
     * @return \DateTime|null
     */
    public function getExclDate()
    {
        return $this->exclDate;
    }
}
