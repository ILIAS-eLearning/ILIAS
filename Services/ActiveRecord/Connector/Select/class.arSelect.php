<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arSelect
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arSelect extends arStatement
{

    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var string
     */
    protected $as = '';
    /**
     * @var string
     */
    protected $field_name = '';

    /**
     * @param ActiveRecord $ar
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar) : string
    {
        $return = '';
        if ($this->getTableName()) {
            $return .= $this->getTableName() . '.';
        }
        $return .= $this->getFieldName();
        if ($this->getAs() && $this->getFieldName() !== '*') {
            $return .= ' AS ' . $this->getAs();
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getTableName() : string
    {
        return $this->table_name;
    }

    /**
     * @param string $table_name
     */
    public function setTableName(string $table_name) : void
    {
        $this->table_name = $table_name;
    }

    /**
     * @return string
     */
    public function getAs() : string
    {
        return $this->as;
    }

    /**
     * @param string $as
     */
    public function setAs(string $as) : void
    {
        $this->as = $as;
    }

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
}
