<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arHaving
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arHaving extends arStatement
{

    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var string
     */
    protected $fieldname = '';
    /**
     * @var
     */
    protected $value;
    /**
     * @var string
     */
    protected $operator = '=';
    /**
     * @var string
     */
    protected $statement = '';
    /**
     * @var string
     */
    protected $glue = 'AND';


    /**
     * @description Build WHERE Statement
     *
     * @param ActiveRecord $ar
     *
     * @throws arException
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar)
    {
        $statement = '';
        if ($this->getTableName()) {
            $statement .= $this->getTableName() . '.';
        }
        $statement .= $this->getFieldname() . ' ' . $this->getOperator() . ' "' . $this->getValue() . '"';
        $this->setStatement($statement);

        return $this->getStatement();
    }


    /**
     * @return string
     */
    public function getFieldname()
    {
        return $this->fieldname;
    }


    /**
     * @param string $fieldname
     */
    public function setFieldname($fieldname)
    {
        $this->fieldname = $fieldname;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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


    /**
     * @return string
     */
    public function getStatement()
    {
        return $this->statement;
    }


    /**
     * @param string $statement
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;
    }


    /**
     * @return string
     */
    public function getGlue()
    {
        return $this->glue;
    }


    /**
     * @param string $glue
     */
    public function setGlue($glue)
    {
        $this->glue = $glue;
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
}
