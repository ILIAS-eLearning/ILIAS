<?php

namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Exception\DomainException;
use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware;
use ILIAS\Messaging\Contract\Command\Command;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

/**
 * Class QuestionCommandBus
 *
 * @package ILIAS\AssessmentQuestion\Authoring\Infrastructure
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionCommandBus {

	/**
	 * @var self
	 */
	protected static $instance;
	/**
	 * @var QuestionCommandBus $command_bus;
	 */
	protected $command_bus;
	/**
	 * @var MessageBusMiddleware $middlewares;
	 */
	protected $middlewares;




	/**
	 * @param CommandHandlerMiddleware[] $arr_additional_middleware
	 *
	 * @return QuestionCommandBus
	 */
	public static function getInstance(array $arr_additional_middleware): void {
		if (is_null(self::$instance)) {
			self::$instance = new self($arr_additional_middleware);
		}

		return self::$instance->command_bus;
	}


	/**
	 * QuestionCommandBus constructor.
	 *
	 * @param array $arr_middle_wares
	 */
	protected function __construct(array $arr_additional_middleware) {
		$command_bus_builder = new CommandBusBuilder();

		$this->command_bus = $command_bus_builder->getCommandBus();
		$this->appendArMiddleware($arr_additional_middleware);
	}

	/**
	 * @param Command $command
	 */
	public function handle(Command $command): void {
		$command_type = get_class($command);

		if (!array_key_exists($command_type, $this->handlers)) {
			throw new DomainException(sprintf("No handler set for message of type %s", $command_type));
		}

		foreach ($this->middlewares as $middleware) {
			$command = $middleware->handle($command);
		}

		$this->handlers[$command_type]->handle($command);
	}





	/**
	 * @param array $arr_additional_middleware
	 */
	protected function appendArMiddleware(array $arr_additional_middleware) {
		if(count($arr_additional_middleware) > 0) {
			foreach ($arr_additional_middleware as $middleware) {
				$this->command_bus->appendMiddleware($middleware);
			}
		}
	}
}

