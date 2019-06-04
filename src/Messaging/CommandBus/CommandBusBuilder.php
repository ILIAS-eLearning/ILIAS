<?php

namespace ILIAS\Messaging\CommandBus;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use ILIAS\Messaging\Middleware\MessageBusSupportingMiddleware;

use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use SimpleBus\Message\Handler\Resolver\NameBasedMessageHandlerResolver;
use SimpleBus\Message\Handler\DelegatesToMessageHandlerMiddleware;
use SimpleBus\Message\Name\NamedMessageNameResolver;

class CommandBusBuilder implements MessageBusBuilder {

	private $command_bus;


	public function __construct() {
		$this->command_bus = new MessageBusSupportingMiddleware();

		$this->command_bus->appendMiddleware(new FinishesHandlingMessageBeforeHandlingNext());
	}

	public function resetMiddlewares(): void {
		$this->command_bus->resetMiddlewares();

		return $this->command_bus;
	}

	/**
	 * Appends new middleware for this message bus.
	 *
	 * @private
	 *
	 * @param MessageBusMiddleware $middleware
	 *
	 * @return void
	 */
	public function appendMiddleware(MessageBusMiddleware $middleware): void {
		$this->command_bus->appendMiddleware($middleware);
	}


	/**
	 * Prepends new middleware for this message bus. Should only be used at configuration time.
	 *
	 * @private
	 *
	 * @param MessageBusMiddleware $middleware
	 *
	 * @return void
	 */
	public function prependMiddleware(MessageBusMiddleware $middleware): void {
		$this->command_bus->prependMiddleware($middleware);
	}


	public function getMiddlewares() {
		return $this->command_bus->getMiddlewares();
	}


	public function handle($message) {
		$this->command_bus->handle($message);
	}


	private function callableForNextMiddleware($index) {
		return $this->command_bus->callableForNextMiddleware($index);
	}


	/**
	 * @param $command_handlers_by_command_name
	 * @param $service_locator_aware_callable_resolver
	 *
	 * @return MessageBusBuilder
	 */
	public function withCommandHandlerMap($command_handlers_by_command_name, $service_locator_aware_callable_resolver): MessageBusBuilder {

		$command_handler_map = new CallableMap($command_handlers_by_command_name, new ServiceLocatorAwareCallableResolver($service_locator_aware_callable_resolver));

		$command_name_resolver = new NamedMessageNameResolver();

		$command_handler_resolver = new NameBasedMessageHandlerResolver($command_name_resolver, $command_handler_map);

		$this->appendMiddleware(new DelegatesToMessageHandlerMiddleware($command_handler_resolver));

		return $this;
	}
}