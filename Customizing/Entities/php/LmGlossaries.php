<?php



/**
 * LmGlossaries
 */
class LmGlossaries
{
    /**
     * @var int
     */
    private $lmId = '0';

    /**
     * @var int
     */
    private $gloId = '0';


    /**
     * Set lmId.
     *
     * @param int $lmId
     *
     * @return LmGlossaries
     */
    public function setLmId($lmId)
    {
        $this->lmId = $lmId;

        return $this;
    }

    /**
     * Get lmId.
     *
     * @return int
     */
    public function getLmId()
    {
        return $this->lmId;
    }

    /**
     * Set gloId.
     *
     * @param int $gloId
     *
     * @return LmGlossaries
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
}
