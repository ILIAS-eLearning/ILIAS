<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ActiveRecord;
use arConnector;
use ilDateTime;

/**
 * Class QuestionListItemAr
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionListItemAr extends AbstractProjectionAr
{

    const STORAGE_NAME = "asq_question_list_item";

    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }


    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_is_notnull true
     */
    protected $title;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     400
     */
    protected $description;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     */
    protected $question;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     */
    protected $author;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     */
    protected $working_time;

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getQuestion() : string
    {
        return $this->question;
    }


    /**
     * @param string $question
     */
    public function setQuestion(string $question) : void
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }
    
    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }
 
    /**
     * @return number
     */
    public function getWorkingTime()
    {
        return $this->working_time;
    }
    
    /**
     * @param number $working_time
     */
    public function setWorkingTime($working_time)
    {
        $this->working_time = $working_time;
    }
    
    public function create()
    {
        parent::create();
    }
}