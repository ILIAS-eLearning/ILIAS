<?php
namespace ILIAS\Messaging\Example\ExampleAsq\Domainmodel\Command;

class CreateQuestionCommand
{

	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;

	public function __construct(string $title,string $description)
	{
		$this->title = $title;
		$this->description = $description;
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
