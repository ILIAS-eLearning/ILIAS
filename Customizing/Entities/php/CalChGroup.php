<?php



/**
 * CalChGroup
 */
class CalChGroup
{
    /**
     * @var int
     */
    private $grpId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var bool
     */
    private $multipleAssignments = '0';

    /**
     * @var string|null
     */
    private $title;


    /**
     * Get grpId.
     *
     * @return int
     */
    public function getGrpId()
    {
        return $this->grpId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CalChGroup
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set multipleAssignments.
     *
     * @param bool $multipleAssignments
     *
     * @return CalChGroup
     */
    public function setMultipleAssignments($multipleAssignments)
    {
        $this->multipleAssignments = $multipleAssignments;

        return $this;
    }

    /**
     * Get multipleAssignments.
     *
     * @return bool
     */
    public function getMultipleAssignments()
    {
        return $this->multipleAssignments;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return CalChGroup
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
}
