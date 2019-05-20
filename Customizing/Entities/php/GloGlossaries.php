<?php



/**
 * GloGlossaries
 */
class GloGlossaries
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $gloId = '0';


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return GloGlossaries
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set gloId.
     *
     * @param int $gloId
     *
     * @return GloGlossaries
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
