<?php



/**
 * StyleColor
 */
class StyleColor
{
    /**
     * @var int
     */
    private $styleId = '0';

    /**
     * @var string
     */
    private $colorName = '.';

    /**
     * @var string|null
     */
    private $colorCode;


    /**
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleColor
     */
    public function setStyleId($styleId)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return int
     */
    public function getStyleId()
    {
        return $this->styleId;
    }

    /**
     * Set colorName.
     *
     * @param string $colorName
     *
     * @return StyleColor
     */
    public function setColorName($colorName)
    {
        $this->colorName = $colorName;

        return $this;
    }

    /**
     * Get colorName.
     *
     * @return string
     */
    public function getColorName()
    {
        return $this->colorName;
    }

    /**
     * Set colorCode.
     *
     * @param string|null $colorCode
     *
     * @return StyleColor
     */
    public function setColorCode($colorCode = null)
    {
        $this->colorCode = $colorCode;

        return $this;
    }

    /**
     * Get colorCode.
     *
     * @return string|null
     */
    public function getColorCode()
    {
        return $this->colorCode;
    }
}
