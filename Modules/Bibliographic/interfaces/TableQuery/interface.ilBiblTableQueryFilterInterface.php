<?php
/**
 * Created by PhpStorm.
 * User: fschmid
 * Date: 20.11.17
 * Time: 16:20
 */

/**
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTableQueryFilterInterface
{

    /**
     * @return string
     */
    public function getFieldName();


    /**
     * @param string $field_name
     */
    public function setFieldName($field_name);


    /**
     * @return string
     */
    public function getFieldValue();


    /**
     * @param string $field_value
     */
    public function setFieldValue($field_value);


    /**
     * @return string
     */
    public function getOperator();


    /**
     * @param string $operator
     */
    public function setOperator($operator);
}
