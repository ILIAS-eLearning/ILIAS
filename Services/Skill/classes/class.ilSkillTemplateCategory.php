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
 * Skill Template Category
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTemplateCategory extends ilSkillTreeNode
{
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sctp");
    }

    public function copy() : ilSkillTemplateCategory
    {
        $sctp = new ilSkillTemplateCategory();
        $sctp->setTitle($this->getTitle());
        $sctp->setDescription($this->getDescription());
        $sctp->setType($this->getType());
        $sctp->setOrderNr($this->getOrderNr());
        $sctp->create();

        return $sctp;
    }
}
