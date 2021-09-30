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
 * Skill Category
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillCategory extends ilSkillTreeNode
{
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("scat");
    }

    public function copy() : ilSkillCategory
    {
        $scat = new ilSkillCategory();
        $scat->setTitle($this->getTitle());
        $scat->setDescription($this->getDescription());
        $scat->setType($this->getType());
        $scat->setSelfEvaluation($this->getSelfEvaluation());
        $scat->setOrderNr($this->getOrderNr());
        $scat->create();

        return $scat;
    }
}
