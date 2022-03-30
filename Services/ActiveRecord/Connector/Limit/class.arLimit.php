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
 * Class arLimit
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arLimit extends arStatement
{
    protected int $start = 0;
    protected int $end = 0;

    public function asSQLStatement(ActiveRecord $ar) : string
    {
        return ' LIMIT ' . $this->getStart() . ', ' . $this->getEnd();
    }

    public function setEnd(int $end) : void
    {
        $this->end = $end;
    }

    public function getEnd() : int
    {
        return $this->end;
    }

    public function setStart(int $start) : void
    {
        $this->start = $start;
    }

    public function getStart() : int
    {
        return $this->start;
    }
}
