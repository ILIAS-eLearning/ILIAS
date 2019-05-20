<?php



/**
 * HelpModule
 */
class HelpModule
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $lmId = '0';


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
     * Set lmId.
     *
     * @param int $lmId
     *
     * @return HelpModule
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
}
