<?php



/**
 * CalChSettings
 */
class CalChSettings
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $adminId = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CalChSettings
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

    /**
     * Set adminId.
     *
     * @param int $adminId
     *
     * @return CalChSettings
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Get adminId.
     *
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
    }
}
