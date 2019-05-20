<?php



/**
 * IlExcTeam
 */
class IlExcTeam
{
    /**
     * @var int
     */
    private $assId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $id = '0';


    /**
     * Set assId.
     *
     * @param int $assId
     *
     * @return IlExcTeam
     */
    public function setAssId($assId)
    {
        $this->assId = $assId;

        return $this;
    }

    /**
     * Get assId.
     *
     * @return int
     */
    public function getAssId()
    {
        return $this->assId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlExcTeam
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
     * Set id.
     *
     * @param int $id
     *
     * @return IlExcTeam
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
}
