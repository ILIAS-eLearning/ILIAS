<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionList;
use ILIAS\UI\Component\Link\Link;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;

/**
 * Class AuthoringService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Authoring
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AuthoringService
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
     * AuthoringApplicationService
     */
    protected $authoring_application_service;


    /**
     * @param int $container_obj_id
     * @param int $actor_user_id
     */
    public function __construct(int $container_obj_id, int $actor_user_id)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;

        $this->authoring_application_service = new AuthoringApplicationService($container_obj_id, $actor_user_id);
    }


    /**
     * @param int                $container_obj_id
     * @param AssessmentEntityId $question_uuid
     * @param int                $actor_user_id
     * @param Link               $container_backlink
     *
     * @return Question
     */
    public function question(AssessmentEntityId $question_uuid, Link $container_backlink) : Question
    {
        return new Question($this->container_obj_id, $question_uuid, $this->actor_user_id, $container_backlink);
    }


    /**
     * @param AssessmentEntityId $question_uuid
     *
     * @return QuestionComponent
     */
    public function questionComponent(AssessmentEntityId $question_uuid):QuestionComponent {
       return new QuestionComponent($this->authoring_application_service->GetQuestion($question_uuid->getId()));
    }


    /**
     * @return QuestionList
     */
    public function questionList() : AuthoringQuestionList
    {
        return new AuthoringQuestionList($this->container_obj_id, $this->actor_user_id);
    }


    /**
     * @return QuestionImport
     */
    public function questionImport() : QuestionImport
    {
        return new QuestionImport();
    }


    /**
     * Returns the current question_uuid or a new one if no current exists
     *
     * @return AssessmentEntityId
     */
    public function currentOrNewQuestionId() : AssessmentEntityId
    {
        global $DIC;

        if ($DIC->http()->request()->getAttribute('question_uuid', false) !== false) {
            $DIC->assessment()->entityIdBuilder()->fromString($DIC->http()->request()->getAttribute('question_uuid', false));
        }

        return $DIC->assessment()->entityIdBuilder()->new();
    }
}
