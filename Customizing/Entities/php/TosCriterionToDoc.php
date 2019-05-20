<?php



/**
 * TosCriterionToDoc
 */
class TosCriterionToDoc
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $docId = '0';

    /**
     * @var string
     */
    private $criterionId;

    /**
     * @var string|null
     */
    private $criterionValue;

    /**
     * @var int
     */
    private $assignedTs = '0';

    /**
     * @var int
     */
    private $modificationTs = '0';

    /**
     * @var int
     */
    private $ownerUsrId = '0';

    /**
     * @var int
     */
    private $lastModifiedUsrId = '0';


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
     * Set docId.
     *
     * @param int $docId
     *
     * @return TosCriterionToDoc
     */
    public function setDocId($docId)
    {
        $this->docId = $docId;

        return $this;
    }

    /**
     * Get docId.
     *
     * @return int
     */
    public function getDocId()
    {
        return $this->docId;
    }

    /**
     * Set criterionId.
     *
     * @param string $criterionId
     *
     * @return TosCriterionToDoc
     */
    public function setCriterionId($criterionId)
    {
        $this->criterionId = $criterionId;

        return $this;
    }

    /**
     * Get criterionId.
     *
     * @return string
     */
    public function getCriterionId()
    {
        return $this->criterionId;
    }

    /**
     * Set criterionValue.
     *
     * @param string|null $criterionValue
     *
     * @return TosCriterionToDoc
     */
    public function setCriterionValue($criterionValue = null)
    {
        $this->criterionValue = $criterionValue;

        return $this;
    }

    /**
     * Get criterionValue.
     *
     * @return string|null
     */
    public function getCriterionValue()
    {
        return $this->criterionValue;
    }

    /**
     * Set assignedTs.
     *
     * @param int $assignedTs
     *
     * @return TosCriterionToDoc
     */
    public function setAssignedTs($assignedTs)
    {
        $this->assignedTs = $assignedTs;

        return $this;
    }

    /**
     * Get assignedTs.
     *
     * @return int
     */
    public function getAssignedTs()
    {
        return $this->assignedTs;
    }

    /**
     * Set modificationTs.
     *
     * @param int $modificationTs
     *
     * @return TosCriterionToDoc
     */
    public function setModificationTs($modificationTs)
    {
        $this->modificationTs = $modificationTs;

        return $this;
    }

    /**
     * Get modificationTs.
     *
     * @return int
     */
    public function getModificationTs()
    {
        return $this->modificationTs;
    }

    /**
     * Set ownerUsrId.
     *
     * @param int $ownerUsrId
     *
     * @return TosCriterionToDoc
     */
    public function setOwnerUsrId($ownerUsrId)
    {
        $this->ownerUsrId = $ownerUsrId;

        return $this;
    }

    /**
     * Get ownerUsrId.
     *
     * @return int
     */
    public function getOwnerUsrId()
    {
        return $this->ownerUsrId;
    }

    /**
     * Set lastModifiedUsrId.
     *
     * @param int $lastModifiedUsrId
     *
     * @return TosCriterionToDoc
     */
    public function setLastModifiedUsrId($lastModifiedUsrId)
    {
        $this->lastModifiedUsrId = $lastModifiedUsrId;

        return $this;
    }

    /**
     * Get lastModifiedUsrId.
     *
     * @return int
     */
    public function getLastModifiedUsrId()
    {
        return $this->lastModifiedUsrId;
    }
}
