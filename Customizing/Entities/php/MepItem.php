<?php



/**
 * MepItem
 */
class MepItem
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var int|null
     */
    private $foreignId;

    /**
     * @var string|null
     */
    private $importId;


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
     * Set type.
     *
     * @param string|null $type
     *
     * @return MepItem
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
     * @return MepItem
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
     * Set foreignId.
     *
     * @param int|null $foreignId
     *
     * @return MepItem
     */
    public function setForeignId($foreignId = null)
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * Get foreignId.
     *
     * @return int|null
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return MepItem
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
