<?php



/**
 * CrsObjectives
 */
class CrsObjectives
{
    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var int
     */
    private $crsId = '0';

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
    private $position = '0';

    /**
     * @var int
     */
    private $created = '0';

    /**
     * @var bool|null
     */
    private $active = '1';

    /**
     * @var int|null
     */
    private $passes = '0';


    /**
     * Get objectiveId.
     *
     * @return int
     */
    public function getObjectiveId()
    {
        return $this->objectiveId;
    }

    /**
     * Set crsId.
     *
     * @param int $crsId
     *
     * @return CrsObjectives
     */
    public function setCrsId($crsId)
    {
        $this->crsId = $crsId;

        return $this;
    }

    /**
     * Get crsId.
     *
     * @return int
     */
    public function getCrsId()
    {
        return $this->crsId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return CrsObjectives
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
     * @return CrsObjectives
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
     * Set position.
     *
     * @param int $position
     *
     * @return CrsObjectives
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set created.
     *
     * @param int $created
     *
     * @return CrsObjectives
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return CrsObjectives
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set passes.
     *
     * @param int|null $passes
     *
     * @return CrsObjectives
     */
    public function setPasses($passes = null)
    {
        $this->passes = $passes;

        return $this;
    }

    /**
     * Get passes.
     *
     * @return int|null
     */
    public function getPasses()
    {
        return $this->passes;
    }
}
