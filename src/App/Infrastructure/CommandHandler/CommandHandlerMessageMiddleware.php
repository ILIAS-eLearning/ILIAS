<?php

namespace ILIAS\App\Infrasctrutre\CommandHandler;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;


class CommandHandlerMessageMiddleware implements MiddlewareInterface
{
	private $handlersLocator;
	private $allowNoHandlers;

	public function __construct(HandlersLocatorInterface $handlersLocator, bool $allowNoHandlers = false)
	{
		$this->handlersLocator = $handlersLocator;
		$this->allowNoHandlers = $allowNoHandlers;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws NoHandlerForMessageException When no handler is found and $allowNoHandlers is false
	 */
	public function handle(Envelope $envelope, StackInterface $stack): Envelope
	{
		$handler = null;
		$message = $envelope->getMessage();
		foreach ($this->handlersLocator->getHandlers($envelope) as $alias => $handler) {
			$envelope = $envelope->with(HandledStamp::fromCallable($handler, $handler($message), \is_string($alias) ? $alias : null));
		}
		if (null === $handler && !$this->allowNoHandlers) {
			throw new NoHandlerForMessageException(sprintf('No handler for message "%s".', \get_class($envelope->getMessage())));
		}

		return $stack->next()->handle($envelope, $stack);
	}
}
