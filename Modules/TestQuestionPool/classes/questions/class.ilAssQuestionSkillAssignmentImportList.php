<?php

declare(strict_types=1);

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
 * @implements Iterator<ilAssQuestionSkillAssignmentImport>
 */
class ilAssQuestionSkillAssignmentImportList implements Iterator
{
    /** @var list<ilAssQuestionSkillAssignmentImport>  */
    protected array $assignments;

    public function __construct()
    {
        $this->assignments = [];
    }

    public function addAssignment(ilAssQuestionSkillAssignmentImport $assignment): void
    {
        $this->assignments[] = $assignment;
    }

    public function assignmentsExist(): bool
    {
        return count($this->assignments) > 0;
    }

    public function current(): ilAssQuestionSkillAssignmentImport
    {
        return current($this->assignments);
    }

    public function next(): void
    {
        next($this->assignments);
    }

    public function key(): int
    {
        return key($this->assignments);
    }

    public function valid(): bool
    {
        $res = key($this->assignments);
        return $res !== null;
    }

    public function rewind(): void
    {
        reset($this->assignments);
    }
}
