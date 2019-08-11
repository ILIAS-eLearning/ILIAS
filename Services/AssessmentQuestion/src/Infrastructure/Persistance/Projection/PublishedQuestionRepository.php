<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;

class PublishedQuestionRepository {

    /**
     * @var array
     */
    protected $answer_option_storages;

    public function construct() {

        $this->answer_option_storages[] = new AnswerOptionImageAr();
        $this->answer_option_storages[] = new AnswerOptionTextAr();

    }


    /**
     * @param string $container_obj_id
     * @param string $question_id
     * @param string $revision_id
     * @param string $title
     * @param string $description
     * @param string $question
     * @param AnswerOption[]  $answer_option
     */
    public function saveNewQuestionRevision(
        string $container_obj_id,
        string $question_id,
        string $revision_id,
        string $title,
        string $description,
        string $question,
        array $answer_options
    ) {
        $this->unpublishCurrentRevision($question_id, $container_obj_id);

        $item = new QuestionListItemAr();
        $item->setContainerObjId($container_obj_id);
        $item->setQuestionId($question_id);
        $item->setRevisionId($revision_id);
		$item->setTitle($title);
		$item->setDescription($description);
		$item->setQuestion($question);

		//if($new) {
        $item->create();
		/*} else {
			$item->save();
		}*/

        foreach ($answer_options as $answer_option) {
            $answer_option->create();
		}
    }

    public function getQuestionByRevisionId(string $revision_id): array {

        $dto = new QuestionDto();
        $question_list_item = QuestionListItemAr::where(['revision_id' => $revision_id])->getArray();
        $dto->setId($question_list_item['question_id']);
        $dto->setRevisionId($revision_id);

        //TODO
        $question_data = QuestionData::create(
            $question_list_item['title'],
            $question_list_item['question'],
            '',
            $question_list_item['description'],
            0
        );
        $dto->setData($question_data);

        //TODO
       // $play_configuration = QuestionPlayConfiguration::create();

        //TODO
        $arr_answer_options = new AnswerOptions();
        foreach($this->answer_option_storages as $storage) {
            $answer_options_ar = $storage->where([
                    'revision_id' => $revision_id
                ]
            )->get();
            if (count($answer_options_ar)) {
                foreach ($answer_options_ar as $answer_option_ar) {
                    /**
                     * @var QuestionListItemAr $item
                     */
                    //$arr_answer_options->addOption();
                }
            }
        }


    }

    public function getQuestionsByContainer($container_obj_id):array {

        //TODO we could return here the whole QuestionsDTO as array see getQuestionByRevisionId

        return QuestionListItemAr::where(['container_obj_id' => $container_obj_id, 'is_current_container_revision' => 1])->getArray();
    }

    public function unpublishCurrentRevision(string $question_id, int $container_obj_id) {



        foreach($this->storages as $storage) {
            /**
             * @var ProjectionAr $storage
             */
            $items = $storage->where([
                    'question_id'                   => $question_id,
                    'is_current_container_revision' => 1,
                    'container_obj_id'              => $container_obj_id
                ]
            )->get();
            if (count($items)) {
                foreach ($items as $item) {
                    /**
                     * @var QuestionListItemAr $item
                     */
                    $item->updateIsCurrentContainerRevisionToNo();
                }
            }
        }
    }
}