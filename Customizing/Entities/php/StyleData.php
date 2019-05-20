<?php



/**
 * StyleData
 */
class StyleData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $uptodate = '0';

    /**
     * @var bool|null
     */
    private $standard = '0';

    /**
     * @var int|null
     */
    private $category;

    /**
     * @var bool|null
     */
    private $active = '1';


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
     * Set uptodate.
     *
     * @param bool|null $uptodate
     *
     * @return StyleData
     */
    public function setUptodate($uptodate = null)
    {
        $this->uptodate = $uptodate;

        return $this;
    }

    /**
     * Get uptodate.
     *
     * @return bool|null
     */
    public function getUptodate()
    {
        return $this->uptodate;
    }

    /**
     * Set standard.
     *
     * @param bool|null $standard
     *
     * @return StyleData
     */
    public function setStandard($standard = null)
    {
        $this->standard = $standard;

        return $this;
    }

    /**
     * Get standard.
     *
     * @return bool|null
     */
    public function getStandard()
    {
        return $this->standard;
    }

    /**
     * Set category.
     *
     * @param int|null $category
     *
     * @return StyleData
     */
    public function setCategory($category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return int|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return StyleData
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
}
