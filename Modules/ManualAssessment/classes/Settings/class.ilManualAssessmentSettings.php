<?php
/**
 * An object carrying settings of an Manual Assessment obj
 * beyond the standart information
 */
class ilManualAssessmentSettings {
	const DEF_CONTENT = "";
	const DEF_RECORD_TEMPLATE = "";

	/**
	 * @var	string
	 */
	protected $content;

	/**
	 * @var	string
	 */
	protected $record_template;

	public function __construct(ilObjManualAssessment $mass, $content = null, $record_template = null) {
		$this->id = $mass->getId();
		$this->content = $content !== null ? $content : self::DEF_CONTENT;
		$this->record_template = $record_template !== null ? $record_template : self::DEF_RECORD_TEMPLATE;
	}

	/**
	 * Get the id of corrwsponding mass-object
	 *
	 * @return	int|string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get the content of this assessment, e.g. corresponding topics...
	 *
	 * @return	string
	 */
	public function content() {
		return $this->content;
	}

	/**
	 * Get the record template to be used as default record with
	 * corresponding object
	 *
	 * @return	string
	 */
	public function recordTemplate() {
		return $this->record_template;
	}

	/**
	 * Set the content of this assessment, e.g. corresponding topics...
	 *
	 * @param	string	$content
	 * @return	ilManualAssessment	$this
	 */
	public function setContent($content) {
		assert('is_string($content)');
		$this->content = $content;
		return $this;
	}

	/**
	 * Get the record template to be used as default record with
	 * corresponding object
	 *
	 * @param	string	$record_template
	 * @return	ilManualAssessment	$this
	 */
	public function setRecordTemplate($record_template) {
		assert('is_string($record_template)');
		$this->record_template = $record_template;
		return $this;
	}
}