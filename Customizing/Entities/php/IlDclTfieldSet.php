<?php



/**
 * IlDclTfieldSet
 */
class IlDclTfieldSet
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $tableId = '0';

    /**
     * @var string
     */
    private $field = '';

    /**
     * @var int|null
     */
    private $fieldOrder;

    /**
     * @var bool|null
     */
    private $exportable;


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
     * Set tableId.
     *
     * @param int $tableId
     *
     * @return IlDclTfieldSet
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;

        return $this;
    }

    /**
     * Get tableId.
     *
     * @return int
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return IlDclTfieldSet
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set fieldOrder.
     *
     * @param int|null $fieldOrder
     *
     * @return IlDclTfieldSet
     */
    public function setFieldOrder($fieldOrder = null)
    {
        $this->fieldOrder = $fieldOrder;

        return $this;
    }

    /**
     * Get fieldOrder.
     *
     * @return int|null
     */
    public function getFieldOrder()
    {
        return $this->fieldOrder;
    }

    /**
     * Set exportable.
     *
     * @param bool|null $exportable
     *
     * @return IlDclTfieldSet
     */
    public function setExportable($exportable = null)
    {
        $this->exportable = $exportable;

        return $this;
    }

    /**
     * Get exportable.
     *
     * @return bool|null
     */
    public function getExportable()
    {
        return $this->exportable;
    }
}
