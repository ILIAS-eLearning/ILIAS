<?php

/**
 * Class ilGlobalCacheSettings
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheSettings {

	const INI_HEADER_CACHE = 'cache';
	const INI_FIELD_ACTIVATE_GLOBAL_CACHE = 'activate_global_cache';
	const INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE = 'global_cache_service_type';
	const INI_HEADER_CACHE_ACTIVATED_COMPONENTS = 'cache_activated_components';
	/**
	 * @var int
	 */
	protected $service = ilGlobalCache::TYPE_STATIC;
	/**
	 * @var array
	 */
	protected $activated_components = array();
	/**
	 * @var bool
	 */
	protected $active = false;


	/**
	 * @param ilIniFile $ilIniFile
	 */
	public function readFromIniFile(ilIniFile $ilIniFile) {
		$this->checkIniHeader($ilIniFile);
		$this->setActive($ilIniFile->readVariable(self::INI_HEADER_CACHE, self::INI_FIELD_ACTIVATE_GLOBAL_CACHE));
		$this->setService($ilIniFile->readVariable(self::INI_HEADER_CACHE, self::INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE));
		foreach ($ilIniFile->readGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS) as $comp => $v) {
			if($v) {
				$this->addActivatedComponent($comp);
			}
		}
	}


	/**
	 * @param ilIniFile $ilIniFile
	 */
	public function writeToIniFile(ilIniFile $ilIniFile) {
		$ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_ACTIVATE_GLOBAL_CACHE, $this->isActive() ? '1' : '0');
		$ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE, $this->getService());
		$ilIniFile->removeGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
		$ilIniFile->addGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
		foreach (ilGlobalCache::$available_components as $comp) {
			$ilIniFile->setVariable(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS, $comp, $this->isComponentActivated($comp) ? '1' : '0');
		}
		$ilIniFile->write();
	}


	/**
	 * @param $component
	 */
	public function addActivatedComponent($component) {
		$this->activated_components[] = $component;
		$this->activated_components = array_unique($this->activated_components);
	}


	/**
	 * @param $component
	 *
	 * @return bool
	 */
	public function isComponentActivated($component) {
		return in_array($component, $this->activated_components);
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
	 * @return array
	 */
	public function getActivatedComponents() {
		return $this->activated_components;
	}


	/**
	 * @param array $activated_components
	 */
	public function setActivatedComponents($activated_components) {
		$this->activated_components = $activated_components;
	}


	/**
	 * @return boolean
	 */
	public function isActive() {
		return $this->active;
	}


	/**
	 * @param boolean $active
	 */
	public function setActive($active) {
		$this->active = $active;
	}


	/**
	 * @param ilIniFile $ilIniFile
	 */
	protected function checkIniHeader(ilIniFile $ilIniFile) {
		if (! $ilIniFile->readGroup(self::INI_HEADER_CACHE)) {
			$ilIniFile->addGroup(self::INI_HEADER_CACHE);
		}
		if (! $ilIniFile->readGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS)) {
			$ilIniFile->addGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
		}
	}
}

?>
