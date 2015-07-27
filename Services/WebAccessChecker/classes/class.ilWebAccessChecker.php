<?php
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');

/**
 * Class ilWebAccessChecker
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWebAccessChecker {

	/**
	 * @var ilWACPath
	 */
	protected $path_object = NULL;
	/**
	 * @var bool
	 */
	protected $checked = false;
	/**
	 * @var string
	 */
	protected $disposition = ilFileDelivery::DISP_INLINE;
	/**
	 * @var string
	 */
	protected $override_mimetype = '';


	/**
	 * @param string $path
	 */
	public static function run($path) {
		$ilWebAccessChecker = new self($path);
		try {
			if ($ilWebAccessChecker->check()) {
				$ilWebAccessChecker->deliver();
			} else {
				$ilWebAccessChecker->deny();
			}
		} catch (ilWACException $e) {
			if ($ilWebAccessChecker->getPathObject()->isImage()) {
				$ilWebAccessChecker->deliverDummyImage();
			}

			$ilWebAccessChecker->initILIAS();

			global $tpl, $ilLog;
			$ilLog->write($e->getMessage());
			$tpl->setVariable('BASE', strstr($_SERVER['REQUEST_URI'], '/data', true) . '/');
			ilUtil::sendFailure($e->getMessage());
			$tpl->getStandardTemplate();
			$tpl->show();
		}
	}


	protected function deliverDummyImage() {
		$ilFileDelivery = new ilFileDelivery('./Services/WebAccessChecker/templates/images/access_denied.png');
		$ilFileDelivery->setDisposition($this->getDisposition());
		$ilFileDelivery->deliver();
	}


	/**
	 * ilWebAccessChecker constructor.
	 *
	 * @param string $path
	 */
	protected function __construct($path) {
		$this->setPathObject(new ilWACPath($path));
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function check() {
		if (! $this->getPathObject()) {
			throw new ilWACException(ilWACException::CODE_NO_PATH);
		}

		$ilWACSignedPath = new ilWACSignedPath($this->getPathObject());
		if ($ilWACSignedPath->isSignedPath()) {
			if ($ilWACSignedPath->isSignedPathValid()) {
				$this->setChecked(true);

				return true;
			}
		}
		$this->initILIAS();

		$checkingInstance = ilWACSecurePath::getCheckingInstance($this->getPathObject());

		if ($checkingInstance instanceof ilWACCheckingClass) {
			$canBeDelivered = $checkingInstance->canBeDelivered($this->getPathObject());
			if ($canBeDelivered) {
				$this->setChecked(true);

				return true;
			}
		}
		$this->setChecked(true);

		return false;
	}


	public function initILIAS() {
		require_once('./Services/Init/classes/class.ilInitialisation.php');
		ilContext::init(ilContext::CONTEXT_WAC);
		ilInitialisation::initILIAS();
	}


	public function deliver() {
		if (! $this->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}

		ilFileDelivery::deliverFileInline($this->getPathObject()->getPath());
	}


	public function deny() {
		if (! $this->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}
		throw new ilWACException(ilWACException::ACCESS_DENIED);
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
	 * @return ilWACPath
	 */
	public function getPathObject() {
		return $this->path_object;
	}


	/**
	 * @param ilWACPath $path_object
	 */
	public function setPathObject($path_object) {
		$this->path_object = $path_object;
	}


	/**
	 * @return string
	 */
	public function getDisposition() {
		return $this->disposition;
	}


	/**
	 * @param string $disposition
	 */
	public function setDisposition($disposition) {
		$this->disposition = $disposition;
	}


	/**
	 * @return string
	 */
	public function getOverrideMimetype() {
		return $this->override_mimetype;
	}


	/**
	 * @param string $override_mimetype
	 */
	public function setOverrideMimetype($override_mimetype) {
		$this->override_mimetype = $override_mimetype;
	}
}

?>
