<?php



/**
 * StyleParameter
 */
class StyleParameter
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $styleId = '0';

    /**
     * @var string|null
     */
    private $tag;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $parameter;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var int
     */
    private $mqId = '0';

    /**
     * @var bool
     */
    private $custom = '0';


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
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleParameter
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
     * Set tag.
     *
     * @param string|null $tag
     *
     * @return StyleParameter
     */
    public function setTag($tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag.
     *
     * @return string|null
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set class.
     *
     * @param string|null $class
     *
     * @return StyleParameter
     */
    public function setClass($class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class.
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set parameter.
     *
     * @param string|null $parameter
     *
     * @return StyleParameter
     */
    public function setParameter($parameter = null)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get parameter.
     *
     * @return string|null
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return StyleParameter
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return StyleParameter
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set mqId.
     *
     * @param int $mqId
     *
     * @return StyleParameter
     */
    public function setMqId($mqId)
    {
        $this->mqId = $mqId;

        return $this;
    }

    /**
     * Get mqId.
     *
     * @return int
     */
    public function getMqId()
    {
        return $this->mqId;
    }

    /**
     * Set custom.
     *
     * @param bool $custom
     *
     * @return StyleParameter
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * Get custom.
     *
     * @return bool
     */
    public function getCustom()
    {
        return $this->custom;
    }
}
