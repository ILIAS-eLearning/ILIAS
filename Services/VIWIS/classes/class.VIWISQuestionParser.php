<?php
require_once 'Services/VIWIS/interfaces/interface.QuestionParser.php';
require_once 'Services/VIWIS/classes/class.QuestionException.php';
require_once 'Services/VIWIS/classes/class.QuestionTypes.php';

/**
 *	@inheritdoc
 */
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
			throw new QuestionException("Could not parse the XML. Reason: no id.");
		}
		$title = (string)$attributes['title'];
		if($title) {
			$this->title = $title;
		} else {
			throw new QuestionException("Could not parse the XML. Reason: no title.");
		}
		$correct_answers = array();
		foreach($xml->responseDeclaration->correctResponse->children() as $response_id) {
			$correct_answers[] = (string)$response_id;
		}
		if(count($correct_answers) > 0) {
			$this->correct_answers = $correct_answers;
		} else {
			throw new QuestionException("Could not parse the XML. Reason: no correct answers.");
		}

		$type = (string)$xml->responseDeclaration['cardinality'];
		if(QuestionTypes::validType($type)) {
			$this->type = $type;
		} else {
			throw new QuestionException("Could not parse the XML. Reason: no valid type. Type: ".$type);
		}

		$question = (string)$xml->itemBody->choiceInteraction->prompt;
		if($question) {
			$this->question = $question;
		} else {
			throw new QuestionException("Could not parse the XML. Reason: no questions.");
		}

		$answers = array();

		foreach($xml->itemBody->choiceInteraction->children() as $child) {
			if($child->getName() !== 'simpleChoice') {
				continue;
			}
			$answers[(string)$child['identifier']] = (string)$child;
		}
		if(count($answers) > 0) {
			$this->answers = $answers;
		} else {
			throw new QuestionException("Could not parse the XML. Reason: no answers.");
		}
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
		return $this->question;
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