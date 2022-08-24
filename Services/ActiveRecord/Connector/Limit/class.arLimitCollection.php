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
 * Class arLimitCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arLimitCollection extends arStatementCollection
{
    public function asSQLStatement(): string
    {
        if ($this->hasStatements()) {
            /**
             * @var $last arLimit
             */
            $statements = $this->getStatements();
            $last = end($statements);

            return $last->asSQLStatement($this->getAr());
        }
        return '';
    }
}
