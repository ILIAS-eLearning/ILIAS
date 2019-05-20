<?php



/**
 * IlCustomBlock
 */
class IlCustomBlock
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $contextObjId;

    /**
     * @var string|null
     */
    private $contextObjType;

    /**
     * @var int|null
     */
    private $contextSubObjId;

    /**
     * @var string|null
     */
    private $contextSubObjType;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $title;


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
     * Set contextObjId.
     *
     * @param int|null $contextObjId
     *
     * @return IlCustomBlock
     */
    public function setContextObjId($contextObjId = null)
    {
        $this->contextObjId = $contextObjId;

        return $this;
    }

    /**
     * Get contextObjId.
     *
     * @return int|null
     */
    public function getContextObjId()
    {
        return $this->contextObjId;
    }

    /**
     * Set contextObjType.
     *
     * @param string|null $contextObjType
     *
     * @return IlCustomBlock
     */
    public function setContextObjType($contextObjType = null)
    {
        $this->contextObjType = $contextObjType;

        return $this;
    }

    /**
     * Get contextObjType.
     *
     * @return string|null
     */
    public function getContextObjType()
    {
        return $this->contextObjType;
    }

    /**
     * Set contextSubObjId.
     *
     * @param int|null $contextSubObjId
     *
     * @return IlCustomBlock
     */
    public function setContextSubObjId($contextSubObjId = null)
    {
        $this->contextSubObjId = $contextSubObjId;

        return $this;
    }

    /**
     * Get contextSubObjId.
     *
     * @return int|null
     */
    public function getContextSubObjId()
    {
        return $this->contextSubObjId;
    }

    /**
     * Set contextSubObjType.
     *
     * @param string|null $contextSubObjType
     *
     * @return IlCustomBlock
     */
    public function setContextSubObjType($contextSubObjType = null)
    {
        $this->contextSubObjType = $contextSubObjType;

        return $this;
    }

    /**
     * Get contextSubObjType.
     *
     * @return string|null
     */
    public function getContextSubObjType()
    {
        return $this->contextSubObjType;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return IlCustomBlock
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlCustomBlock
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
