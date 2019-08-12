<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ActiveRecord;
use arConnector;
use ilDateTime;

/**
 * Class MultipleChoiceQuestionAr
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceQuestionAr extends AbstractProjectionAr
{
    const STORAGE_NAME = "asq_mc_question";
    
    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }
    
    /**
     * @var bool
     * 
     * @con_has_field        true
     * @con_fieldtype        integer
     * @con_length           1
     * @con_is_notnull       true
     */
    protected $shuffle_answers;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $max_answers;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $thumbnail_size;
    /**
     * @var bool
     *
     * @con_has_field        true
     * @con_fieldtype        integer
     * @con_length           1
     * @con_is_notnull       true
     */
    protected $single_line;
    
    /**
     * @return boolean
     */
    public function isShuffleAnswers()
    {
        return $this->shuffle_answers;
    }

    /**
     * @return number
     */
    public function getMaxAnswers()
    {
        return $this->max_answers;
    }

    /**
     * @return number
     */
    public function getThumbnailSize()
    {
        return $this->thumbnail_size;
    }

    /**
     * @return boolean
     */
    public function isSingleLine()
    {
        return $this->single_line;
    }

    public function setData(
        int $container_obj_id,
        string $question_id,
        string $revision_id,
        bool $shuffle_answers ,
        int $max_answers,
        int $thumbnail_size,
        bool $single_line)
    {
        $this->container_obj_id = $container_obj_id;
        $this->question_id = $question_id;
        $this->revision_id = $revision_id;
        $this->shuffle_answers = $shuffle_answers;
        $this->max_answers = $max_answers;
        $this->thumbnail_size = $thumbnail_size;
        $this->single_line = $single_line;
    }
   
    
    public function create()
    {
        parent::create();
    }
}