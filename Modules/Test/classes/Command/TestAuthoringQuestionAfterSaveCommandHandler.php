<?php
declare(strict_types=1);

namespace ILIAS\Modules\Test\Command;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringQuestion;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringQuestionAfterSaveCommand;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringQuestionAfterSaveCommandHandler;
use ilObjTest;
use ilTestQuestionSetConfigFactory;

/**
 * class TestAuthoringQuestionAfterSaveCommandHandler
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestAuthoringQuestionAfterSaveCommandHandler implements AuthoringQuestionAfterSaveCommandHandler
{
    /**
     * @param AuthoringQuestionAfterSaveCommand $command
     */
    public function handle(AuthoringQuestionAfterSaveCommand $command) {
        if($command->getQuestionDto()->isComplete()) {
            $question_service = $this->getQuestionService($command->getQuestionDto());

            $question_service->publishNewRevision();


            //get the question again with the new revision id
            $question_service = $this->getQuestionService($command->getQuestionDto());

            $questionSetConfig = ilTestQuestionSetConfigFactory::getInstance(new ilObjTest($command->getQuestionDto()->getContainerObjId(), false))->getQuestionSetConfig();
            $questionSetConfig->updateRevisionId($question_service->getQuestionDto()->getId(), $question_service->getQuestionDto()->getRevisionId());
        }
    }

    protected function getQuestionService(QuestionDto $question_dto): AuthoringQuestion {
        global $DIC;

        return $question_service = $DIC->assessment()->questionAuthoring(
            $question_dto->getContainerObjId(), $DIC->user()->getId()
            )->question( $DIC->assessment()->entityIdBuilder()->fromString($question_dto->getId()));
    }
}