<?php



/**
 * SystStyleCat
 */
class SystStyleCat
{
    /**
     * @var string
     */
    private $skinId = '';

    /**
     * @var string
     */
    private $styleId = '';

    /**
     * @var string
     */
    private $substyle = '';

    /**
     * @var int
     */
    private $categoryRefId = '0';


    /**
     * Set skinId.
     *
     * @param string $skinId
     *
     * @return SystStyleCat
     */
    public function setSkinId($skinId)
    {
        $this->skinId = $skinId;

        return $this;
    }

    /**
     * Get skinId.
     *
     * @return string
     */
    public function getSkinId()
    {
        return $this->skinId;
    }

    /**
     * Set styleId.
     *
     * @param string $styleId
     *
     * @return SystStyleCat
     */
    public function setStyleId($styleId)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return string
     */
    public function getStyleId()
    {
        return $this->styleId;
    }

    /**
     * Set substyle.
     *
     * @param string $substyle
     *
     * @return SystStyleCat
     */
    public function setSubstyle($substyle)
    {
        $this->substyle = $substyle;

        return $this;
    }

    /**
     * Get substyle.
     *
     * @return string
     */
    public function getSubstyle()
    {
        return $this->substyle;
    }

    /**
     * Set categoryRefId.
     *
     * @param int $categoryRefId
     *
     * @return SystStyleCat
     */
    public function setCategoryRefId($categoryRefId)
    {
        $this->categoryRefId = $categoryRefId;

        return $this;
    }

    /**
     * Get categoryRefId.
     *
     * @return int
     */
    public function getCategoryRefId()
    {
        return $this->categoryRefId;
    }
}
