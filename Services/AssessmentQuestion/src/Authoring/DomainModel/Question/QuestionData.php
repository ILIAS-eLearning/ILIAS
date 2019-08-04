<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

class QuestionData extends AbstractValueObject {
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
	private $question_text;
	/**
	 * @var string
	 */
	private $author;


	/**
	 * QuestionData constructor.
	 *
	 * @param string $title
	 * @param string $description
	 * @param string $text
	 * @param string $author
	 */
	public function __construct(string $title, string $text, string $author, string $description = null) {
		$this->title = $title;
		$this->description = $description;
		$this->question_text = $text;
		$this->author = $author;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getQuestionText(): string {
		return $this->question_text;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string {
		return $this->author;
	}
	/**
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::equals()
	 */
    public function equals(AbstractValueObject $other): bool
    {
        return $this->getAuthor() === $other->getAuthor() &&
               $this->getDescription() === $other->getDescription() &&
               $this->getQuestionText() === $other->getQuestionText() &&
               $this->getTitle() === $other->getTitle();
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::jsonSerialize()
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}