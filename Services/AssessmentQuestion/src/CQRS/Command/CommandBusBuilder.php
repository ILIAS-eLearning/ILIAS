<?php

namespace ILIAS\AssessmentQuestion\CQRS\Command;

/**
 * Class CommandBusBuilder
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CommandBusBuilder {

	private static $current_bus;
	/*
	 * @return CommandBusAdapter|CommandBusContract
	 */
	public static function getCommandBus() {
		if (self::$current_bus === null) {
			$current_bus = new CommandBus();

			//here could middlewares be added

			self::$current_bus = $current_bus;
		}

		return self::$current_bus;
	}
}