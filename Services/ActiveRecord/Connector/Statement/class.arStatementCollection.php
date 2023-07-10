<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    public function add(arStatement $arStatement): void
    {
        $this->statements[] = $arStatement;
    }

    public function hasStatements(): bool
    {
        return $this->statements !== [];
    }

    public static function getInstance(ActiveRecord $activeRecord): arStatementCollection
    {
        /**
         * @var $classname arStatementCollection
         */
        $classname = static::class;
        $arWhereCollection = new $classname();
        $arWhereCollection->setAr($activeRecord);

        return $arWhereCollection;
    }

    abstract public function asSQLStatement(): string;

    public function setAr(ActiveRecord $activeRecord): void
    {
        $this->ar = $activeRecord;
    }

    public function getAr(): ?\ActiveRecord
    {
        return $this->ar;
    }

    /**
     * @param \arStatement[] $statements
     */
    public function setStatements(array $statements): void
    {
        $this->statements = $statements;
    }

    /**
     * @return \arStatement[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }
}
