<?php



/**
 * CalCatVisibility
 */
class CalCatVisibility
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $catId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool
     */
    private $visible = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CalCatVisibility
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
     * Set catId.
     *
     * @param int $catId
     *
     * @return CalCatVisibility
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;

        return $this;
    }

    /**
     * Get catId.
     *
     * @return int
     */
    public function getCatId()
    {
        return $this->catId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CalCatVisibility
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return CalCatVisibility
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }
}
