<?php
// declare(strict_types=1);

require_once './Services/ActiveRecord/class.ActiveRecord.php';
require_once './Services/WebAccessChecker/class.ilWACException.php';

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
	public static function getCheckingInstance(ilWACPath $ilWACPath)  {
		/**
		 * @var $obj ilWACSecurePath
		 */
		$obj = self::find($ilWACPath->getModuleType());
		if (!$obj) {
			throw new ilWACException(ilWACException::CODE_NO_PATH, 'No Checking Instance found for id: ' . $ilWACPath->getSecurePathId());
		}

		$secure_path_checking_class = $obj->getComponentDirectory() . '/classes/class.' . $obj->getCheckingClass() . '.php';
		if (!file_exists($secure_path_checking_class)) {
			throw new ilWACException(ilWACException::CODE_NO_PATH, 'Checking Instance not found in path: ' . $secure_path_checking_class);
		}

		require_once($secure_path_checking_class);
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
	 * @con_length     64
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
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $in_sec_folder = false;
	/**
	 * @var bool
	 */
	protected $has_checking_instance = false;


	/**
	 * @return string
	 */
	public function getPath()  {
		return (string)$this->path;
	}


	/**
	 * @param string $path
	 * @return void
	 */
	public function setPath( $path) {
		assert(is_string($path));
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getComponentDirectory()  {
		preg_match("/[\\\|\\/](Services|Modules|Customizing)[\\\|\\/].*/u", $this->component_directory, $matches);

		return (string)'.' . $matches[0];
	}


	/**
	 * @param string $component_directory
	 * @return void
	 */
	public function setComponentDirectory( $component_directory) {
		assert(is_string($component_directory));
		$this->component_directory = $component_directory;
	}


	/**
	 * @return string
	 */
	public function getCheckingClass()  {
		return (string)$this->checking_class;
	}


	/**
	 * @param string $checking_class
	 * @return void
	 */
	public function setCheckingClass( $checking_class) {
		assert(is_string($checking_class));
		$this->checking_class = $checking_class;
	}


	/**
	 * @param bool $has_checking_instance
	 * @return void
	 */
	public function setHasCheckingInstance($has_checking_instance) {
		assert(is_bool($has_checking_instance));
		$this->has_checking_instance = $has_checking_instance;
	}


	/**
	 * @return string
	 */
	public function getInSecFolder()  {
		return (string)$this->in_sec_folder;
	}


	/**
	 * @param string $in_sec_folder
	 */
	public function setInSecFolder( $in_sec_folder) {
		// assert(is_string($in_sec_folder));
		$this->in_sec_folder = $in_sec_folder;
	}
}
