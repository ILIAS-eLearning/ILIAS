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
	 * @var int;
	 */
	public $container_id;
	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * AsqAuthoringSpec
	 */
	public function __construct(\ilGlobalPageTemplate $tpl, ilLanguage $lng,$container_id, int $user_id) {
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->container_id = $container_id;
		$this->user_id = $user_id;
	}
}