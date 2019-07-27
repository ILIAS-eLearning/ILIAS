<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
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
	 * @var QuestionData
	 */
	private $data;

	/**
	 * CreateQuestionCommand constructor.
	 *
	 * @param QuestionData $data
	 * @param int          $creator_id
	 */
	public function __construct(QuestionData $data, int $creator_id)
	{
		parent::__construct($creator_id);
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	public function getCreator()
	{
		return $this->issuing_user_id;
	}
}
