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
 * @package     Modules/Test(QuestionPool)
 */
class ilAssQuestionAssignedSkillList implements Iterator
{
    /**
     * @var array
     */
    protected $skills = array();

    /**
     * @param integer $skillBaseId
     * @param integer $skillTrefId
     */
    public function addSkill($skillBaseId, $skillTrefId): void
    {
        $this->skills[] = "{$skillBaseId}:{$skillTrefId}";
    }

    /**
     * @return bool
     */
    public function skillsExist(): bool
    {
        return (bool) count($this->skills);
    }

    /**
     * @return array
     */
    public function current(): array
    {
        return current($this->skills);
    }

    /**
     * @return array
     */
    public function next(): array
    {
        return next($this->skills);
    }

    /**
     * @return integer|bool
     */
    public function key()
    {
        $res = key($this->skills);
        return $res;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        $res = key($this->skills);
        return $res !== null;
    }

    /**
     * @return array|bool
     */
    public function rewind()
    {
        return reset($this->skills);
    }

    /**
     * @return array
     */
    public function sleep(): array
    {
        return array('skills');
    }

    public function wakeup(): void
    {
        // TODO: Implement __wakeup() method.
    }
}
