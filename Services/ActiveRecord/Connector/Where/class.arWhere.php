<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arWhere
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arWhere extends arStatement
{
    const TYPE_STRING = 1;
    const TYPE_REGULAR = 2;
    /**
     * @var int
     */
    protected $type = self::TYPE_REGULAR;
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
    protected $link = 'AND';

    /**
     * @description Build WHERE Statement
     * @param ActiveRecord $ar
     * @return string
     * @throws arException
     */
    public function asSQLStatement(ActiveRecord $ar) : string
    {
        if ($this->getType() === self::TYPE_REGULAR) {
            $arField = $ar->getArFieldList()->getFieldByName($this->getFieldname());
            if ($arField instanceof arField) {
                $type = $arField->getFieldType();
                $statement = $ar->getConnectorContainerName() . '.' . $this->getFieldname();
            } else {
                $statement = $this->getFieldname();
            }

            if (is_array($this->getValue())) {
                if (in_array($this->getOperator(), array('IN', 'NOT IN', 'NOTIN'))) {
                    $statement .= ' ' . $this->getOperator() . ' (';
                } else {
                    $statement .= ' IN (';
                }
                $values = array();
                foreach ($this->getValue() as $value) {
                    $values[] = $ar->getArConnector()->quote($value, $type);
                }
                $statement .= implode(', ', $values);
                $statement .= ')';
            } else {
                if ($this->getValue() === null) {
                    $operator = 'IS';
                    if (in_array($this->getOperator(), array('IS', 'IS NOT'))) {
                        $operator = $this->getOperator();
                    }
                    $this->setOperator($operator);
                }
                $statement .= ' ' . $this->getOperator();
                $statement .= ' ' . $ar->getArConnector()->quote($this->getValue(), $type);
            }
            $this->setStatement($statement);
        }

        return $this->getStatement();
    }

    /**
     * @param string $fieldname
     */
    public function setFieldname(string $fieldname) : void
    {
        $this->fieldname = $fieldname;
    }

    /**
     * @return string
     */
    public function getFieldname() : string
    {
        return $this->fieldname;
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
     * @param mixed $value
     */
    public function setValue($value) : void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $type
     */
    public function setType(int $type) : void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
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
    public function getStatement() : string
    {
        return $this->statement;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link) : void
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getLink() : string
    {
        return $this->link;
    }
}
