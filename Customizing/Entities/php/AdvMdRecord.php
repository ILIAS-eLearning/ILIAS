<?php



/**
 * AdvMdRecord
 */
class AdvMdRecord
{
    /**
     * @var int
     */
    private $recordId = '0';

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var bool
     */
    private $active = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int|null
     */
    private $parentObj;

    /**
     * @var int|null
     */
    private $gpos;


    /**
     * Get recordId.
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return AdvMdRecord
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

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return AdvMdRecord
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return AdvMdRecord
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
     * @return AdvMdRecord
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
     * Set parentObj.
     *
     * @param int|null $parentObj
     *
     * @return AdvMdRecord
     */
    public function setParentObj($parentObj = null)
    {
        $this->parentObj = $parentObj;

        return $this;
    }

    /**
     * Get parentObj.
     *
     * @return int|null
     */
    public function getParentObj()
    {
        return $this->parentObj;
    }

    /**
     * Set gpos.
     *
     * @param int|null $gpos
     *
     * @return AdvMdRecord
     */
    public function setGpos($gpos = null)
    {
        $this->gpos = $gpos;

        return $this;
    }

    /**
     * Get gpos.
     *
     * @return int|null
     */
    public function getGpos()
    {
        return $this->gpos;
    }
}
