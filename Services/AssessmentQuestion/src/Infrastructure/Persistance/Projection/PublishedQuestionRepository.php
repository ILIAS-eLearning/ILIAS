<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ActiveRecord;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ChoiceEditorDisplayDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringDefinition;

class PublishedQuestionRepository
{
    /**
     * @param string $container_obj_id
     * @param string $question_id
     * @param string $revision_id
     * @param QuestionData $data
     * @param AbstractProjectionAr $question_data
     * @param array $answer_options
     */
    public function saveNewQuestionRevision(
        string $container_obj_id,
        string $question_id,
        string $revision_id,
        QuestionData $data,
        AbstractProjectionAr $question_data,
        array $answer_options
    ) {
        $this->unpublishCurrentRevision($question_id, $container_obj_id);

        $item = new QuestionListItemAr();
        $item->setContainerObjId($container_obj_id);
        $item->setQuestionId($question_id);
        $item->setRevisionId($revision_id);
        $item->setTitle($data->getTitle());
        $item->setDescription($data->getDescription());
        $item->setQuestion($data->getQuestionText());
        $item->setAuthor($data->getAuthor());
        $item->setWorkingTime($data->getWorkingTime());

        $item->create();

        $question_data->create();
        
        foreach ($answer_options as $answer_option) {
            $answer_option->create();
        }
    }


    public function getQuestionByRevisionId(string $revision_id) : QuestionDto
    {

        $dto = new QuestionDto();
        /** @var QuestionListItemAr $question_list_item */
        $question_list_item = QuestionListItemAr::where(['revision_id' => $revision_id])->first();
        $dto->setId($question_list_item->getQuestionId());
        $dto->setRevisionId($revision_id);

        //TODO
        $question_data = QuestionData::create(
            $question_list_item->getTitle(),
            $question_list_item->getQuestion(),
            $question_list_item->getAuthor(),
            $question_list_item->getDescription(),
            $question_list_item->getWorkingTime()
        );
        $dto->setData($question_data);

        /** @var MultipleChoiceQuestionAr $play_config */
        $play_config = MultipleChoiceQuestionAr::where(['revision_id' => $revision_id])->first();
        $dto->setPlayConfiguration(QuestionPlayConfiguration::create(MultipleChoiceEditorConfiguration::create(
            $play_config->isShuffleAnswers(),
            $play_config->getMaxAnswers(),
            $play_config->getThumbnailSize(),
            $play_config->isSingleLine())));

        $answer_options = new AnswerOptions();
        $answer_option_ars = AnswerOptionChoiceAr::where(['revision_id' => $revision_id])->get();
        
        $index = 1;
        foreach ($answer_option_ars as $answer_option_ar) {
            $answer_options->addOption(new AnswerOption(
                $index,
                new ChoiceEditorDisplayDefinition(
                    $answer_option_ar->getText(), 
                    $answer_option_ar->getImageUuid()),
                new MultipleChoiceScoringDefinition(
                    $answer_option_ar->getPointsSelected(),
                    $answer_option_ar->getPointsUnselected())));
            $index += 1;
        }
        $dto->setAnswerOptions($answer_options);
        
        return $dto;
    }


    public function getQuestionsByContainer($container_obj_id) : array
    {

        //TODO we could return here the whole QuestionsDTO as array see getQuestionByRevisionId

        return QuestionListItemAr::where(['container_obj_id' => $container_obj_id, 'is_current_container_revision' => 1])->getArray();
    }


    public function unpublishCurrentRevision(string $question_id, int $container_obj_id)
    {
        /** @var QuestionListItemAr $question */
        $question = QuestionListItemAr::where(['question_id' => $question_id])->first();
        
        if (!is_null($question)) {
            foreach(MultipleChoiceQuestionAr::where(['revision_id' => $question->getRevisionId()])->get() as $mc_ar) {
                $mc_ar->delete();
            }
            
            foreach(AnswerOptionChoiceAr::where(['revision_id' => $question->getRevisionId()])->get() as $answer_option) {
                $answer_option->delete();
            }
        }
        
        $question->delete();
    }
}