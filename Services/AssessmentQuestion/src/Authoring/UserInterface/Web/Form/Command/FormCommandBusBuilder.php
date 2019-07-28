<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;


class FormCommandBusBuilder {

	private static $current_bus;
	/*
	 * @return CommandBusAdapter|CommandBusContract
	 */
	public static function getFormCommandBus() {
		if (self::$current_bus === null) {
			$current_bus = new FormCommandBus();

			//here could middlewares be added

			self::$current_bus = $current_bus;
		}

		return self::$current_bus;
	}
}