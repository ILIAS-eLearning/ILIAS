<?php

namespace ILIAS\App\CoreApp\Member\Infrastructure\CommandHandler;

use ILIAS\App\CoreApp\Member\Domain\Repository\MemberWriteonlyRepository;
use ILIAS\App\CoreApp\Member\Domain\Command\AddCourseMemberToCourseCommand;

class AddCourseMemberToCourseCommandHandler
{
	/**
	 * @var  MemberWriteonlyRepository
	 */
	private $course_repository;

	public function __construct(MemberWriteonlyRepository $member_writeonly_repository)
	{
		$this->member_writeonly_repository = $member_writeonly_repository;
	}

	/**
	 * @param AddCourseMemberToCourseCommand $add_course_member_to_course_command
	 *
	 */
	public function __invoke(AddCourseMemberToCourseCommand $add_member_command)
	{
		$this->member_writeonly_repository->addParticipant($add_member_command->getObjId(),$add_member_command->getUserId());
	}
}