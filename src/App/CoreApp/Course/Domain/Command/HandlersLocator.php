<?php

namespace ILIAS\App\CoreApp\Course\Domain\Command;

use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Envelope;
use ILIAS\App\CoreApp\Course\Infrastructure\CommandHandler\AddMemberCommandHandler;

class HandlersLocator implements HandlersLocatorInterface {

	private $handlers;


	/**
	 * @param CourseRepository
	 */
	public function __construct($crs_repository) {

		/*$this->handlers = [
			AddMemberCommand::class => [ 'add_member_to_course' => new AddMemberCommandHandler($crs_repository) ]
		];*/

		//TODO -> Member
	}


	/**
	 * {@inheritdoc}
	 */
	public function getHandlers(Envelope $envelope): iterable {
		$seen = [];

		foreach (self::listTypes($envelope) as $type) {
			foreach ($this->handlers[$type] ?? [] as $alias => $handler) {
				if (!\in_array($handler, $seen, true)) {
					yield $alias => $seen[] = $handler;
				}
			}
		}
	}


	/**
	 * @internal
	 */
	public static function listTypes(Envelope $envelope): array {
		$class = \get_class($envelope->getMessage());

		return [ $class => $class ] + class_parents($class) + class_implements($class) + [ '*' => '*' ];
	}
}
