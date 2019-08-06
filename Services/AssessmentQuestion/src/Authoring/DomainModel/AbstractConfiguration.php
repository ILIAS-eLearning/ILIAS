<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionCreatedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
abstract class AbstractConfiguration extends AbstractValueObject {
	/**
	 * Returns the Classname this Configuration contains configuration data for
	 * Default assumes the configuration class is named ClassConfiguration, so it
	 * returns Class
	 *
	 * @return bool|string
	 */
	public function configurationFor() {
		$class = get_called_class();
		return substr($class, 0, strlen($class) - strlen("Configuration"));
	}
}