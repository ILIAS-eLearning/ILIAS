<?php

/**
 * Class ilGlobalCacheSettings
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheSettings {

	/**
	 * @var int
	 */
	protected $service = ilGlobalCache::TYPE_STATIC;
	/**
	 * @var bool
	 */
	protected $activate_language = true;
	/**
	 * @var bool
	 */
	protected $activate_object_definition = true;
	/**
	 * @var bool
	 */
	protected $activate_template = true;
	/**
	 * @var bool
	 */
	protected $activate_control_structure = true;
	/**
	 * @var bool
	 */
	protected $activate_plugins = true;
	/**
	 * @var bool
	 */
	protected $activate_component = true;
	/**
	 * @var bool
	 */
	protected $activate_events = true;


	/**
	 * @param ilIniFile $ilIniFile
	 */
	public function readFromIniFile(ilIniFile $ilIniFile) {

	}


	public function writeToIniFile(ilIniFile $ilIniFile){
		
	}


	/**
	 * @return int
	 */
	public function getService() {
		return $this->service;
	}


	/**
	 * @param int $service
	 */
	public function setService($service) {
		$this->service = $service;
	}


	/**
	 * @return boolean
	 */
	public function isActivateLanguage() {
		return $this->activate_language;
	}


	/**
	 * @param boolean $activate_language
	 */
	public function setActivateLanguage($activate_language) {
		$this->activate_language = $activate_language;
	}


	/**
	 * @return boolean
	 */
	public function isActivateObjectDefinition() {
		return $this->activate_object_definition;
	}


	/**
	 * @param boolean $activate_object_definition
	 */
	public function setActivateObjectDefinition($activate_object_definition) {
		$this->activate_object_definition = $activate_object_definition;
	}


	/**
	 * @return boolean
	 */
	public function isActivateTemplate() {
		return $this->activate_template;
	}


	/**
	 * @param boolean $activate_template
	 */
	public function setActivateTemplate($activate_template) {
		$this->activate_template = $activate_template;
	}


	/**
	 * @return boolean
	 */
	public function isActivateControlStructure() {
		return $this->activate_control_structure;
	}


	/**
	 * @param boolean $activate_control_structure
	 */
	public function setActivateControlStructure($activate_control_structure) {
		$this->activate_control_structure = $activate_control_structure;
	}


	/**
	 * @return boolean
	 */
	public function isActivatePlugins() {
		return $this->activate_plugins;
	}


	/**
	 * @param boolean $activate_plugins
	 */
	public function setActivatePlugins($activate_plugins) {
		$this->activate_plugins = $activate_plugins;
	}


	/**
	 * @return boolean
	 */
	public function isActivateComponent() {
		return $this->activate_component;
	}


	/**
	 * @param boolean $activate_component
	 */
	public function setActivateComponent($activate_component) {
		$this->activate_component = $activate_component;
	}


	/**
	 * @return boolean
	 */
	public function isActivateEvents() {
		return $this->activate_events;
	}


	/**
	 * @param boolean $activate_events
	 */
	public function setActivateEvents($activate_events) {
		$this->activate_events = $activate_events;
	}
}

?>
