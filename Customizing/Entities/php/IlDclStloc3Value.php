<?php



/**
 * IlDclStloc3Value
 */
class IlDclStloc3Value
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
     * @var \DateTime
     */
    private $value = '1970-01-01 00:00:00';


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
     * @return IlDclStloc3Value
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
     * @param \DateTime $value
     *
     * @return IlDclStloc3Value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return \DateTime
     */
    public function getValue()
    {
        return $this->value;
    }
}
