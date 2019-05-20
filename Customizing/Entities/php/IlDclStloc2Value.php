<?php



/**
 * IlDclStloc2Value
 */
class IlDclStloc2Value
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $recordFieldId = '0';

    /**
     * @var int|null
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
     * Set recordFieldId.
     *
     * @param int $recordFieldId
     *
     * @return IlDclStloc2Value
     */
    public function setRecordFieldId($recordFieldId)
    {
        $this->recordFieldId = $recordFieldId;

        return $this;
    }

    /**
     * Get recordFieldId.
     *
     * @return int
     */
    public function getRecordFieldId()
    {
        return $this->recordFieldId;
    }

    /**
     * Set value.
     *
     * @param int|null $value
     *
     * @return IlDclStloc2Value
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return int|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
