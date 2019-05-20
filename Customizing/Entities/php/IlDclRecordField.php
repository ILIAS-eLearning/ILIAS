<?php



/**
 * IlDclRecordField
 */
class IlDclRecordField
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $recordId = '0';

    /**
     * @var int
     */
    private $fieldId = '0';


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
     * Set recordId.
     *
     * @param int $recordId
     *
     * @return IlDclRecordField
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get recordId.
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return IlDclRecordField
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
}
