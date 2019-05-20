<?php



/**
 * IlDclTviewSet
 */
class IlDclTviewSet
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $tableviewId = '0';

    /**
     * @var string
     */
    private $field = '';

    /**
     * @var bool|null
     */
    private $visible;

    /**
     * @var bool|null
     */
    private $inFilter;

    /**
     * @var string|null
     */
    private $filterValue;

    /**
     * @var bool|null
     */
    private $filterChangeable;


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
     * Set tableviewId.
     *
     * @param int $tableviewId
     *
     * @return IlDclTviewSet
     */
    public function setTableviewId($tableviewId)
    {
        $this->tableviewId = $tableviewId;

        return $this;
    }

    /**
     * Get tableviewId.
     *
     * @return int
     */
    public function getTableviewId()
    {
        return $this->tableviewId;
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return IlDclTviewSet
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
     * Set visible.
     *
     * @param bool|null $visible
     *
     * @return IlDclTviewSet
     */
    public function setVisible($visible = null)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool|null
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set inFilter.
     *
     * @param bool|null $inFilter
     *
     * @return IlDclTviewSet
     */
    public function setInFilter($inFilter = null)
    {
        $this->inFilter = $inFilter;

        return $this;
    }

    /**
     * Get inFilter.
     *
     * @return bool|null
     */
    public function getInFilter()
    {
        return $this->inFilter;
    }

    /**
     * Set filterValue.
     *
     * @param string|null $filterValue
     *
     * @return IlDclTviewSet
     */
    public function setFilterValue($filterValue = null)
    {
        $this->filterValue = $filterValue;

        return $this;
    }

    /**
     * Get filterValue.
     *
     * @return string|null
     */
    public function getFilterValue()
    {
        return $this->filterValue;
    }

    /**
     * Set filterChangeable.
     *
     * @param bool|null $filterChangeable
     *
     * @return IlDclTviewSet
     */
    public function setFilterChangeable($filterChangeable = null)
    {
        $this->filterChangeable = $filterChangeable;

        return $this;
    }

    /**
     * Get filterChangeable.
     *
     * @return bool|null
     */
    public function getFilterChangeable()
    {
        return $this->filterChangeable;
    }
}
