<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class OrderingScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OrderingScoringConfiguration extends AbstractConfiguration {
    /**
     * @var int
     */
    protected $points;
    
    
    static function create(int $points) : OrderingScoringConfiguration
    {
        $object = new NumericScoringConfiguration();
        $object->points = $points;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var OrderingScoringConfiguration $other */
        return get_class($this) === get_class($other) &&
        $this->points === $other->points;
    }
}