<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Config;

class AnswerOptionFormFieldDefinition {
	const TYPE_TEXT = 'TextField';
	const TYPE_NUMBER = 'NumberField';
	const TYPE_IMAGE = 'ImageField';

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