<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

class PublishedQuestionRepository {

    public function construct() {

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
        array $answer_option
    ) {
        $this->unpublishCurrentRevision($question_id, $container_obj_id);

        $item = new QuestionListItem();
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

        foreach ($answer_option as $answer_option) {
            $answer_option->create();
		}
    }

    public function getQuestionsByContainer($container_obj_id):array {
        return QuestionListItem::where(['container_obj_id' => $container_obj_id, 'is_current_container_revision' => 1])->getArray();
    }

    public function unpublishCurrentRevision(string $question_id, int $container_obj_id) {

        $storages[] = new QuestionListItem();
        $storages[] = new AnswerOptionImageAr();
        $storages[] = new AnswerOptionTextAr();

        foreach($storages as $storage) {
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
                     * @var QuestionListItem $item
                     */
                    $item->updateIsCurrentContainerRevisionToNo();
                }
            }
        }
    }
}