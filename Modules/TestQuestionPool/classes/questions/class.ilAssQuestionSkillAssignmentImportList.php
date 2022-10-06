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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImportList implements Iterator
{
    /**
     * @var array[ilAssQuestionSkillAssignmentImport]
     */
    protected $assignments;

    /**
     * ilAssQuestionSkillAssignmentImportList constructor.
     */
    public function __construct()
    {
        $this->assignments = array();
    }

    /**
     * @param ilAssQuestionSkillAssignmentImport $assignment
     */
    public function addAssignment(ilAssQuestionSkillAssignmentImport $assignment): void
    {
        $this->assignments[] = $assignment;
    }

    public function assignmentsExist(): bool
    {
        return count($this->assignments) > 0;
    }

    /**
     * @return ilAssQuestionSkillAssignmentImport
     */
    public function current(): ilAssQuestionSkillAssignmentImport
    {
        return current($this->assignments);
    }

    /**
     * @return ilAssQuestionSkillAssignmentImport
     */
    public function next(): ilAssQuestionSkillAssignmentImport
    {
        return next($this->assignments);
    }

    /**
     * @return integer|bool
     */
    public function key()
    {
        $res = key($this->assignments);
        return $res;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        $res = key($this->assignments);
        return $res !== null;
    }

    /**
     * @return ilAssQuestionSkillAssignmentImport|bool
     */
    public function rewind()
    {
        return reset($this->assignments);
    }

    public function sleep(): void
    {
        // TODO: Implement __sleep() method.
    }

    public function wakeup(): void
    {
        // TODO: Implement __wakeup() method.
    }
}
