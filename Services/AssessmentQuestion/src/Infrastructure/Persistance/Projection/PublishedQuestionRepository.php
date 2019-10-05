<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;

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
                //same revision already published
                return;
            }
            
            $old_question->delete();
        }

        $old_question_list = QuestionListItemAr::where(['question_id' => $question->getAggregateId()->getId()])->first();
        
        if (!is_null($old_question_list)) {
            $old_question_list->delete();
        }
        
        $question_ar = QuestionAr::createNew($question);
        $question_ar->create();
        
        $question_list = QuestionListItemAr::createNew($question);
        $question_list->create();
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
        return $dto;
    }



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