<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ActiveRecord;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ChoiceEditorDisplayDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringDefinition;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;

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
            
            $object_id = $old_question->getObjectId();
            $old_question->delete();
        }

        $old_question_list = QuestionListItemAr::where(['question_id' => $question->getAggregateId()->getId()])->first();
        
        if (!is_null($old_question_list)) {
            $old_question_list->delete();
        }
        
        if (is_null($object_id)) {
            if (!is_null($question->getLegacyData()) &&
                !is_null($question->getLegacyData()->getObjectId())) {
                    $object_id = $question->getLegacyData()->getObjectId();
                }
                else {
                    $object_id = $this->getNextObjectId();
                }
        }
        
        $question_ar = QuestionAr::createNew($question, $object_id);
        $question_ar->create();
        
        $question_list = QuestionListItemAr::createNew($question);
        $question_list->create();
    }

    /**
     * return current highest object_id + 1
     * is not auto increment due to import takeover of questions with existion object ids
     * 
     * @return number
     */
    private function getNextObjectId() {
        global $DIC;
        
        $sql = "SELECT max(object_id) FROM " . QuestionAr::STORAGE_NAME;
        $query = $DIC->database()->query($sql);
        $result = $DIC->database()->fetchAssoc($query);
        $int_result = intval($result['object_id']);
        return $int_result ? $int_result + 1 : 1;
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