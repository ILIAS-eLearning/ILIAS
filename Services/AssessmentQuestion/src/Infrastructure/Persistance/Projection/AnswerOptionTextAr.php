<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;

class AnswerOptionTextAr extends AbstractAnswerOptionAr
{

    const STORAGE_NAME = "asq_answer_option_txt";
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected $text;


    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }

    public function setData(int $container_obj_id,string $question_id, string $revision_id, string $text)
    {
        $this->container_obj_id = $container_obj_id;
        $this->question_id = $question_id;
        $this->revision_id = $revision_id;
        $this->text = $text;
    }

    function satisfy(string $storage_type) : bool
    {
        return (AnswerOptionFormFieldDefinition::TYPE_TEXT == $storage_type) ? true : false;
    }
}