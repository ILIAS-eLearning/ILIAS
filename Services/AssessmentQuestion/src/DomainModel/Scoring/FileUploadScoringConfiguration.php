<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class FileUploadScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FileUploadScoringConfiguration extends AbstractConfiguration {
    /**
     * @var int
     */
    protected $points;
    
    /**
     * @var bool
     */
    protected $completed_by_submition;
    
    /**
     * @param int $points
     * @param bool $completed_by_submition
     * @return FileUploadScoringConfiguration
     */
    static function create(int $points, bool $completed_by_submition) : FileUploadScoringConfiguration
    {
        $object = new FileUploadScoringConfiguration();
        $object->points = $points;
        $object->completed_by_submition = $completed_by_submition;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getPoints() : int {
        return $this->points;
    }
    
    /**
     * @return boolean
     */
    public function isCompletedBySubmition() {
        return $this->completed_by_submition;
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var FileUploadScoringConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->points === $other->points &&
               $this->completed_by_submition === $other->completed_by_submition;
    }
}