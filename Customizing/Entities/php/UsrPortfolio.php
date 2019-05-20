<?php



/**
 * UsrPortfolio
 */
class UsrPortfolio
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $isOnline;

    /**
     * @var bool|null
     */
    private $isDefault;

    /**
     * @var string|null
     */
    private $bgColor;

    /**
     * @var string|null
     */
    private $fontColor;

    /**
     * @var string|null
     */
    private $img;

    /**
     * @var bool|null
     */
    private $ppic;


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
     * Set isOnline.
     *
     * @param bool|null $isOnline
     *
     * @return UsrPortfolio
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set isDefault.
     *
     * @param bool|null $isDefault
     *
     * @return UsrPortfolio
     */
    public function setIsDefault($isDefault = null)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool|null
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set bgColor.
     *
     * @param string|null $bgColor
     *
     * @return UsrPortfolio
     */
    public function setBgColor($bgColor = null)
    {
        $this->bgColor = $bgColor;

        return $this;
    }

    /**
     * Get bgColor.
     *
     * @return string|null
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * Set fontColor.
     *
     * @param string|null $fontColor
     *
     * @return UsrPortfolio
     */
    public function setFontColor($fontColor = null)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * Get fontColor.
     *
     * @return string|null
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    /**
     * Set img.
     *
     * @param string|null $img
     *
     * @return UsrPortfolio
     */
    public function setImg($img = null)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img.
     *
     * @return string|null
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set ppic.
     *
     * @param bool|null $ppic
     *
     * @return UsrPortfolio
     */
    public function setPpic($ppic = null)
    {
        $this->ppic = $ppic;

        return $this;
    }

    /**
     * Get ppic.
     *
     * @return bool|null
     */
    public function getPpic()
    {
        return $this->ppic;
    }
}
