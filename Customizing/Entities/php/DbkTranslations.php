<?php



/**
 * DbkTranslations
 */
class DbkTranslations
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $trId = '0';


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return DbkTranslations
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
     * Set trId.
     *
     * @param int $trId
     *
     * @return DbkTranslations
     */
    public function setTrId($trId)
    {
        $this->trId = $trId;

        return $this;
    }

    /**
     * Get trId.
     *
     * @return int
     */
    public function getTrId()
    {
        return $this->trId;
    }
}
