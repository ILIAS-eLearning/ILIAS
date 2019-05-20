<?php



/**
 * LngData
 */
class LngData
{
    /**
     * @var string
     */
    private $module = ' ';

    /**
     * @var string
     */
    private $identifier = ' ';

    /**
     * @var string
     */
    private $langKey = '';

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var \DateTime|null
     */
    private $localChange;

    /**
     * @var string|null
     */
    private $remarks;


    /**
     * Set module.
     *
     * @param string $module
     *
     * @return LngData
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module.
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set identifier.
     *
     * @param string $identifier
     *
     * @return LngData
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set langKey.
     *
     * @param string $langKey
     *
     * @return LngData
     */
    public function setLangKey($langKey)
    {
        $this->langKey = $langKey;

        return $this;
    }

    /**
     * Get langKey.
     *
     * @return string
     */
    public function getLangKey()
    {
        return $this->langKey;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return LngData
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

    /**
     * Set localChange.
     *
     * @param \DateTime|null $localChange
     *
     * @return LngData
     */
    public function setLocalChange($localChange = null)
    {
        $this->localChange = $localChange;

        return $this;
    }

    /**
     * Get localChange.
     *
     * @return \DateTime|null
     */
    public function getLocalChange()
    {
        return $this->localChange;
    }

    /**
     * Set remarks.
     *
     * @param string|null $remarks
     *
     * @return LngData
     */
    public function setRemarks($remarks = null)
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * Get remarks.
     *
     * @return string|null
     */
    public function getRemarks()
    {
        return $this->remarks;
    }
}
