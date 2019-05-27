<?php

namespace srag\IliasComponent\Context\Command\Command;

use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Handler\Resolver\NameBasedMessageHandlerResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use srag\IliasComponent\Context\Command\Command\Resolver;
use srag\IliasComponent\Context\Command\WriteOnlyDomainRepository;

/**
 * Class AbstractResolver
 *
 * @package srag\IliasComponent\Command
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractResolver implements Resolver {

	/**
	 * @var NameBasedMessageHandlerResolver
	 */
	protected $resolver;


	/**
	 * @inheritdoc
	 */
	public function __construct(WriteOnlyDomainRepository $repository) {
		$this->resolver = new NameBasedMessageHandlerResolver(new ClassBasedNameResolver(), new CallableMap($this->getCommandHandlerMap(), new ServiceLocatorAwareCallableResolver(function (string $command_handler_class) use ($repository): callable {
			return new $command_handler_class($repository);
		})));
	}


	/**
	 * @inheritdoc
	 */
	public function resolve($maybeCallable): callable {
		return $this->resolver->resolve($maybeCallable);
	}


	/**
	 * @return array
	 */
	protected abstract function getCommandHandlerMap(): array;
}
