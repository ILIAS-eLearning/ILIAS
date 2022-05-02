<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected static array $cache = [];
    /**
     * @var arStatement[]
     */
    protected array $statements = [];
    protected ?\ActiveRecord $ar = null;

    public function add(arStatement $statement) : void
    {
        $this->statements[] = $statement;
    }

    public function hasStatements() : bool
    {
        return $this->statements !== [];
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
