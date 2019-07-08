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
	 * @var string
	 */
	private $text;
	/**
	 * CreateQuestionCommand constructor.
	 *
	 * @param string $title
	 * @param string $description
	 * @param string $text
	 * @param int    $creator_id
	 */
	public function __construct(string $title, string $description, string $text, int $creator_id)
	{
		$this->title = $title;
		$this->description = $description;
		$this->issuing_user_id = $creator_id;
		$this->text = $text;
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

	public function getCreator()
	{
		return $this->issuing_user_id;
	}

	public function getText()
	{
		return $this->text;
	}
}
