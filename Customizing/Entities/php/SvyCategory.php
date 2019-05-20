<?php



/**
 * SvyCategory
 */
class SvyCategory
{
    /**
     * @var int
     */
    private $categoryId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $defaultvalue = '0';

    /**
     * @var int
     */
    private $ownerFi = '0';

    /**
     * @var string|null
     */
    private $neutral = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get categoryId.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SvyCategory
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
     * Set defaultvalue.
     *
     * @param string|null $defaultvalue
     *
     * @return SvyCategory
     */
    public function setDefaultvalue($defaultvalue = null)
    {
        $this->defaultvalue = $defaultvalue;

        return $this;
    }

    /**
     * Get defaultvalue.
     *
     * @return string|null
     */
    public function getDefaultvalue()
    {
        return $this->defaultvalue;
    }

    /**
     * Set ownerFi.
     *
     * @param int $ownerFi
     *
     * @return SvyCategory
     */
    public function setOwnerFi($ownerFi)
    {
        $this->ownerFi = $ownerFi;

        return $this;
    }

    /**
     * Get ownerFi.
     *
     * @return int
     */
    public function getOwnerFi()
    {
        return $this->ownerFi;
    }

    /**
     * Set neutral.
     *
     * @param string|null $neutral
     *
     * @return SvyCategory
     */
    public function setNeutral($neutral = null)
    {
        $this->neutral = $neutral;

        return $this;
    }

    /**
     * Get neutral.
     *
     * @return string|null
     */
    public function getNeutral()
    {
        return $this->neutral;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyCategory
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
