<?php

namespace ILIAS\App\CoreApp\Member\Domain\Command;

use ILIAS\App\CoreApp\Member\Domain\Repository\MemberWriteonlyRepository;
use ILIAS\App\CoreApp\Member\Domain\Service\MemberWriteonlyService;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Envelope;
use ILIAS\App\CoreApp\Member\Infrastructure\CommandHandler\AddCourseMemberToCourseCommandHandler;

class HandlersLocator implements HandlersLocatorInterface {

	private $handlers;


	/**
	 * @param MemberWriteonlyRepository
	 */
	public function __construct($member_repository) {

		$this->handlers = [
			AddCourseMemberToCourseCommand::class => [ 'add_member_to_course' => new AddCourseMemberToCourseCommandHandler($member_repository) ]
		];
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
