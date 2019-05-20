<?php



/**
 * IlDclFieldPropB
 */
class IlDclFieldPropB
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
     * @var int
     */
    private $datatypePropId = '0';

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
     * @return IlDclFieldPropB
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
     * Set datatypePropId.
     *
     * @param int $datatypePropId
     *
     * @return IlDclFieldPropB
     */
    public function setDatatypePropId($datatypePropId)
    {
        $this->datatypePropId = $datatypePropId;

        return $this;
    }

    /**
     * Get datatypePropId.
     *
     * @return int
     */
    public function getDatatypePropId()
    {
        return $this->datatypePropId;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return IlDclFieldPropB
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
