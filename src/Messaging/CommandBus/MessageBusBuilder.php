<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\CommandBus;

interface MessageBusBuilder {

	/**
	 * @param $command_handlers_by_command_name
	 * @param $service_locator_aware_callable_resolver
	 *
	 * $command_handlers_by_command_name: Provide optional a map
	 * of command names to callables.
	 * $commandHandlersByCommandName = [
	 * 'Fully\Qualified\Class\Name\Of\Command' => ... // a "callable"
	 * ];
	 *
	 * $service_locator_aware_callable_resolver
	 *
	 * If you don't provide a map the ComandBus looks for the CommandHandler by provided
	 * ComandName CommandName[WithSuffixHandler]
	 *
	 *
	 *
	 * @return mixed
	 */
	public function withCommandHandlerMap($command_handlers_by_command_name,$service_locator_aware_callable_resolver): MessageBusBuilder;


}