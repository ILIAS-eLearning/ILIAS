<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config;

/**
 * Class AnswerOptionFormFieldDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionFormFieldDefinition {
	const TYPE_TEXT = 'TextField';
	const TYPE_TEXT_AREA = 'TextArea';
	const TYPE_NUMBER = 'NumberField';
	const TYPE_IMAGE = 'ImageField';
    const TYPE_RADIO = "RadioField"; 
	
	/**
	 * @var string
	 */
	private $header;
	/**
	 * @var string
	 */
	private $type;
	/**
	 * @var string
	 */
	private $post_var;
	/**
	 * @var array
	 */
	private $options;


    /**
     * AnswerOptionFormFieldDefinition constructor.
     *
     * @param string     $header
     * @param string     $type
     * @param string     $post_var
     * @param array|null $options
     */
	public function __construct(string $header, string $type, string $post_var, array $options = null) {
		$this->header = $header;
		$this->type = $type;
		$this->post_var = $post_var;
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getHeader(): string {
		return $this->header;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getPostVar(): string {
		return $this->post_var;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array {
		return $this->options;
	}
}