<?php



/**
 * IlDclField
 */
class IlDclField
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $tableId = '0';

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
    private $datatypeId = '0';

    /**
     * @var bool
     */
    private $required = '0';

    /**
     * @var bool
     */
    private $isUnique = '0';

    /**
     * @var bool
     */
    private $isLocked = '0';


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
     * Set tableId.
     *
     * @param int $tableId
     *
     * @return IlDclField
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;

        return $this;
    }

    /**
     * Get tableId.
     *
     * @return int
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlDclField
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
     * @return IlDclField
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
     * Set datatypeId.
     *
     * @param int $datatypeId
     *
     * @return IlDclField
     */
    public function setDatatypeId($datatypeId)
    {
        $this->datatypeId = $datatypeId;

        return $this;
    }

    /**
     * Get datatypeId.
     *
     * @return int
     */
    public function getDatatypeId()
    {
        return $this->datatypeId;
    }

    /**
     * Set required.
     *
     * @param bool $required
     *
     * @return IlDclField
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set isUnique.
     *
     * @param bool $isUnique
     *
     * @return IlDclField
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * Get isUnique.
     *
     * @return bool
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * Set isLocked.
     *
     * @param bool $isLocked
     *
     * @return IlDclField
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Get isLocked.
     *
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }
}
