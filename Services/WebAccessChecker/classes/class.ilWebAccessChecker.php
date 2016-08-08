<?php
require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACLog.php');
require_once('./Services/Init/classes/class.ilInitialisation.php');
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACCookie.php');

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
	const CM_FILE_TOKEN = 1;
	const CM_FOLDER_TOKEN = 2;
	const CM_CHECKINGINSTANCE = 3;
	const CM_SECFOLDER = 4;
	/**
	 * @var ilWACPath
	 */
	protected $path_object = null;
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
	protected static $DEBUG = false;
	/**
	 * @var bool
	 */
	protected static $use_seperate_logfile = false;
	/**
	 * @var ilWACCookieInterface
	 */
	protected $cookie = null;
	/**
	 * @var array
	 */
	protected $applied_checking_methods = array();


	/**
	 * ilWebAccessChecker constructor.
	 *
	 * @param $path
	 * @param \ilWACCookieInterface|null $ilWACCookieInterface
	 */
	public function __construct($path, ilWACCookieInterface $ilWACCookieInterface = null) {
		$this->setPathObject(new ilWACPath($path));
		$this->setCookie($ilWACCookieInterface ? $ilWACCookieInterface : new ilWACCookie());
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function check() {
		ilWACLog::getInstance()->write('Checking File: ' . $this->getPathObject()->getPathWithoutQuery());
		if (!$this->getPathObject()) {
			throw new ilWACException(ilWACException::CODE_NO_PATH);
		}

		// Check if Path has been signed with a token
		$ilWACSignedPath = new ilWACSignedPath($this->getPathObject(), $this->cookie);
		if ($ilWACSignedPath->isSignedPath()) {
			$this->addAppliedCheckingMethod(self::CM_FILE_TOKEN);
			if ($ilWACSignedPath->isSignedPathValid()) {
				$this->setChecked(true);
				ilWACLog::getInstance()->write('checked using token');

				return true;
			}
		}

		// Check if the whole secured folder has been signed
		if ($ilWACSignedPath->isFolderSigned()) {
			$this->addAppliedCheckingMethod(self::CM_FOLDER_TOKEN);
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
			$this->addAppliedCheckingMethod(self::CM_CHECKINGINSTANCE);
			ilWACLog::getInstance()->write('has checking instance: ' . get_class($checkingInstance));
			$canBeDelivered = $checkingInstance->canBeDelivered($this->getPathObject());
			if ($canBeDelivered) {
				ilWACLog::getInstance()->write('checked using fallback');
				if ($this->isRevalidateFolderTokens()) {
					$ilWACSignedPath->revalidatingFolderToken();
				}

				$this->setChecked(true);

				return true;
			} else {
				ilWACLog::getInstance()->write('checking-instance denied access');
				$this->setChecked(true);

				return false;
			}
		}

		// none of the checking mechanisms could have been applied. no access
		$this->setChecked(true);
		ilWACLog::getInstance()->write('none of the checking mechanisms could have been applied. access depending on sec folder');
		if ($this->getPathObject()->isInSecFolder()) {
			$this->addAppliedCheckingMethod(self::CM_SECFOLDER);
			ilWACLog::getInstance()->write('file is in sec-folder, no delivery');

			return false;
		} else {
			$this->addAppliedCheckingMethod(self::CM_SECFOLDER);
			ilWACLog::getInstance()->write('file is not in sec-folder, delivery');

			return true;
		}
	}


	/**
	 * @return bool
	 * @throws \ilWACException
	 */
	public function initILIAS() {
		if ($this->isInitialized()) {
			return true;
		}
		$GLOBALS['COOKIE_PATH'] = '/';
		$this->cookie->set('ilClientId', $this->getPathObject()->getClient(), 0, '/');
		ilContext::init(ilContext::CONTEXT_WAC);
		try {
			ilWACLog::getInstance()->write('init ILIAS');
			ilInitialisation::initILIAS();
			$this->checkPublicSection();
			$this->checkUser();
		} catch (Exception $e) {
			if ($e instanceof ilWACException) {
				throw  $e;
			}
			if ($e instanceof Exception && $e->getMessage() == 'Authentication failed.') {
				$_REQUEST["baseClass"] = "ilStartUpGUI";
				// @todo authentication: fix request show login
				$_REQUEST["cmd"] = "showLogin";

				$_POST['username'] = 'anonymous';
				$_POST['password'] = 'anonymous';
				ilWACLog::getInstance()->write('reinit ILIAS');
				ilInitialisation::reinitILIAS();
				$this->checkPublicSection();
				$this->checkUser();
			}
		}
		$this->setInitialized(true);
	}


	protected function checkPublicSection() {
		global $ilSetting, $ilUser;
		if (!$ilSetting instanceof ilSetting || ($ilUser->getId() == ANONYMOUS_USER_ID && !$ilSetting->get('pub_section'))) {
			ilWACLog::getInstance()->write('public section not activated');
			throw new ilWACException(ilWACException::ACCESS_DENIED_NO_PUB);
		}
	}


	protected function checkUser() {
		global $ilUser;
		if (!$ilUser instanceof ilObjUser || ($ilUser->getId() == 0 && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false)) {
			throw new ilWACException(ilWACException::ACCESS_DENIED_NO_LOGIN);
		}
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


	/**
	 * @return boolean
	 */
	public static function isUseSeperateLogfile() {
		return self::$use_seperate_logfile;
	}


	/**
	 * @param boolean $use_seperate_logfile
	 */
	public static function setUseSeperateLogfile($use_seperate_logfile) {
		self::$use_seperate_logfile = $use_seperate_logfile;
	}


	/**
	 * @return \ilWACCookieInterface
	 */
	public function getCookie() {
		return $this->cookie;
	}


	/**
	 * @param \ilWACCookieInterface $cookie
	 */
	public function setCookie($cookie) {
		$this->cookie = $cookie;
	}


	/**
	 * @return array
	 */
	public function getAppliedCheckingMethods() {
		return $this->applied_checking_methods;
	}


	/**
	 * @param array $applied_checking_methods
	 */
	public function setAppliedCheckingMethods($applied_checking_methods) {
		$this->applied_checking_methods = $applied_checking_methods;
	}


	/**
	 * @param int $method
	 */
	protected function addAppliedCheckingMethod($method) {
		$this->applied_checking_methods[] = $method;
	}
}
