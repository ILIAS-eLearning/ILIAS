<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\AssessmentQuestion\Application\PlayApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

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
    protected $container_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var QuestionConfig
     */
    protected $question_config;



    /**
     * ProcessingService constructor.
     *
     * @param int            $container_obj_id
     * @param int            $actor_user_id
     * @param QuestionConfig $question_config
     */
    public function __construct(int $container_obj_id, int $actor_user_id, QuestionConfig $question_config)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;
        $this->question_config = $question_config;

        //$this->processing_application_service = new PlayApplicationService($container_obj_id, $actor_user_id, $question_config);
    }


    /**
     * @param string $question_revision_uuid
     *
     * @return Question
     */
    public function question(string $question_revision_id) : Question
    {
        return new Question($question_revision_id, $this->actor_user_id, $this->question_config);
    }

    /**
     * @return ProcessingQuestionList
     */
    public function questionList() : ProcessingQuestionList
    {
        return new ProcessingQuestionList($this->container_obj_id, $this->actor_user_id, $this->question_config);
    }
}
