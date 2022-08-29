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
 * Class arWhereCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arHavingCollection extends arStatementCollection
{
    public function asSQLStatement(): string
    {
        $return = '';
        if ($this->hasStatements()) {
            $return .= ' HAVING ';
            $havings = $this->getHavings();
            $last = end($havings);
            foreach ($havings as $arWhere) {
                $return .= $arWhere->asSQLStatement($this->getAr());
                if ($arWhere !== $last) {
                    $return .= ' ' . $arWhere->getGlue() . ' ';
                }
            }
        }

        return $return;
    }

    /**
     * @return arHaving[]
     */
    public function getHavings(): array
    {
        return $this->statements;
    }
}
