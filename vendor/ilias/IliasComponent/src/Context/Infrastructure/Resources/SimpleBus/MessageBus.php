<?php

namespace srag\IliasComponent\Context\Infrastructure\Resources\MessageBus;

use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\Message\Handler\DelegatesToMessageHandlerMiddleware;
use srag\IliasComponent\Context\Command\Command\Resolver;
use srag\IliasComponent\Context\Infrastructure\Resources\MessageBus\MessageBus\MessageBus as MessageBusInterface;

/**
 * Class MessageBus
 *
 * @package srag\IliasComponent\Context\Infrastructure\Resources\MessageBus
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class MessageBus implements MessageBusInterface {

	/**
	 * @var MessageBusSupportingMiddleware
	 */
	protected $message_bus;


	/**
	 * MessageBus constructor
	 *
	 * @param Resolver               $resolver
	 * @param MessageBusMiddleware[] $middlewares
	 */
	public function __construct(Resolver $resolver, array $middlewares = []) {
		$middlewares[] = new DelegatesToMessageHandlerMiddleware($resolver);
		$middlewares[] = new FinishesHandlingMessageBeforeHandlingNext();

		$this->message_bus = new MessageBusSupportingMiddleware($middlewares);
	}


	/**
	 * @inheritdoc
	 */
	public function handle($message): void {
		$this->message_bus->handle($message);
	}
}
