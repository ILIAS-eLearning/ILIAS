<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingUserAnswer;

/**
 * Class ServiceFactory
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Authoring
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ProcessingService
{
    /**
     * @var int
     */
    protected $processing_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var int
     */
    protected $attempt_number;
    /**
     * @var QuestionConfig
     */
    protected $question_config;
    /**
     * @var string
     */
    protected $lng_key;

    /**
     * ProcessingService constructor.
     *
     * @param int            $processing_obj_id
     * @param int            $actor_user_id
     * @param int            $attempt_number
     * @param QuestionConfig $question_config
     */
    public function __construct(int $processing_obj_id, int $actor_user_id, int $attempt_number)
    {
        global $DIC;

        $this->processing_obj_id = $processing_obj_id;
        $this->actor_user_id = $actor_user_id;
        $this->attempt_number = $attempt_number;

        //The lng_key could be used in future as parameter in the constructor
        $this->lng_key = $DIC->language()->getDefaultLanguage();
    }


    /**
     * @param string         $question_revision_id
     *
     * @return ProcessingQuestion
     */
    public function question(string $question_revision_id) : ProcessingQuestion
    {
        return new ProcessingQuestion($question_revision_id, $this->processing_obj_id, $this->actor_user_id, $this->attempt_number, $this->lng_key);
    }


    /**
     * @param string $question_revision_key
     *
     * @return ProcessingUserAnswer
     */
    public function userAnswer(string $question_revision_key) : ProcessingUserAnswer
    {
        return new ProcessingUserAnswer($this->processing_obj_id, $this->actor_user_id, $this->attempt_number, $question_revision_key, $this->lng_key);
    }

    /**
     * @return ProcessingQuestionList
     */
    public function questionList() : ProcessingQuestionList
    {
        return new ProcessingQuestionList($this->processing_obj_id, $this->actor_user_id,  $this->attempt_number, $this->lng_key);
    }
}
