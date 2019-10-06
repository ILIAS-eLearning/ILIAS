<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\AnswerSpecificFeedback;

use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFeedbackFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ilRadioGroupInputGUI;
use ilRadioOption;
use stdClass;

/**
 * Class AnswerSpecificPageObjectFeedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionFeedbackPage extends AbstractAnswerOptionFeedback
{

    /**
     * @var int
     */
    private $question_int_id;
	/**
     * @var int
     */
	private $answer_option_id;


    /**
     * PageObjectAnswerSpecificFeedback constructor.
     *
     * @param int $il_page_object_int_id
     */
    public function __construct(int $question_int_id, int $answer_option_id) {
        $this->question_int_id = $question_int_id;
        $this->answer_option_id = $answer_option_id;
    }


    /**
     * @return ilFormPropertyGUI|null
     */
    public function getFields(): ilFormPropertyGUI
    {
        global $DIC;



        $page_factory = new PageFactory(PageFactory::ASQ_PAGE_TYPE_ANSWER_OPTION_FEEDBACK,$this->question_int_id, $this->answer_option_id);

        $answer_specific_feedback = new \ilNonEditableValueGUI('test',$this->answer_option_id, true);
        $answer_specific_feedback->setValue($page_factory->getAnswerOptionFeedbackPage()->getPageEditingLink());


        return $answer_specific_feedback;

/*
        $feedback_setting = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_feedback_setting'), self::VAR_FEEDBACK_SETTING);
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_all'), self::OPT_FEEDBACK_SETTING_ALL));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_checked'), self::OPT_FEEDBACK_SETTING_CHECKED));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_correct'), self::OPT_FEEDBACK_SETTING_CORRECT));
        $feedback_setting->setRequired(true);
        $fields[] =  $feedback_setting;
*/
        //TODO - if page not exists create - link?

    }

    public static function deserialize(stdClass $data) {
        return new AnswerOptionFeedbackPage();
    }

    public function getValues(): array {
        return [];
    }
}