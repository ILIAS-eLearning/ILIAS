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


	public static function run() {
		$ilWebAccessChecker = new self($_SERVER['REQUEST_URI']);
		try {
			if ($ilWebAccessChecker->check()) {
				$ilWebAccessChecker->deliver();
			} else {
				$ilWebAccessChecker->deny();
			}
		} catch (ilWACException $e) {
			if ($ilWebAccessChecker->getPathObject()->isImage()) {
				ilFileDelivery::deliverFileInline('./Services/WebAccessChecker/templates/images/access_denied.png');
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


	/**
	 * ilWebAccessChecker constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path) {
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
		session_destroy();
		ilContext::init(ilContext::CONTEXT_WEB_ACCESS_CHECK);
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
}

?>
