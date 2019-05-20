<?php



/**
 * IlDclFieldProp
 */
class IlDclFieldProp
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $fieldId = '0';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $value;


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
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return IlDclFieldProp
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Get fieldId.
     *
     * @return int
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return IlDclFieldProp
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
     * @return IlDclFieldProp
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
