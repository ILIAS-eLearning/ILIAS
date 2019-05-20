<?php



/**
 * GloTermReference
 */
class GloTermReference
{
    /**
     * @var int
     */
    private $gloId = '0';

    /**
     * @var int
     */
    private $termId = '0';


    /**
     * Set gloId.
     *
     * @param int $gloId
     *
     * @return GloTermReference
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
     * Set termId.
     *
     * @param int $termId
     *
     * @return GloTermReference
     */
    public function setTermId($termId)
    {
        $this->termId = $termId;

        return $this;
    }

    /**
     * Get termId.
     *
     * @return int
     */
    public function getTermId()
    {
        return $this->termId;
    }
}
