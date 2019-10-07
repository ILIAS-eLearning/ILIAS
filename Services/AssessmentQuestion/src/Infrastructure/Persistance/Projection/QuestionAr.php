<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ILIAS\AssessmentQuestion\DomainModel\Question;
use ilDateTime;

/**
 * Class QuestionAr
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionAr extends AbstractProjectionAr
{
    const STORAGE_NAME = "asq_question";
    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $id;
    /**
     * @con_has_field true
     * @con_fieldtype timestamp
     */
    protected $created;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $question_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $revision_id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_index      true
     * @con_is_notnull true
     */
    protected $container_obj_id;
    /**
     * @con_has_field        true
     * @con_fieldtype        integer
     * @con_length           8
     * @con_is_notnull       true
     * @con_is_unique        true
     *
     * @var int
     */
    protected $question_int_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     * @con_is_notnull true
     */
    protected $question_data;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     * @con_is_notnull true
     */
    protected $question_configuration;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected $answer_options;  
    
    /**
     * 
     * @param Question $question
     */
    public static function createNew(Question $question) {
        $object = new QuestionAr();
        
        $created = new ilDateTime(time(), IL_CAL_UNIX);
        $object->created = $created->get(IL_CAL_DATETIME);
        $object->question_int_id = $question->getQuestionIntId();
        $object->revision_id = $question->getRevisionId()->GetKey();
        $object->question_id = $question->getAggregateId()->getId();
        $object->question_data = json_encode($question->getData());
        $object->question_configuration = json_encode($question->getPlayConfiguration());
        $object->answer_options = json_encode($question->getAnswerOptions()->getOptions());
        $object->container_obj_id = $question->getContainerObjId();
        
        return $object;
        
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getQuestionId() : string
    {
        return $this->question_id;
    }

    /**
     * @return string
     */
    public function getRevisionId() : string
    {
        return $this->revision_id;
    }

    /**
     * @return int
     */
    public function getContainerObjId() : int
    {
        return $this->container_obj_id;
    }

    /**
     * @return int
     */
    public function getQuestionIntId(): int
    {
        return $this->question_int_id;
    }

    /**
     * @return string
     */
    public function getQuestionData()
    {
        return $this->question_data;
    }
    
    /**
     * @return string
     */
    public function getQuestionConfiguration()
    {
        return $this->question_configuration;
    }
    
    /**
     * @return string
     */
    public function getAnswerOptions()
    {
        return $this->answer_options;
    }
    
    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }
}