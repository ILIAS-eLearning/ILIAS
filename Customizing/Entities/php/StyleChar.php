<?php



/**
 * StyleChar
 */
class StyleChar
{
    /**
     * @var int
     */
    private $styleId = '0';

    /**
     * @var string
     */
    private $type = ' ';

    /**
     * @var string
     */
    private $characteristic = ' ';

    /**
     * @var bool
     */
    private $hide = '0';


    /**
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleChar
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
     * Set type.
     *
     * @param string $type
     *
     * @return StyleChar
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set characteristic.
     *
     * @param string $characteristic
     *
     * @return StyleChar
     */
    public function setCharacteristic($characteristic)
    {
        $this->characteristic = $characteristic;

        return $this;
    }

    /**
     * Get characteristic.
     *
     * @return string
     */
    public function getCharacteristic()
    {
        return $this->characteristic;
    }

    /**
     * Set hide.
     *
     * @param bool $hide
     *
     * @return StyleChar
     */
    public function setHide($hide)
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * Get hide.
     *
     * @return bool
     */
    public function getHide()
    {
        return $this->hide;
    }
}
