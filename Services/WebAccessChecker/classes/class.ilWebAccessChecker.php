<?php
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACLog.php');
require_once('./Services/WebAccessChecker/classes/class.ilHTTP.php');

/**
 * Class ilWebAccessChecker
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWebAccessChecker {

	const DISPOSITION = 'disposition';
	const STATUS_CODE = 'status_code';
	const REVALIDATE = 'revalidate';
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
	 * @var bool
	 */
	protected $send_status_code = false;
	/**
	 * @var bool
	 */
	protected $initialized = false;
	/**
	 * @var bool
	 */
	protected $revalidate_folder_tokens = true;
	/**
	 * @var bool
	 */
	protected static $DEBUG = true;


	/**
	 * @param string $path
	 */
	public static function run($path) {
		$ilWebAccessChecker = new self($path);
		if ($_GET[self::DISPOSITION]) {
			$ilWebAccessChecker->setDisposition($_GET[self::DISPOSITION]);
		}
		if ($_GET[self::STATUS_CODE]) {
			$ilWebAccessChecker->setSendStatusCode($_GET[self::STATUS_CODE]);
		}
		if ($_GET[self::REVALIDATE]) {
			$ilWebAccessChecker->setRevalidateFolderTokens($_GET[self::REVALIDATE]);
		}

		try {
			if ($ilWebAccessChecker->check()) {
				$ilWebAccessChecker->deliver();
			} else {
				$ilWebAccessChecker->deny();
			}
		} catch (ilWACException $e) {
			switch ($e->getCode()) {
				case ilWACException::ACCESS_DENIED:
					$ilWebAccessChecker->handleAccessErrors($e);
					break;
				case ilWACException::ACCESS_WITHOUT_CHECK:
				case ilWACException::INITIALISATION_FAILED:
				case ilWACException::NO_CHECKING_INSTANCE:
				default:
					$ilWebAccessChecker->handleErrors($e);
					break;
			}
		}
	}


	/**
	 * @throws ilWACException
	 *
	 * remove after deploy
	 */
	public static function install() {
		$obj = new self('tmp');
		$obj->initILIAS();
		require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');
		ilWACSecurePath::installDB();
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
	protected function check() {
		ilWACLog::getInstance()->write(str_repeat('#', 100));
		ilWACLog::getInstance()->write('Checking file: ' . $this->getPathObject()->getPathWithoutQuery());
		if (! $this->getPathObject()) {
			throw new ilWACException(ilWACException::CODE_NO_PATH);
		}

		// Check if Path has been signed with a token
		$ilWACSignedPath = new ilWACSignedPath($this->getPathObject());
		if ($ilWACSignedPath->isSignedPath()) {
			if ($ilWACSignedPath->isSignedPathValid()) {
				$this->setChecked(true);
				ilWACLog::getInstance()->write('checked using token');

				return true;
			}
		}

		// Check if the whole secured folder has been signed
		if ($ilWACSignedPath->isFolderSigned()) {
			if ($ilWACSignedPath->isFolderTokenValid()) {
				if ($this->isRevalidateFolderTokens()) {
					$ilWACSignedPath->revalidatingFolderToken();
				}
				$this->setChecked(true);
				ilWACLog::getInstance()->write('checked using secure folder');

				return true;
			}
		}

		// Fallback, have to initiate ILIAS
		$this->initILIAS();

		// Maybe the path has been registered, lets check
		$checkingInstance = ilWACSecurePath::getCheckingInstance($this->getPathObject());
		if ($checkingInstance instanceof ilWACCheckingClass) {
			ilWACLog::getInstance()->write('has checking instance: ' . get_class($checkingInstance));
			$canBeDelivered = $checkingInstance->canBeDelivered($this->getPathObject());
			if ($canBeDelivered) {
				ilWACLog::getInstance()->write('checked using fallback');
				if ($this->isRevalidateFolderTokens()) {
					$ilWACSignedPath->revalidatingFolderToken();
				}

				$this->setChecked(true);

				return true;
			}
		}

		// Files in ^/data/.*/sec Folder can be checked automatically. this part will be refactored to the new registry method
		if ($ilWACSignedPath->getPathObject()->isInSecFolder()) {
			ilWACLog::getInstance()->write('this file is in sec folder');
			$component = substr($ilWACSignedPath->getPathObject()->getSecurePathId(), 2);
			$comp_dir = NULL;
			switch (true) {
				case ilComponent::lookupId(IL_COMP_MODULE, $component):
					$comp_dir = "Modules";
					break;
				case ilComponent::lookupId(IL_COMP_SERVICE, $component):
					$comp_dir = "Services";
					break;
			}
			if ($comp_dir) {
				$comp_class = "il" . $component . "WebAccessChecker";
				$comp_include = $comp_dir . "/" . $component . "/classes/class." . $comp_class . ".php";
				if (file_exists($comp_include)) {
					include_once $comp_include;
					if (class_exists($comp_class)) {
						$comp_inst = new $comp_class();
						if ($comp_inst instanceof ilComponentWebAccessChecker) {
							if ($comp_inst->isValidPath(explode('/', $ilWACSignedPath->getPathObject()->getPath()))) {
								$obj_id = $comp_inst->getRepositoryObjectId();
								global $ilAccess;
								$obj_type = ilObject::_lookupType($obj_id);
								$ref_ids = ilObject::_getAllReferences($obj_id);
								foreach ($ref_ids as $ref_id) {
									global $ilUser;
									if ($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $ref_id, $obj_type, $obj_id)) {
										$this->setChecked(true);

										return true;
									}
								}
							}
						}
					}
				}
			}
		}

		// none of the checking mechanisms could have been applied. no access
		$this->setChecked(true);
		ilWACLog::getInstance()->write('none of the checking mechanisms could have been applied. no access');

		return false;
	}


	protected function initILIAS() {
		if ($this->isInitialized()) {
			return true;
		}
		require_once('./Services/Init/classes/class.ilInitialisation.php');
		$GLOBALS['COOKIE_PATH'] = '/';
		setcookie('ilClientId', $this->getPathObject()->getClient(), 0, '/');
		ilContext::init(ilContext::CONTEXT_WAC);
		try {
			ilWACLog::getInstance()->write('init ILIAS');
			ilInitialisation::initILIAS();
			global $ilUser, $ilSetting;
			// No User seems to be logged in, we have to re-init ILIAS for anonymous
			if ($ilUser->getId() == 0) {
				if (! $ilSetting->get('pub_section')) {
					// ILIAS does not support public access, abort. Access isn't possible
					throw new ilWACException(ilWACException::ACCESS_DENIED);
				}
				$_POST['username'] = 'anonymous';
				$_POST['password'] = 'anonymous';
				ilWACLog::getInstance()->write('have to re-init ILIAS since theres no User to get checked');
				ilInitialisation::reinitILIAS();
			}
		} catch (Exception $e) {
			throw new ilWACException(ilWACException::INITIALISATION_FAILED);
		}
		$this->setInitialized(true);
	}


	protected function deliver() {
		if (! $this->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}

		switch ($this->getDisposition()) {
			case ilFileDelivery::DISP_ATTACHMENT:
				ilFileDelivery::deliverFileAttached($this->getPathObject()->getPath());
				break;
			case ilFileDelivery::DISP_INLINE:
				if ($this->getPathObject()->isVideo()) {
					ilWACLog::getInstance()->write('begin streaming');
					ilFileDelivery::streamVideoInline($this->getPathObject()->getPath());
				} else {
					ilFileDelivery::deliverFileInline($this->getPathObject()->getPath());
				}
				break;
		}
	}


	protected function deny() {
		if (! $this->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}
		throw new ilWACException(ilWACException::ACCESS_DENIED);
	}


	protected function deliverDummyImage() {
		$ilFileDelivery = new ilFileDelivery('./Services/WebAccessChecker/templates/images/access_denied.png', $this->getPathObject()->getFileName());
		$ilFileDelivery->setDisposition($this->getDisposition());
		$ilFileDelivery->deliver();
	}


	protected function deliverDummyVideo() {
		$ilFileDelivery = new ilFileDelivery('./Services/WebAccessChecker/templates/images/access_denied.mp4', $this->getPathObject()->getFileName());
		$ilFileDelivery->setDisposition($this->getDisposition());
		$ilFileDelivery->stream();
	}


	/**
	 * @param ilWACException $e
	 */
	protected function handleAccessErrors(ilWACException $e) {
		ilHTTP::STATUS(401);
		if ($this->getPathObject()->isImage()) {
			$this->deliverDummyImage();
		}
		if ($this->getPathObject()->isVideo()) {
			$this->deliverDummyVideo();
		}

		$this->initILIAS();

		global $tpl, $ilLog;
		$ilLog->write($e->getMessage());
		$tpl->setVariable('BASE', strstr($_SERVER['REQUEST_URI'], '/data', true) . '/');
		ilUtil::sendFailure($e->getMessage());
		$tpl->getStandardTemplate();
		$tpl->show();
	}


	/**
	 * @param ilWACException $e
	 */
	protected function handleErrors(ilWACException $e) {
		ilHTTP::STATUS(500);
		echo $e->getMessage();
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


	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->initialized;
	}


	/**
	 * @param boolean $initialized
	 */
	public function setInitialized($initialized) {
		$this->initialized = $initialized;
	}


	/**
	 * @return boolean
	 */
	public function isSendStatusCode() {
		return $this->send_status_code;
	}


	/**
	 * @param boolean $send_status_code
	 */
	public function setSendStatusCode($send_status_code) {
		$this->send_status_code = $send_status_code;
	}


	/**
	 * @return boolean
	 */
	public function isRevalidateFolderTokens() {
		return $this->revalidate_folder_tokens;
	}


	/**
	 * @param boolean $revalidate_folder_tokens
	 */
	public function setRevalidateFolderTokens($revalidate_folder_tokens) {
		$this->revalidate_folder_tokens = $revalidate_folder_tokens;
	}


	/**
	 * @return boolean
	 */
	public static function isDEBUG() {
		return self::$DEBUG;
	}


	/**
	 * @param boolean $DEBUG
	 */
	public static function setDEBUG($DEBUG) {
		self::$DEBUG = $DEBUG;
	}
}

?>
