<?php
require_once("./Services/ContainerReference/classes/class.ilContainerSelectionExplorer.php");

/**
 * Class ilAsyncExplorer
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncContainerSelectionExplorer extends ilContainerSelectionExplorer {

	protected $tpl;

	protected static $js_conf;
	protected static $js_on_load_added = array();

	public function __construct($a_target) {
		parent::__construct($a_target);

		global $tpl;
		$this->tpl = $tpl;

		$this->addJsConf('save_explorer_url', $a_target);
	}

	public function buildOnClick($a_node_id, $a_type, $a_title)
	{
		$ref_id = (int) $_GET['ref_id'];
		if($ref_id) {
			return "$('body').trigger('async_explorer-add_reference', {target_id: '".$a_node_id."', type: '".$a_type."', parent_id: '".$ref_id."'});";
		}
	}

	public function buildLinkTarget($a_node_id, $a_type)
	{
		return "javascript:void(0);";
	}



	public function getOutput() {
		self::initJs();

		return parent::getOutput();
	}

	public function initJs() {
		self::addOnLoadCode('explorer', '$("#'.$this->getId().'").training_programme_async_explorer('.json_encode($this->js_conf).');');
	}

	/**
	 * Adds onload code to the template
	 *
	 * @param $id
	 * @param $content
	 */
	protected function addOnLoadCode($id, $content) {
		global $tpl;

		if(!isset(self::$js_on_load_added[$id])) {
			$tpl->addOnLoadCode($content);
			self::$js_on_load_added[$id] = $content;
		}
	}

	/**
	 *
	 * @param array $js_conf
	 */
	public function addJsConf($key, $value) {
		$this->js_conf[$key] = $value;
	}

	/**
	 *
	 * @return string
	 */
	public function getJsConf($key) {
		return $this->js_conf[$key];
	}
}