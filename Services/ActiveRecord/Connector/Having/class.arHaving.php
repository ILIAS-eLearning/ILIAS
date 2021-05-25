<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arHaving
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
     * @param ActiveRecord $ar
     * @return string
     * @throws arException
     */
    public function asSQLStatement(ActiveRecord $ar) : string
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
    public function getFieldname() : string
    {
        return $this->fieldname;
    }

    /**
     * @param string $fieldname
     */
    public function setFieldname(string $fieldname) : void
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

    /**
     * @return string
     */
    public function getStatement() : string
    {
        return $this->statement;
    }

    /**
     * @param string $statement
     */
    public function setStatement(string $statement) : void
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
    public function setGlue(string $glue) : void
    {
        $this->glue = $glue;
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
}
