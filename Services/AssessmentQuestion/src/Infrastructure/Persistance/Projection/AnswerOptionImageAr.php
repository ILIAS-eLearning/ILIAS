<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;

class AnswerOptionImageAr extends AbstractAnswerOptionAr {

    const STORAGE_NAME = "asq_answer_option_img";

    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     */
    protected $image_uuid;

    public function setData(
        int $container_obj_id,
        string $question_id,
        string $revision_id, string $value)
    {
        $this->container_obj_id = $container_obj_id;
        $this->question_id = $question_id;
        $this->revision_id = $revision_id;
        $this->image_uuid = $value;
    }


    /**
     * @return string
     */
    static function returnDbTableName() {
        return self::STORAGE_NAME;
    }


    public function satisfy(string $storage_type) : bool
    {
        return (AnswerOptionFormFieldDefinition::TYPE_IMAGE == $storage_type) ? true : false;
    }

}