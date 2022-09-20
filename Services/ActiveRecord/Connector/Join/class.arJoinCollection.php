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
 * Class arJoinCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arJoinCollection extends arStatementCollection
{
    protected array $table_names = array();

    /**
     * @param arJoin $statement
     */
    public function getSaveTableName(arStatement $statement): string
    {
        $table_name = $statement->getTableName();
        if (in_array($table_name, $this->table_names, true)) {
            $vals = array_count_values($this->table_names);
            $next = $vals[$table_name] + 1;
            $statement->setFullNames(true);
            $statement->setIsMapped(true);

            return $table_name . '_' . $next;
        }
        return $table_name;
    }

    public function add(arStatement $statement): void
    {
        $statement->setTableNameAs($this->getSaveTableName($statement));
        $this->table_names[] = $statement->getTableName();
        parent::add($statement);
    }

    public function asSQLStatement(): string
    {
        $return = '';
        if ($this->hasStatements()) {
            foreach ($this->getJoins() as $join) {
                $return .= $join->asSQLStatement($this->getAr());
            }
        }

        return $return;
    }

    /**
     * @return arJoin[]
     */
    public function getJoins(): array
    {
        return $this->statements;
    }
}
