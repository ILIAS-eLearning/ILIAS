<?php



/**
 * TableProperties
 */
class TableProperties
{
    /**
     * @var string
     */
    private $tableId = '';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $property = '';

    /**
     * @var string
     */
    private $value = ' ';


    /**
     * Set tableId.
     *
     * @param string $tableId
     *
     * @return TableProperties
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;

        return $this;
    }

    /**
     * Get tableId.
     *
     * @return string
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return TableProperties
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set property.
     *
     * @param string $property
     *
     * @return TableProperties
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property.
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return TableProperties
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
