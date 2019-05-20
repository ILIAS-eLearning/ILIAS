<?php



/**
 * CacheClob
 */
class CacheClob
{
    /**
     * @var string
     */
    private $component = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $entryId = '';

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var int
     */
    private $expireTime = '0';

    /**
     * @var string|null
     */
    private $iliasVersion;

    /**
     * @var int|null
     */
    private $intKey1;

    /**
     * @var int|null
     */
    private $intKey2;

    /**
     * @var string|null
     */
    private $textKey1;

    /**
     * @var string|null
     */
    private $textKey2;


    /**
     * Set component.
     *
     * @param string $component
     *
     * @return CacheClob
     */
    public function setComponent($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return CacheClob
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
     * Set entryId.
     *
     * @param string $entryId
     *
     * @return CacheClob
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * Get entryId.
     *
     * @return string
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return CacheClob
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
     * Set expireTime.
     *
     * @param int $expireTime
     *
     * @return CacheClob
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;

        return $this;
    }

    /**
     * Get expireTime.
     *
     * @return int
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * Set iliasVersion.
     *
     * @param string|null $iliasVersion
     *
     * @return CacheClob
     */
    public function setIliasVersion($iliasVersion = null)
    {
        $this->iliasVersion = $iliasVersion;

        return $this;
    }

    /**
     * Get iliasVersion.
     *
     * @return string|null
     */
    public function getIliasVersion()
    {
        return $this->iliasVersion;
    }

    /**
     * Set intKey1.
     *
     * @param int|null $intKey1
     *
     * @return CacheClob
     */
    public function setIntKey1($intKey1 = null)
    {
        $this->intKey1 = $intKey1;

        return $this;
    }

    /**
     * Get intKey1.
     *
     * @return int|null
     */
    public function getIntKey1()
    {
        return $this->intKey1;
    }

    /**
     * Set intKey2.
     *
     * @param int|null $intKey2
     *
     * @return CacheClob
     */
    public function setIntKey2($intKey2 = null)
    {
        $this->intKey2 = $intKey2;

        return $this;
    }

    /**
     * Get intKey2.
     *
     * @return int|null
     */
    public function getIntKey2()
    {
        return $this->intKey2;
    }

    /**
     * Set textKey1.
     *
     * @param string|null $textKey1
     *
     * @return CacheClob
     */
    public function setTextKey1($textKey1 = null)
    {
        $this->textKey1 = $textKey1;

        return $this;
    }

    /**
     * Get textKey1.
     *
     * @return string|null
     */
    public function getTextKey1()
    {
        return $this->textKey1;
    }

    /**
     * Set textKey2.
     *
     * @param string|null $textKey2
     *
     * @return CacheClob
     */
    public function setTextKey2($textKey2 = null)
    {
        $this->textKey2 = $textKey2;

        return $this;
    }

    /**
     * Get textKey2.
     *
     * @return string|null
     */
    public function getTextKey2()
    {
        return $this->textKey2;
    }
}
