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
 * Class arSelectCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arSelectCollection extends arStatementCollection
{
    public function asSQLStatement(): string
    {
        $return = 'SELECT ';
        if ($this->hasStatements()) {
            $activeRecord = $this->getAr();
            $selectSQLs = array_map(fn ($select) => $select->asSQLStatement($activeRecord), $this->getSelects());
            $return .= implode(', ', $selectSQLs);
        }

        return $return;
    }

    /**
     * @return arSelect[]
     */
    public function getSelects(): array
    {
        return $this->statements;
    }
}
