<?php

namespace ILIAS\DI;

use ILIAS\DI\Exceptions\InvalidClassException;
use ILIAS\DI\Exceptions\NoSuchServiceException;

/**
 * Class Factory
 * @package ILIAS\DI
 *
 * Create instances of classes using type hinting and the dependency injection container.
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class Injector {

	/**
	 * @var Container
	 */
	protected $dic;

	/**
	 * Factory constructor.
	 *
	 * @param $dic Container
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
	}

	/**
	 * @param $fullyQualifiedClassName string The given class must type hint all its constructor arguments. Furthermore the types must exist in the DI-Container.
	 * @param null $requireFile string
	 * @return object
	 * @throws InvalidClassException
	 * @throws NoSuchServiceException
	 */
	public function createInstance($fullyQualifiedClassName, $requireFile = null) {
		if($requireFile)
			/** @noinspection PhpIncludeInspection */
			require_once($requireFile);

		// The reflection classes needed.
		$reflectionClass = new \ReflectionClass($fullyQualifiedClassName);
		$constructor = $reflectionClass->getConstructor();
		if(!$constructor)
			return $reflectionClass->newInstance();

		$parameters = $constructor->getParameters();

		// we get the arguments to construct the object from the DIC and Typehinting.
		$constructorArguments = $this->createConstructorArguments($fullyQualifiedClassName, $parameters);

		// Crate the instance with the arguments.
		return $reflectionClass->newInstanceArgs($constructorArguments);
	}

	/**
	 * @param $fullyQualifiedClassName string
	 * @param $parameters ReflectionParameter[]
	 * @return array
	 * @throws InvalidClassException
	 * @throws NoSuchServiceException
	 */
	protected function createConstructorArguments($fullyQualifiedClassName, $parameters) {
		$constructorArguments = [];

		foreach ($parameters as $parameter) {
			$type = $parameter->getType()->__toString();

			if ($parameter->getType()->isBuiltin())
				throw new InvalidClassException("The DI cannot instantiate $fullyQualifiedClassName because some of the constructors arguments are built in types. Only interfaces (and objects) are stored in the DI-Container.");

			if (!$type)
				throw new InvalidClassException("The DI cannot instantiate $fullyQualifiedClassName because some of the constructors arguments are not type hinted. Make sure all parameters in the constructor have type hinting.");

			if (!isset($this->dic[$type]))
				throw new NoSuchServiceException("You wanted to instantiate a class of type $fullyQualifiedClassName which wants an injection of type $type. The DI-Container does not contain such a service. The services available are: " . implode(', ', $this->dic->keys()));

			$constructorArguments[] = $this->dic[$type];
		}
		return $constructorArguments;
	}


}