<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class ClozeScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ClozeScoringConfiguration extends AbstractConfiguration {
    public function equals(AbstractValueObject $other): bool
    {
        return get_class($this) === get_class($other);
    }
}