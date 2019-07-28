<?php

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command;

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