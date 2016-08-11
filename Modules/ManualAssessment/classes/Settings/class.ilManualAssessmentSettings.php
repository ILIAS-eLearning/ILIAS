<?php

class ilManualAssessmentSettings {
	const DEF_CONTENT = "";
	const DEF_RECORD_TEMPLATE = "";

	protected $content;
	protected $record_template;

	public function __construct(ilObjManualAssessment $mass, $content = null, $record_template = null) {
		$this->id = $mass->getId();
		$this->content = $content !== null ? $content : self::DEF_CONTENT;
		$this->record_template = $record_template !== null ? $record_template : self::DEF_RECORD_TEMPLATE;
	}

	public function getId() {
		return $this->id;
	}

	public function content() {
		return $this->content;
	}

	public function recordTemplate() {
		return $this->record_template;
	}

	public function setContent($content) {
		assert('is_string($content)');
		$this->content = $content;
		return $this;
	}

	public function setRecordTemplate($record_template) {
		assert('is_string($record_template)');
		$this->record_template = $record_template;
		return $this;
	}
}