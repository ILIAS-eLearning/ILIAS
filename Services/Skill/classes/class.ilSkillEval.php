<?php


/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
include_once("./Services/Skill/classes/class.ilSkillProfile.php");

/**
 * Personal skills GUI class
 *
 * @author Alex Killing <killing@gmx.de>
 *
 * @ingroup ServicesSkill
 */
class ilSkillEval
{
    const TYPE_APPRAISAL = 1;
    const TYPE_MEASUREMENT = 2;
    const TYPE_SELF_EVAL = 3;
}
