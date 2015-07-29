<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class ilWACSecurePath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACSecurePath extends ActiveRecord {

	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return 'il_wac_secure_path';
	}


	/**
	 * @param ilWACPath $ilWACPath
	 *
	 * @return ilWACCheckingClass
	 * @throws ilWACException
	 */
	public static function getCheckingInstance(ilWACPath $ilWACPath) {
		/**
		 * @var $obj ilWACSecurePath
		 */
		$obj = self::find($ilWACPath->getSecurePathId());
		if (! $obj) {
			return null;
//			throw new ilWACException(ilWACException::NO_CHECKING_INSTANCE);
		}
		require_once($obj->getComponentDirectory() . '/classes/class.' . $obj->getCheckingClass() . '.php');
		$class_name = $obj->getCheckingClass();

		return new $class_name();
	}


	/**
	 * @return bool
	 */
	public function hasCheckingInstance() {
		return $this->has_checking_instance;
	}


	/**
	 * @var string
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $path = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $component_directory = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $checking_class = '';
	/**
	 * @var bool
	 */
	protected $has_checking_instance = false;


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getComponentDirectory() {
		return $this->component_directory;
	}


	/**
	 * @param string $component_directory
	 */
	public function setComponentDirectory($component_directory) {
		$this->component_directory = $component_directory;
	}


	/**
	 * @return string
	 */
	public function getCheckingClass() {
		return $this->checking_class;
	}


	/**
	 * @param string $checking_class
	 */
	public function setCheckingClass($checking_class) {
		$this->checking_class = $checking_class;
	}


	/**
	 * @param boolean $has_checking_instance
	 */
	public function setHasCheckingInstance($has_checking_instance) {
		$this->has_checking_instance = $has_checking_instance;
	}
}

?>
