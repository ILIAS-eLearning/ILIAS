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
 * Class arJoinCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arJoinCollection extends arStatementCollection
{
    protected array $table_names = [];

    /**
     * @param arJoin $arStatement
     */
    public function getSaveTableName(arStatement $arStatement): string
    {
        $tableName = $arStatement->getTableName();
        if (in_array($tableName, $this->table_names, true)) {
            $vals = array_count_values($this->table_names);
            $next = $vals[$tableName] + 1;
            $arStatement->setFullNames(true);
            $arStatement->setIsMapped(true);

            return $tableName . '_' . $next;
        }
        return $tableName;
    }

    public function add(arStatement $arStatement): void
    {
        $arStatement->setTableNameAs($this->getSaveTableName($arStatement));
        $this->table_names[] = $arStatement->getTableName();
        parent::add($arStatement);
    }

    public function asSQLStatement(): string
    {
        $return = '';
        if ($this->hasStatements()) {
            foreach ($this->getJoins() as $arJoin) {
                $return .= $arJoin->asSQLStatement($this->getAr());
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
