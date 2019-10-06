<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\AnswerSpecificFeedback;

/**
 * Class Feedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AbstractAnswerOptionFeedback {

    const VAR_FEEDBACK_SETTING = "feedback_setting";
    const VAR_FEEDBACK_FOR_ANSWER = "feedback_for_answer";

    const OPT_FEEDBACK_SETTING_ALL = 1;
    const OPT_FEEDBACK_SETTING_CHECKED = 2;
    const OPT_FEEDBACK_SETTING_CORRECT = 3;



}