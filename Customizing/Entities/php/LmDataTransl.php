<?php



/**
 * LmDataTransl
 */
class LmDataTransl
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $lang = '';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var string|null
     */
    private $shortTitle;


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return LmDataTransl
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set lang.
     *
     * @param string $lang
     *
     * @return LmDataTransl
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return LmDataTransl
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
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return LmDataTransl
     */
    public function setCreateDate($createDate = null)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime|null
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return LmDataTransl
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set shortTitle.
     *
     * @param string|null $shortTitle
     *
     * @return LmDataTransl
     */
    public function setShortTitle($shortTitle = null)
    {
        $this->shortTitle = $shortTitle;

        return $this;
    }

    /**
     * Get shortTitle.
     *
     * @return string|null
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
    }
}
