<?php



/**
 * UsrDataMulti
 */
class UsrDataMulti
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $fieldId = '';

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
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return UsrDataMulti
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
     * @param string $fieldId
     *
     * @return UsrDataMulti
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Get fieldId.
     *
     * @return string
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
     * @return UsrDataMulti
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
