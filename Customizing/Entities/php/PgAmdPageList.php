<?php



/**
 * PgAmdPageList
 */
class PgAmdPageList
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
     * @var string|null
     */
    private $data;


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return PgAmdPageList
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * @return PgAmdPageList
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
     * Set data.
     *
     * @param string|null $data
     *
     * @return PgAmdPageList
     */
    public function setData($data = null)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return string|null
     */
    public function getData()
    {
        return $this->data;
    }
}
