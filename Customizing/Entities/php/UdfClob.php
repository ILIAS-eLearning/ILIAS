<?php



/**
 * UdfClob
 */
class UdfClob
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $fieldId = '0';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return UdfClob
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return UdfClob
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
     * Set value.
     *
     * @param string|null $value
     *
     * @return UdfClob
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
