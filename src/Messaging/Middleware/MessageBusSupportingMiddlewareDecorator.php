<?php

namespace ILIAS\Messaging\Middleware;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

abstract class MessageBusSupportingMiddlewareDecorator {

	/**
	 * @var MessageBusSupportingMiddleware
	 */
	protected $message_bus_supporting_middleware;

	/**
	 * @var MessageBusMiddleware[]
	 */
	private $middlewares = [];



	public function __construct(MessageBusSupportingMiddleware $message_bus_supporting_middleware) {
		$this->message_bus_supporting_middleware = $message_bus_supporting_middleware;

		$this->middlewares = $this->message_bus_supporting_middleware->getMiddlewares();
	}

	public function resetMiddlewares() {
		$this->middlewares = [];
	}


	public function appendMiddleware(MessageBusMiddleware $middleware) {
		$this->middlewares[] = $middleware;
	}

	public function prependMiddleware(MessageBusMiddleware $middleware)
	{
		array_unshift($this->middlewares, $middleware);
	}

	public function getMiddlewares() {
		return $this->middlewares;
	}

	public function handle($message) {
		$this->message_bus_supporting_middleware->handle($message);
	}

	public function callableForNextMiddleware($index) {
		$reflection_class = new \ReflectionClass($this->message_bus_supporting_middleware);

		$reflection_method = $reflection_class->getMethod("callableForNextMiddleware");

		$reflection_method->setAccessible(true);

		return $reflection_method->invoke($this->message_bus_supporting_middleware, $index);
	}
}
