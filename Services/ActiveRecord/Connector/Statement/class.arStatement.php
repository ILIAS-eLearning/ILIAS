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
 * Class arStatement
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
abstract class arStatement
{
    protected string $table_name_as = '';

    abstract public function asSQLStatement(ActiveRecord $ar) : string;

    public function getTableNameAs() : string
    {
        return $this->table_name_as;
    }

    public function setTableNameAs(string $table_name_as) : void
    {
        $this->table_name_as = $table_name_as;
    }
}
