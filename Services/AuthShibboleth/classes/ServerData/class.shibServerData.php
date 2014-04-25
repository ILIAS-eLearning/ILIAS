<?php
require_once('./Services/AuthShibboleth/classes/Config/class.shibConfig.php');

/**
 * Class shibServerData
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibServerData extends shibConfig {

	/**
	 * @var bool
	 */
	protected static $cache = NULL;


	/**
	 * @param array $data
	 */
	protected function __construct(array $data) {
		$shibConfig = shibConfig::getInstance();
		foreach (array_keys(get_class_vars('shibConfig')) as $field) {
			$str = $shibConfig->getValueByKey($field);
			if ($str !== NULL) {
				$this->{$field} = $data[$str];
			}
		}
	}


	/**
	 * @param array $data
	 *
	 * @return shibServerData
	 */
	public static function getInstance(array $data) {
		if (! isset(self::$cache)) {
			self::$cache = new self($data);
		}

		return self::$cache;
	}
}

?>