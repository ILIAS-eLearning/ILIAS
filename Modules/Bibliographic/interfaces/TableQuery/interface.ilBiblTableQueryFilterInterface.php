<?php
/**
 * Created by PhpStorm.
 * User: fschmid
 * Date: 20.11.17
 * Time: 16:20
 */

/**
 * Class ilBiblTableQueryInfo
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTableQueryFilterInterface
{
    public function getFieldName() : string;
    
    public function setFieldName(string $field_name) : void;
    
    /**
     * @return string|array
     */
    public function getFieldValue();
    
    /**
     * @param string|array $field_value
     */
    public function setFieldValue($field_value) : void;
    
    public function getOperator() : string;
    
    public function setOperator(string $operator) : void;
}
