<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arJoin
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arJoin extends arStatement
{
    const TYPE_NORMAL = self::TYPE_INNER;
    const TYPE_LEFT = 'LEFT';
    const TYPE_RIGHT = 'RIGHT';
    const TYPE_INNER = 'INNER';
    const AS_TEXT = ' AS ';
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
    protected $fields = array('*');
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
     * @param string        $as
     * @return string
     */
    protected function asStatementText(ActiveRecord $ar, $as = ' AS ') : string
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
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar) : string
    {
        return $this->asStatementText($ar, self::AS_TEXT);
    }

    public function setLeft() : void
    {
        $this->setType(self::TYPE_LEFT);
    }

    public function setRght() : void
    {
        $this->setType(self::TYPE_RIGHT);
    }

    public function setInner() : void
    {
        $this->setType(self::TYPE_INNER);
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields) : void
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * @param string $on_first_field
     */
    public function setOnFirstField(string $on_first_field) : void
    {
        $this->on_first_field = $on_first_field;
    }

    /**
     * @return string
     */
    public function getOnFirstField() : string
    {
        return $this->on_first_field;
    }

    /**
     * @param string $on_second_field
     */
    public function setOnSecondField(string $on_second_field) : void
    {
        $this->on_second_field = $on_second_field;
    }

    /**
     * @return string
     */
    public function getOnSecondField() : string
    {
        return $this->on_second_field;
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
    public function getOperator() : string
    {
        return $this->operator;
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
    public function getTableName() : string
    {
        return $this->table_name;
    }

    /**
     * @param string $type
     */
    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param bool $both_external
     */
    public function setBothExternal(bool $both_external) : void
    {
        $this->both_external = $both_external;
    }

    /**
     * @return bool
     */
    public function getBothExternal() : bool
    {
        return $this->both_external;
    }

    /**
     * @param bool $full_names
     */
    public function setFullNames(bool $full_names) : void
    {
        $this->full_names = $full_names;
    }

    /**
     * @return bool
     */
    public function getFullNames() : bool
    {
        return $this->full_names;
    }

    /**
     * @return bool
     */
    public function isIsMapped() : bool
    {
        return $this->is_mapped;
    }

    /**
     * @param bool $is_mapped
     */
    public function setIsMapped(bool $is_mapped) : void
    {
        $this->is_mapped = $is_mapped;
    }
}
