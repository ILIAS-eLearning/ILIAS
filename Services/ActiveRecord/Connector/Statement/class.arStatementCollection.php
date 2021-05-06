<?php
require_once('class.arStatement.php');

/**
 * Class arStatementCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
abstract class arStatementCollection
{

    /**
     * @var arStatementCollection[]
     */
    protected static $cache = array();
    /**
     * @var arStatement[]
     */
    protected $statements = array();
    /**
     * @var ActiveRecord
     */
    protected $ar;

    /**
     * @param arStatement $statement
     */
    public function add(arStatement $statement) : void
    {
        $this->statements[] = $statement;
    }

    /**
     * @return bool
     */
    public function hasStatements() : bool
    {
        return count($this->statements) > 0;
    }

    /**
     * @param ActiveRecord $ar
     * @return arStatementCollection
     */
    public static function getInstance(ActiveRecord $ar) : arStatementCollection
    {
        /**
         * @var $classname arStatementCollection
         */
        $classname = static::class;
        $arWhereCollection = new $classname();
        $arWhereCollection->setAr($ar);

        return $arWhereCollection;
    }

    /**
     * @return string
     */
    abstract public function asSQLStatement() : string;

    /**
     * @param \ActiveRecord $ar
     */
    public function setAr(ActiveRecord $ar) : void
    {
        $this->ar = $ar;
    }

    /**
     * @return \ActiveRecord
     */
    public function getAr() : ActiveRecord
    {
        return $this->ar;
    }

    /**
     * @param \arStatement[] $statements
     */
    public function setStatements(array $statements) : void
    {
        $this->statements = $statements;
    }

    /**
     * @return \arStatement[]
     */
    public function getStatements() : array
    {
        return $this->statements;
    }
}
