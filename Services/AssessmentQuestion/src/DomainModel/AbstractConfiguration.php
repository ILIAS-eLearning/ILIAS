<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

/**
 * Class AbstractConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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
	
	/**
	 * @return array
	 */
	public function getOptionFormConfig() : array {
	    return [];
	}
}