<?php

use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\AssessmentQuestion\Domainmodel\Command\CreateQuestionCommand;

class CreateQuestionService {

	/**
	 * @var ilDBInterface
	 */
	private  $db;

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function execute($command) {
		$command_bus = new CommandBusBuilder();
		$command_bus->handle($command);
	}

}