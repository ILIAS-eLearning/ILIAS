<?php



/**
 * IlDclSelOpts
 */
class IlDclSelOpts
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
    private $optId = '0';

    /**
     * @var int
     */
    private $sorting = '0';

    /**
     * @var string
     */
    private $value = '';


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
     * @return IlDclSelOpts
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
     * Set optId.
     *
     * @param int $optId
     *
     * @return IlDclSelOpts
     */
    public function setOptId($optId)
    {
        $this->optId = $optId;

        return $this;
    }

    /**
     * Get optId.
     *
     * @return int
     */
    public function getOptId()
    {
        return $this->optId;
    }

    /**
     * Set sorting.
     *
     * @param int $sorting
     *
     * @return IlDclSelOpts
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting.
     *
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return IlDclSelOpts
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
