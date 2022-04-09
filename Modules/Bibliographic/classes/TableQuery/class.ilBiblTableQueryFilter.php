<?php

/**
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTableQueryFilter implements ilBiblTableQueryFilterInterface
{
    protected string $field_name = '';
    protected string $field_value = '';
    protected string $operator = '=';


    /**
     * @return string
     */
    public function getFieldName() : string
    {
        return $this->field_name;
    }


    /**
     * @param string $field_name
     */
    public function setFieldName(string $field_name) : void
    {
        $this->field_name = $field_name;
    }


    /**
     * @return string
     */
    public function getFieldValue() : string
    {
        return $this->field_value;
    }


    /**
     * @param string $field_value
     */
    public function setFieldValue(string $field_value) : void
    {
        $this->field_value = $field_value;
    }


    /**
     * @return string
     */
    public function getOperator() : string
    {
        return $this->operator;
    }


    /**
     * @param string $operator
     */
    public function setOperator(string $operator) : void
    {
        $this->operator = $operator;
    }
}
