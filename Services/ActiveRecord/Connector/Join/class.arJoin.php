<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arJoin
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arJoin extends arStatement
{
    const TYPE_NORMAL = self::TYPE_INNER;
    const TYPE_LEFT = 'LEFT';
    const TYPE_RIGHT = 'RIGHT';
    const TYPE_INNER = 'INNER';
    /**
     * @var string
     */
    protected $type = self::TYPE_NORMAL;
    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var array
     */
    protected $fields = array( '*' );
    /**
     * @var string
     */
    protected $operator = '=';
    /**
     * @var string
     */
    protected $on_first_field = '';
    /**
     * @var string
     */
    protected $on_second_field = '';
    /**
     * @var bool
     */
    protected $full_names = false;
    /**
     * @var bool
     */
    protected $both_external = false;
    /**
     * @var bool
     */
    protected $is_mapped = false;


    /**
     * @param \ActiveRecord $ar
     * @param string $as
     * @return string
     */
    protected function asStatementText(ActiveRecord $ar, $as = ' AS ')
    {
        $return = ' ' . $this->getType() . ' ';
        $return .= ' JOIN ' . $this->getTableName() . $as . $this->getTableNameAs();
        if ($this->getBothExternal()) {
            $return .= ' ON ' . $this->getOnFirstField() . ' ' . $this->getOperator() . ' ';
        } else {
            $return .= ' ON ' . $ar->getConnectorContainerName() . '.' . $this->getOnFirstField() . ' ' . $this->getOperator() . ' ';
        }
        $return .= $this->getTableNameAs() . '.' . $this->getOnSecondField();

        return $return;
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar)
    {
        return $this->asStatementText($ar, ' AS ');
    }


    public function setLeft()
    {
        $this->setType(self::TYPE_LEFT);
    }


    public function setRght()
    {
        $this->setType(self::TYPE_RIGHT);
    }


    public function setInner()
    {
        $this->setType(self::TYPE_INNER);
    }


    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }


    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }


    /**
     * @param string $on_first_field
     */
    public function setOnFirstField($on_first_field)
    {
        $this->on_first_field = $on_first_field;
    }


    /**
     * @return string
     */
    public function getOnFirstField()
    {
        return $this->on_first_field;
    }


    /**
     * @param string $on_second_field
     */
    public function setOnSecondField($on_second_field)
    {
        $this->on_second_field = $on_second_field;
    }


    /**
     * @return string
     */
    public function getOnSecondField()
    {
        return $this->on_second_field;
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
    public function getOperator()
    {
        return $this->operator;
    }


    /**
     * @param string $table_name
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }


    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }


    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param boolean $both_external
     */
    public function setBothExternal($both_external)
    {
        $this->both_external = $both_external;
    }


    /**
     * @return boolean
     */
    public function getBothExternal()
    {
        return $this->both_external;
    }


    /**
     * @param boolean $full_names
     */
    public function setFullNames($full_names)
    {
        $this->full_names = $full_names;
    }


    /**
     * @return boolean
     */
    public function getFullNames()
    {
        return $this->full_names;
    }


    /**
     * @return boolean
     */
    public function isIsMapped()
    {
        return $this->is_mapped;
    }


    /**
     * @param boolean $is_mapped
     */
    public function setIsMapped($is_mapped)
    {
        $this->is_mapped = $is_mapped;
    }
}
