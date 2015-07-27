<?php
require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');

/**
 * Class ilWebAccessChecker
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWebAccessChecker {

	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var bool
	 */
	protected $checked = false;
	/**
	 * @var array
	 */
	protected static $image_suffixes = array(
		'png',
		'jpg',
		'jpeg',
		'gif',
		'svg',
	);


	public function checkAndDeliver() {
		if ($this->check()) {
			$this->deliver();
		} else {
			$this->deny();
		}
	}


	/**
	 * ilWebAccessChecker constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path) {
		$this->setNormalizedPath($path);
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function check() {
		if (! $this->getPath()) {
			throw new ilWACException(ilWACException::CODE_NO_PATH);
		}

		$ilWACSignedPath = new ilWACSignedPath($this->getPath());
		if ($ilWACSignedPath->isSignedPath()) {
			$this->setChecked(true);

			return $ilWACSignedPath->isSignedPathValid();
		}

		$this->setChecked(true);

		return false;
	}


	/**
	 * @return bool
	 */
	public function isImage() {
		return in_array(strtolower($this->getSuffix()), self::$image_suffixes);
	}


	/**
	 * @return mixed
	 */
	public function getSuffix() {
		$parts = parse_url($this->getPath());

		return pathinfo($parts['path'], PATHINFO_EXTENSION);
	}


	public function deliver() {
		if (! $this->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}

		ilFileDelivery::deliverFileInline($this->getPath());
	}


	public function deny() {
		if (! $this->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}
		throw new ilWACException(ilWACException::ACCESS_DENIED);
	}


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
	 * @return boolean
	 */
	public function isChecked() {
		return $this->checked;
	}


	/**
	 * @param boolean $checked
	 */
	public function setChecked($checked) {
		$this->checked = $checked;
	}


	/**
	 * @param $path
	 */
	protected function setNormalizedPath($path) {
		$this->setPath(self::normalizePath($path));
	}


	/**
	 * @param $path
	 *
	 * @return string
	 */
	public static function normalizePath($path) {
		if ($path[0] == '/' && ! is_file($path)) {
			$path = '.' . $path;
		}
		$path = str_replace('././', './', $path);

		return $path;
	}
}

?>
