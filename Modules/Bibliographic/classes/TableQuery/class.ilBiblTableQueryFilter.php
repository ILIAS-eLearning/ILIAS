<?php

/**
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTableQueryFilter implements ilBiblTableQueryFilterInterface
{
    protected string $field_name = '';
    /**
     * @var string|array|null
     */
    protected $field_value = null;
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

    
    public function getFieldValue()
    {
        return $this->field_value;
    }
    
    public function setFieldValue($field_value) : void
    {
        assert(is_array($field_value) || is_string($field_value));
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
