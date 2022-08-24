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
 ********************************************************************
 */

/**
 * Class ilDclRecordQueryObject
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRecordQueryObject
{
    protected string $selectStatement = "";
    protected string $joinStatement = "";
    protected string $whereStatement = "";
    protected string $groupStatement = "";
    protected string $orderStatement = "";

    public function getSelectStatement(): string
    {
        return $this->selectStatement;
    }

    public function setSelectStatement(string $selectStatement): void
    {
        $this->selectStatement = " " . $selectStatement;
    }

    public function getJoinStatement(): string
    {
        return $this->joinStatement;
    }

    public function setJoinStatement(string $joinStatement): void
    {
        $this->joinStatement = " " . $joinStatement;
    }

    public function getWhereStatement(): string
    {
        return $this->whereStatement;
    }

    public function setWhereStatement(string $whereStatement): void
    {
        $this->whereStatement = " " . $whereStatement;
    }

    public function getGroupStatement(): string
    {
        return $this->groupStatement;
    }

    public function setGroupStatement(string $groupStatement): void
    {
        $this->groupStatement = " " . $groupStatement;
    }

    public function getOrderStatement(): string
    {
        return $this->orderStatement;
    }

    public function setOrderStatement(string $orderStatement): void
    {
        $this->orderStatement = " " . $orderStatement;
    }

    /**
     * Apply custom sorting
     */
    public function applyCustomSorting(
        ilDclBaseFieldModel $field,
        array $all_records,
        string $direction = 'asc'
    ): array {
        return $all_records;
    }
}
