<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\AssessmentQuestion\Application\PlayApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;

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
    protected $actor_user_id;
    /**
     * @var PlayApplicationService
     */
    protected $processing_application_service;


    /**
     * @param int $container_obj_id
     * @param int $actor_user_id
     */
    public function __construct(int $container_obj_id, int $actor_user_id)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;

        $this->processing_application_service = new PlayApplicationService($container_obj_id, $actor_user_id);
    }


    /**
     * @param string $question_revision_uuid
     * @param int    $actor_user_id
     * @param string $userAnswerUuid
     *
     * @return Question
     */
    public function question(AssessmentEntityId $question_revision_id, AssessmentEntityId $user_answer_id) : Question
    {
        return new Question($question_revision_id, $this->actor_user_id, $user_answer_id);
    }

    /**
     * @return ProcessingQuestionList
     */
    public function questionList() : ProcessingQuestionList
    {
        return new ProcessingQuestionList($this->container_obj_id, $this->actor_user_id);
    }
}
