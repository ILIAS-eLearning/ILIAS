<?php



/**
 * IlDclDatatype
 */
class IlDclDatatype
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string
     */
    private $ildbType = '';

    /**
     * @var int
     */
    private $storageLocation = '0';

    /**
     * @var int|null
     */
    private $sort = '0';


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
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlDclDatatype
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
     * Set ildbType.
     *
     * @param string $ildbType
     *
     * @return IlDclDatatype
     */
    public function setIldbType($ildbType)
    {
        $this->ildbType = $ildbType;

        return $this;
    }

    /**
     * Get ildbType.
     *
     * @return string
     */
    public function getIldbType()
    {
        return $this->ildbType;
    }

    /**
     * Set storageLocation.
     *
     * @param int $storageLocation
     *
     * @return IlDclDatatype
     */
    public function setStorageLocation($storageLocation)
    {
        $this->storageLocation = $storageLocation;

        return $this;
    }

    /**
     * Get storageLocation.
     *
     * @return int
     */
    public function getStorageLocation()
    {
        return $this->storageLocation;
    }

    /**
     * Set sort.
     *
     * @param int|null $sort
     *
     * @return IlDclDatatype
     */
    public function setSort($sort = null)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     *
     * @return int|null
     */
    public function getSort()
    {
        return $this->sort;
    }
}
