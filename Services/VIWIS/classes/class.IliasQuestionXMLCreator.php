<?php
require_once 'Services/VIWIS/interfaces/interface.QuestionXMLCreator.php';
require_once 'Services/VIWIS/classes/class.QuestionException.php';
require_once 'Services/VIWIS/classes/class.QuestionTypes.php';

require_once 'Modules/TestQuestionPool/classes/class.assSingleChoice.php';
require_once 'Modules/TestQuestionPool/classes/class.assMultipleChoice.php';
require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssSingleChoiceFeedback.php';
require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultipleChoiceFeedback.php';

/**
 *	@inheritdoc
 */
class IliasQuestionXMLCreator implements QuestionXMLCreator {

	private $title;
	private $id;
	private $question;
	private $type;
	private $answers = array();
	private $correct_answers = array();

	/**
	 *	@inheritdoc
	 */
	public function XML() {
		if(count($this->answers) === 0) {
			throw new questionException('');
		}
		if(count($this->correct_answers) === 0) {
			throw new questionException('');
		}
		global $ilCtrl,$ilDB,$lng;
		switch($this->type) {
			case 'single':
				$obj = new assSingleChoice(		$this->title,
												'',
												'root user',
												-1,
												$this->question);
				$obj->feedbackOBJ = new ilAssSingleChoiceFeedback($obj,$ilCtrl,$ilDB,$lng);
				break;
			case 'multiple':
				$obj = new assMultipleChoice(	$this->title,
												'',
												'root user',
												-1,
												$this->question);
				$obj->feedbackOBJ = new ilAssMultipleChoiceFeedback($obj,$ilCtrl,$ilDB,$lng);
				break;
			default:
				throw new questionException('unknown question type $this->type');
		}
		$obj->setId($this->id);
		foreach ($this->answers as $answer_id => $answer) {
			$points = in_array($answer_id, $this->correct_answers) ? 1 : 0;
			$obj->addAnswer($answer,$points);
		}
		return $obj->toXML();
	}

	/**
	 *	@inheritdoc
	 */
	public function setTitle($title) {
		if($title) {
			$this->title = $title;
			return $this;
		}
		throw new questionException('invalid title');
	}

	/**
	 *	@inheritdoc
	 */
	public function setId($id) {
		if($id) {
			$this->id = $id;
			return $this;
		}
		throw new questionException('invalid id');
	}

	/**
	 *	@inheritdoc
	 */
	public function setQuestion($question) {
		if($question) {
			$this->question = $question;
			return $this;
		}
		throw new questionException('invalid id');
	}

	/**
	 *	@inheritdoc
	 */
	public function addAnswer($answer, $correct) {
		if($answer) {
			$this->answers[md5($answer)] = $answer;
			if($correct) {
				$this->correct_answers[] = md5($answer);
			}
			return $this;
		}
		throw new questionException('invalid answer');
	}

	/**
	 *	@inheritdoc
	 */
	public function setType($question_type) {
		if(QuestionTypes::validType($question_type)) {
			$this->type = $question_type;
			return $this;
		}
		throw new questionException('Unknown question type');
	}
}