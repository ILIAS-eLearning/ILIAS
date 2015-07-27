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
	 * @param $full_path
	 */
	public static function getCheckingInstance($full_path) {

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
	protected $classes_dir = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $checking_class = '';


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
	public function getClassesDir() {
		return $this->classes_dir;
	}


	/**
	 * @param string $classes_dir
	 */
	public function setClassesDir($classes_dir) {
		$this->classes_dir = $classes_dir;
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
}

?>
