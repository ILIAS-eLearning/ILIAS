<?php

use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\Messaging\Example\ExampleAsq\Domainmodel\Command\CreateQuestionCommand;

class CreateQuestionService {

	public function __construct(CreateQuestionCommand $command, ilDBInterface $db)
	{

		$command_bus = new CommandBusBuilder();

		$command_bus->handle($command);

	}

}