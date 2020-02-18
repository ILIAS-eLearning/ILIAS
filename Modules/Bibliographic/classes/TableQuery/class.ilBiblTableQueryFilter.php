<?php

/**
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTableQueryFilter implements ilBiblTableQueryFilterInterface
{

    /**
     * @var string
     */
    protected $field_name = '';
    /**
     * @var string
     */
    protected $field_value = '';
    /**
     * @var string
     */
    protected $operator = '=';


    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->field_name;
    }


    /**
     * @param string $field_name
     */
    public function setFieldName($field_name)
    {
        $this->field_name = $field_name;
    }


    /**
     * @return string
     */
    public function getFieldValue()
    {
        return $this->field_value;
    }


    /**
     * @param string $field_value
     */
    public function setFieldValue($field_value)
    {
        $this->field_value = $field_value;
    }


    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }


    /**
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }
}
