<?php



/**
 * StyleSetting
 */
class StyleSetting
{
    /**
     * @var int
     */
    private $styleId = '0';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleSetting
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
     * Set name.
     *
     * @param string $name
     *
     * @return StyleSetting
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return StyleSetting
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
