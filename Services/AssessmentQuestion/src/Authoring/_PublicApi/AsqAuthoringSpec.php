<?php
namespace ILIAS\AssessmentQuestion\Authoring\_PublicApi;

use ilLanguage;

/**
 * Class AsqAuthoringSpec
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqAuthoringSpec {

	/**
	 * @var  \ilGlobalPageTemplate $tpl
	 */
	public $tpl;
	/**
	 * @var  \ilLanguage $lng
	 */
	public $lng;

	/**
	 * AsqAuthoringSpec
	 */
	public function __construct(\ilGlobalPageTemplate $tpl, ilLanguage $lng) {
		$this->withIlTemplate($tpl);
		$this->withLanguage($lng);
	}


	/**
	 * @param $tpl
	 */
	protected function withIlTemplate($tpl) {
		$this->tpl = $tpl;
	}

	/**
	 * ilLanguage
	 */
	protected function withLanguage(\ilLanguage $lng) {
		$this->lng = $lng;
	}


	/**
	 * AggregateWithRevisionId[]
	 * $arr_aggregate_with_revision_id
	 */
	public function withFilterAggregateWithRevisionId($arr_aggregate_with_revision_id) {

	}
}