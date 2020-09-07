<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @param array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     */
    public static function getUsageInfo($a_cskill_ids, &$a_usages);
}
