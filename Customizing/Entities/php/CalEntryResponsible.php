<?php



/**
 * CalEntryResponsible
 */
class CalEntryResponsible
{
    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalEntryResponsible
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return CalEntryResponsible
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
