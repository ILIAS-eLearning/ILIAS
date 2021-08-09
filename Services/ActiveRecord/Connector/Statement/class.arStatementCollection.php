<?php

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
    protected static array $cache = array();
    /**
     * @var arStatement[]
     */
    protected $statements = array();
    protected ?\ActiveRecord $ar = null;

    public function add(arStatement $statement) : void
    {
        $this->statements[] = $statement;
    }

    public function hasStatements() : bool
    {
        return count($this->statements) > 0;
    }

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

    abstract public function asSQLStatement() : string;

    public function setAr(ActiveRecord $ar) : void
    {
        $this->ar = $ar;
    }

    public function getAr() : ?\ActiveRecord
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
