<?php

namespace ILIAS\Messaging;

use ILIAS\Messaging\Adapter\CommandBusAdapter;
use ILIAS\Messaging\Contract\Command\CommandBus;
use ILIAS\Messaging\FinishHandlingMessageBefordeHandlingNext;

class CommandBusBuilder {

	private static $current_bus;
	/*
	 * @return CommandBusAdapter|CommandBus
	 */
	public static function getCommandBus() {
		if (self::$current_bus === null) {
			$current_bus = new CommandBusAdapter();

			//here could middlewares be added

			self::$current_bus = $current_bus;
		}

		return self::$current_bus;
	}
}