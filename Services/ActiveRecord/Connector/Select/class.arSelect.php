<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arSelect
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arSelect extends arStatement
{

    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var array
     */
    protected $as = '';
    /**
     * @var string
     */
    protected $field_name = '';


    /**
     * @param ActiveRecord $ar
     *
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar)
    {
        $return = '';
        if ($this->getTableName()) {
            $return .= $this->getTableName() . '.';
        }
        $return .= $this->getFieldName();
        if ($this->getAs() and $this->getFieldName() != '*') {
            $return .= ' AS ' . $this->getAs();
        }

        return $return;
    }


    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }


    /**
     * @param string $table_name
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }


    /**
     * @return array
     */
    public function getAs()
    {
        return $this->as;
    }


    /**
     * @param array $as
     */
    public function setAs($as)
    {
        $this->as = $as;
    }


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
}
