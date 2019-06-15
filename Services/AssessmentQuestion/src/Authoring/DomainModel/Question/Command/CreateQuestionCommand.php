<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\Messaging\Contract\Command\AbstractCommand;
use ILIAS\Messaging\Contract\Command\Command;

/**
 * Class CreateQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
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


	/**
	 * CreateQuestionCommand constructor.
	 *
	 * @param string $title
	 * @param string $description
	 * @param int    $creator_id
	 */
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
