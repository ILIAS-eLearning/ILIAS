<?php

/* This file is part of ILIAS, a powerful learning management system
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
********************************************************************/

/**
 * Get info on usages of skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
interface ilSkillUsageInfo
{
    /**
     * Get title of an assigned item
     *
     * @param array $a_cskill_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @param array $a_usages
     */
    public static function getUsageInfo(array $a_cskill_ids, array &$a_usages);
}
