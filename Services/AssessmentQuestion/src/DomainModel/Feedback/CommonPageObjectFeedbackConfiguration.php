<?php

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;

/**
 * Class ErrorTextScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CommonPageObjectFeedbackConfiguration extends AbstractConfiguration {
    /**
     * @var Page
     */
    protected $page;
    
    static function create(Page $page) : CommonPageObjectFeedbackConfiguration
    {
        $object = new CommonPageObjectFeedbackConfiguration();
        $object->page = $page;
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
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var CommonPageObjectFeedbackConfiguration $other */
        return get_class($this) === get_class($other) &&
        $this->page->getId() === $other->page->getId();
    }
}