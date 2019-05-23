<?php

namespace ILIAS\App\CoreApp\Course\Domain\Service;
use ILIAS\App\Domain\Service\WriteonlyService;
use ILIAS\App\CoreApp\Course\Domain\Command\AddMemberCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class CourseWriteonlyService implements WriteonlyService
{
	/** @var MessageBusInterface  */
	private $messageBus;

	public function __construct(
		MessageBusInterface $messageBus
	) {
		$this->messageBus = $messageBus;
	}

	public function addMember(int $obj_id,int $user_id)
	{
		$this->messageBus->dispatch(
			new AddMemberCommand($obj_id,$user_id)
		);
	}
}