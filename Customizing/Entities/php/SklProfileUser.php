<?php



/**
 * SklProfileUser
 */
class SklProfileUser
{
    /**
     * @var int
     */
    private $profileId = '0';

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Set profileId.
     *
     * @param int $profileId
     *
     * @return SklProfileUser
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * Get profileId.
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklProfileUser
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
