<?php



/**
 * WebrParams
 */
class WebrParams
{
    /**
     * @var int
     */
    private $paramId = '0';

    /**
     * @var int
     */
    private $webrId = '0';

    /**
     * @var int
     */
    private $linkId = '0';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var bool
     */
    private $value = '0';


    /**
     * Get paramId.
     *
     * @return int
     */
    public function getParamId()
    {
        return $this->paramId;
    }

    /**
     * Set webrId.
     *
     * @param int $webrId
     *
     * @return WebrParams
     */
    public function setWebrId($webrId)
    {
        $this->webrId = $webrId;

        return $this;
    }

    /**
     * Get webrId.
     *
     * @return int
     */
    public function getWebrId()
    {
        return $this->webrId;
    }

    /**
     * Set linkId.
     *
     * @param int $linkId
     *
     * @return WebrParams
     */
    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;

        return $this;
    }

    /**
     * Get linkId.
     *
     * @return int
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return WebrParams
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param bool $value
     *
     * @return WebrParams
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }
}
