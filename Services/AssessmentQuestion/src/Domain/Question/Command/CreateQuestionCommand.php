<?php

namespace ILIAS\AssessmentQuestion\Domain\Question\Command;

use ILIAS\Messaging\Contract\Command\AbstractCommand;
use ILIAS\Messaging\Contract\Command\Command;

class CreateQuestionCommand extends AbstractCommand implements Command
{

	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;

	public function __construct(string $title, string $description, int $creator_id)
	{
		$this->title = $title;
		$this->description = $description;
		$this->creator_id = $creator_id;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}
	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}
