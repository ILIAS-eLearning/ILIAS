<?php



/**
 * BadgeBadge
 */
class BadgeBadge
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $parentId = '0';

    /**
     * @var string|null
     */
    private $typeId;

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
    private $descr;

    /**
     * @var string|null
     */
    private $conf;

    /**
     * @var string|null
     */
    private $image;

    /**
     * @var string|null
     */
    private $valid;

    /**
     * @var string|null
     */
    private $crit;


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
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return BadgeBadge
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set typeId.
     *
     * @param string|null $typeId
     *
     * @return BadgeBadge
     */
    public function setTypeId($typeId = null)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return string|null
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return BadgeBadge
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
     * @return BadgeBadge
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
     * Set descr.
     *
     * @param string|null $descr
     *
     * @return BadgeBadge
     */
    public function setDescr($descr = null)
    {
        $this->descr = $descr;

        return $this;
    }

    /**
     * Get descr.
     *
     * @return string|null
     */
    public function getDescr()
    {
        return $this->descr;
    }

    /**
     * Set conf.
     *
     * @param string|null $conf
     *
     * @return BadgeBadge
     */
    public function setConf($conf = null)
    {
        $this->conf = $conf;

        return $this;
    }

    /**
     * Get conf.
     *
     * @return string|null
     */
    public function getConf()
    {
        return $this->conf;
    }

    /**
     * Set image.
     *
     * @param string|null $image
     *
     * @return BadgeBadge
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set valid.
     *
     * @param string|null $valid
     *
     * @return BadgeBadge
     */
    public function setValid($valid = null)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid.
     *
     * @return string|null
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set crit.
     *
     * @param string|null $crit
     *
     * @return BadgeBadge
     */
    public function setCrit($crit = null)
    {
        $this->crit = $crit;

        return $this;
    }

    /**
     * Get crit.
     *
     * @return string|null
     */
    public function getCrit()
    {
        return $this->crit;
    }
}
