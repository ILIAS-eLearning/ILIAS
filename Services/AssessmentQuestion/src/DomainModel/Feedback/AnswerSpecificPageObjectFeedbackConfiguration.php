<?php

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;

/**
 * Class AnswerSpecificPageObjectFeedbackConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerSpecificPageObjectFeedbackConfiguration extends AbstractConfiguration {
    /**
     * @var Page
     */
    protected $page;
    /**
     * @var AnswerOptions
     */
    protected $answer_options;
    
    static function create(Page $page, AnswerOptions $answer_options) : AnswerSpecificPageObjectFeedbackConfiguration
    {
        $object = new AnswerSpecificPageObjectFeedbackConfiguration();
        $object->page = $page;
        $object->answer_options = $answer_options;
        return $object;
    }
    
    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }


    /**
     * @return AnswerOptions
     */
    public function getAnswerOptions() : AnswerOptions
    {
        return $this->answer_options;
    }


    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var AnswerSpecificPageObjectFeedbackConfiguration $other */
        return get_class($this) === get_class($other) &&
        $this->page->getId() === $other->page->getId();
    }
}