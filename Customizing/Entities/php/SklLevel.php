<?php



/**
 * SklLevel
 */
class SklLevel
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $skillId = '0';

    /**
     * @var int
     */
    private $nr = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int
     */
    private $triggerRefId = '0';

    /**
     * @var int
     */
    private $triggerObjId = '0';

    /**
     * @var \DateTime|null
     */
    private $creationDate;

    /**
     * @var string|null
     */
    private $importId;


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
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return SklLevel
     */
    public function setSkillId($skillId)
    {
        $this->skillId = $skillId;

        return $this;
    }

    /**
     * Get skillId.
     *
     * @return int
     */
    public function getSkillId()
    {
        return $this->skillId;
    }

    /**
     * Set nr.
     *
     * @param int $nr
     *
     * @return SklLevel
     */
    public function setNr($nr)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SklLevel
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

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return SklLevel
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set triggerRefId.
     *
     * @param int $triggerRefId
     *
     * @return SklLevel
     */
    public function setTriggerRefId($triggerRefId)
    {
        $this->triggerRefId = $triggerRefId;

        return $this;
    }

    /**
     * Get triggerRefId.
     *
     * @return int
     */
    public function getTriggerRefId()
    {
        return $this->triggerRefId;
    }

    /**
     * Set triggerObjId.
     *
     * @param int $triggerObjId
     *
     * @return SklLevel
     */
    public function setTriggerObjId($triggerObjId)
    {
        $this->triggerObjId = $triggerObjId;

        return $this;
    }

    /**
     * Get triggerObjId.
     *
     * @return int
     */
    public function getTriggerObjId()
    {
        return $this->triggerObjId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime|null $creationDate
     *
     * @return SklLevel
     */
    public function setCreationDate($creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return SklLevel
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }
}
