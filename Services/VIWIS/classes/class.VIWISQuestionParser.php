<?php
require_once 'Services/VIWIS/interfaces/interface.QuestionParser.php';
require_once 'Services/VIWIS/classes/class.QuestionException.php';
require_once 'Services/VIWIS/classes/class.QuestionTypes.php';

class VIWISQuestionParser implements QuestionParser {

	private $title;
	private $id;
	private $question;
	private $type;
	private $answers = array();
	private $correct_answers = array();

	/**
	 *	@inheritdoc
	 */
	public function parseXML($xml_string) {
		$xml = new SimpleXMLElement($xml_string);
		$attributes = $xml->attributes();
		$id = (string)$attributes['identifier'];
		if($id) {
			$this->id = $id;
		} else {
			throw new QuestionException("");
		}
		$title = (string)$attributes['title'];
		if($title) {
			$this->title = $title;
		} else {
			throw new QuestionException("");
		}
		$correct_answers = array();
		foreach($xml->responseDeclaration->correctResponse->children() as $response_id) {
			$correct_answers[] = (string)$response_id;
		}
		if(count($correct_answers) > 0) {
			$this->correct_answers = $correct_answers;
		} else {
			throw new QuestionException("");
		}

		$type = (string)$xml->responseDeclaration['cardinality'];
		if(QuestionTypes::validType($type)) {
			$this->type = $type;
		} else {
			throw new QuestionException("");
		}
		$this->question = (string)$xml->itemBody->choiceInteraction->prompt;
		return $this;
	}

	/**
	 *	@inheritdoc
	 */
	public function title() {
		return $this->title;
	}

	/**
	 *	@inheritdoc
	 */
	public function id() {
		return $this->id;
	}

	/**
	 *	@inheritdoc
	 */
	public function question() {
		return $this->questions;
	}

	/**
	 *	@inheritdoc
	 */
	public function answers() {
		return $this->answers;
	}

	/**
	 *	@inheritdoc
	 */
	public function correctAnswerIds() {
		return $this->correct_answers;
	}

	/**
	 *	@inheritdoc
	 */
	public function type() {
		return $this->type;
	}
}