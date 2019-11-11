<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

class PublishedQuestionRepository
{
    /**
    * @param Question $question
    */
    public function saveNewQuestionRevision(Question $question) {
        /** @var QuestionAr $old_question */
        $old_question = QuestionAr::where(['question_id' => $question->getAggregateId()->getId()])->first();


        if (!is_null($old_question)) {
            if ($old_question->getRevisionId() === $question->getRevisionId()) {
                //same question already published
                return;
            }
        }

        //TODO we should compare the content with creating revision ids and not at this place here!
            /*
            if ($old_question->getRevisionId() === $question->getRevisionId() ||
                $this->contentEquals($question, $this->GenerateDtoFromAr($old_question))) {
                //same question already published
                return;
            }

        }*/
        
        $question_ar = QuestionAr::createNew($question);
        $question_ar->create();
        
        $question_list = QuestionListItemAr::createNew($question);
        $question_list->create();
    }
    
    private function contentEquals(Question $current, QuestionDto $old) {
        return $current->getData()->equals($old->getData()) &&
               $current->getPlayConfiguration()->equals($old->getPlayConfiguration()) &&
               $current->getAnswerOptions()->equals($old->getAnswerOptions());
    }
    
    public function getQuestionByRevisionId(string $revision_id) : QuestionDto
    {
        /** @var QuestionAr $question */
        $question = QuestionAr::where(['revision_id' => $revision_id])->first();

        $dto = $this->GenerateDtoFromAr($question);
        
        return $dto;
    }
   
    /**
     * @param QuestionAr $question
     * @return \ILIAS\AssessmentQuestion\DomainModel\QuestionDto
     */
    private function GenerateDtoFromAr(QuestionAr $question)
    {
        $dto = new QuestionDto();
        $dto->setId($question->getQuestionId());
        $dto->setRevisionId($question->getRevisionId());
        $dto->setContainerObjId($question->getContainerObjId());
        $dto->setQuestionIntId($question->getQuestionIntId());
        $dto->setData(AbstractValueObject::deserialize($question->getQuestionData()));
        $dto->setPlayConfiguration(AbstractValueObject::deserialize($question->getQuestionConfiguration()));
        $dto->setAnswerOptions(Answeroptions::deserialize($question->getAnswerOptions()));
        $dto->setLegacyData(QuestionLegacyData::create(0, $question->getContainerObjId(), $question->getObjectId()));
        $dto->setFeedback(Feedback::deserialize($question->getFeedback()));
        return $dto;
    }


    /**
     * @param $container_obj_id
     *
     * @return QuestionDto[]
     */
    public function getQuestionsByContainer($container_obj_id) : array
    {
        $questions = [];
        
        foreach (QuestionAr::where(['container_obj_id' => $container_obj_id])->get() as $question) {
            $questions[] = $this->GenerateDtoFromAr($question);
        }

        return $questions;
    }


    public function unpublishCurrentRevision(string $question_id, int $container_obj_id)
    {
        /** @var QuestionListItemAr $question */
        $question_list = QuestionListItemAr::where(['question_id' => $question_id])->first();
        
        if (!is_null($question)) {
            $question_list->delete();
        }
        
        /** @var QuestionAr $question */
        $question = QuestionAr::where(['question_id' => $question_id])->first();
        
        if (!is_null($question)) {
            $question->delete();
        }
    }
}