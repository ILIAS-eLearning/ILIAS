<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class KprimChoiceScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class KprimChoiceScoringConfiguration extends AbstractConfiguration {
    /**
     * @var int
     */
    protected $points;
    /**
     * @var int
     */
    protected $half_points_at;
    
    /**
     * @param int $points
     * @param int $half_points_at
     * @return KprimChoiceScoringConfiguration
     */
    static function create(int $points, int $half_points_at) : KprimChoiceScoringConfiguration
        {
            $object = new KprimChoiceScoringConfiguration();
            $object->points = $points;
            $object->half_points_at = $half_points_at;
            return $object;
    }
    
    /**
     * @return number
     */
    public function getPoints()
    {
        return $this->points;
    }
    
    /**
     * @return number
     */
    public function getHalfPointsAt()
    {
        return $this->half_points_at;
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var KprimChoiceScoringConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->half_points_at === $other->half_points_at &&
               $this->points === $other->points;
    }
}