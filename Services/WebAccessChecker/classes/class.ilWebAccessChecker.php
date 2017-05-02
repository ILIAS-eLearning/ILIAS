<?php
declare(strict_types=1);

use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\Cookies\CookieWrapper;
use ILIAS\HTTP\GlobalHttpState;
use Psr\Http\Message\UriInterface;

require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');
require_once('./Services/Init/classes/class.ilInitialisation.php');
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');

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
	protected static $use_seperate_logfile = false;
	/**
	 * @var array
	 */
	protected $applied_checking_methods = array();


    /**
     * @var \ILIAS\DI\HTTPServices $http
     */
    private $http;
	/**
	 * @var CookieFactory $cookieFactory
	 */
    private $cookieFactory;


	/**
	 * ilWebAccessChecker constructor.
	 *
	 * @param GlobalHttpState $httpState
	 * @param CookieFactory   $cookieFactory
	 */
	public function __construct(GlobalHttpState $httpState, CookieFactory $cookieFactory ) {
		$this->setPathObject(new ilWACPath($httpState->request()->getRequestTarget()));
        $this->http = $httpState;
        $this->cookieFactory = $cookieFactory;
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function check() : bool {
		if (!$this->getPathObject()) {
			throw new ilWACException(ilWACException::CODE_NO_PATH);
		}

		// Check if Path has been signed with a token
		$ilWACSignedPath = new ilWACSignedPath($this->getPathObject(), $this->http, $this->cookieFactory);
		if ($ilWACSignedPath->isSignedPath()) {
			$this->addAppliedCheckingMethod(self::CM_FILE_TOKEN);
			if ($ilWACSignedPath->isSignedPathValid()) {
				$this->setChecked(true);
				$this->sendHeader('checked using token');

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
				$this->sendHeader('checked using secure folder');

				return true;
			}
		}

		// Fallback, have to initiate ILIAS
		$this->initILIAS();

		// Maybe the path has been registered, lets check
		$checkingInstance = ilWACSecurePath::getCheckingInstance($this->getPathObject());
		if ($checkingInstance instanceof ilWACCheckingClass) {
			$this->addAppliedCheckingMethod(self::CM_CHECKINGINSTANCE);
			$canBeDelivered = $checkingInstance->canBeDelivered($this->getPathObject());
			if ($canBeDelivered) {
				$this->sendHeader('checked using fallback');
				if ($ilWACSignedPath->isFolderSigned() && $this->isRevalidateFolderTokens()) {
					$ilWACSignedPath->revalidatingFolderToken();
				}

				$this->setChecked(true);

				return true;
			} else {
				$this->setChecked(true);

				return false;
			}
		}

		// none of the checking mechanisms could have been applied. no access
		$this->setChecked(true);
		if ($this->getPathObject()->isInSecFolder()) {
			$this->addAppliedCheckingMethod(self::CM_SECFOLDER);

			return false;
		} else {
			$this->addAppliedCheckingMethod(self::CM_SECFOLDER);

			return true;
		}
	}


	/**
	 * @param string $message
	 * @return void
	 */
	protected function sendHeader(string $message) {
		$response = $this->http->response()->withHeader('X-ILIAS-WebAccessChecker', $message);
        $this->http->saveResponse($response);
	}


	/**
	 * @return void
	 */
	public function initILIAS() {
		if ($this->isInitialized()) {
			return;
		}

		$GLOBALS['COOKIE_PATH'] = '/';

        $cookie = $this->cookieFactory->create('ilClientId', $this->getPathObject()->getClient())
                ->withPath('/')
                ->withExpires(0);

        $response = $this->http->cookieJar()
            ->with($cookie)
            ->renderIntoResponseHeader($this->http->response());

        $this->http->saveResponse($response);

		ilContext::init(ilContext::CONTEXT_WAC);
		try {
			ilInitialisation::initILIAS();
			$this->checkPublicSection();
			$this->checkUser();
		}
		catch (Exception $e) {
			if ($e instanceof Exception && $e->getMessage() == 'Authentication failed.') {
				$request = $this->http->request();
				$queries = $request->getQueryParams();
				$queries["baseClass"] = "ilStartUpGUI";
				$_REQUEST["baseClass"] = "ilStartUpGUI";
				// @todo authentication: fix request show login
				$queries["cmd"] = "showLoginPage";
				$_REQUEST["cmd"] = "showLoginPage";
				$request = $request->withQueryParams($queries);

				$this->http->saveRequest($request);

				ilInitialisation::reinitILIAS();
				$GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, ANONYMOUS_USER_ID);
				$this->checkPublicSection();
				$this->checkUser();
			}
		}
		$this->setInitialized(true);
	}


	/**
	 * @return void
	 * @throws ilWACException
	 */
	protected function checkPublicSection() {
		global $ilSetting, $ilUser;
		if (!$ilSetting instanceof ilSetting || ($ilUser->getId() === ANONYMOUS_USER_ID && !$ilSetting->get('pub_section'))) {
			throw new ilWACException(ilWACException::ACCESS_DENIED_NO_PUB);
		}
	}


	/**
	 * @return void
	 * @throws ilWACException
	 */
	protected function checkUser() {
		global $ilUser;
		$referrer = $_SERVER['HTTP_REFERER'] ?? '';
		if (!$ilUser instanceof ilObjUser || ($ilUser->getId() === 0 && strpos($referrer, 'login.php') === false)) {
			throw new ilWACException(ilWACException::ACCESS_DENIED_NO_LOGIN);
		}
	}


	/**
	 * @return bool
	 */
	public function isChecked() : bool {
		return $this->checked;
	}


	/**
	 * @param boolean $checked
	 * @return void
	 */
	public function setChecked(bool $checked) {
		$this->checked = $checked;
	}


	/**
	 * @return ilWACPath
	 */
	public function getPathObject() : ilWACPath{
		return $this->path_object;
	}


	/**
	 * @param ilWACPath $path_object
	 * @return void
	 */
	public function setPathObject(ilWACPath $path_object) {
		$this->path_object = $path_object;
	}


	/**
	 * @return string
	 */
	public function getDisposition() : string {
		return $this->disposition;
	}


	/**
	 * @param string $disposition
	 * @return void
	 */
	public function setDisposition(string $disposition) {
		$this->disposition = $disposition;
	}


	/**
	 * @return string
	 */
	public function getOverrideMimetype() : string {
		return $this->override_mimetype;
	}


	/**
	 * @param string $override_mimetype
	 * @return void
	 */
	public function setOverrideMimetype(string $override_mimetype) {
		$this->override_mimetype = $override_mimetype;
	}


	/**
	 * @return bool
	 */
	public function isInitialized() : bool {
		return $this->initialized;
	}


	/**
	 * @param bool $initialized
	 */
	public function setInitialized(bool $initialized) {
		$this->initialized = $initialized;
	}


	/**
	 * @return bool
	 */
	public function isSendStatusCode() : bool {
		return $this->send_status_code;
	}


	/**
	 * @param bool $send_status_code
	 * @return void
	 */
	public function setSendStatusCode(bool $send_status_code) {
		$this->send_status_code = $send_status_code;
	}


	/**
	 * @return bool
	 */
	public function isRevalidateFolderTokens() : bool {
		return $this->revalidate_folder_tokens;
	}


	/**
	 * @param bool $revalidate_folder_tokens
	 * @return void
	 */
	public function setRevalidateFolderTokens(bool $revalidate_folder_tokens) {
		$this->revalidate_folder_tokens = $revalidate_folder_tokens;
	}

	/**
	 * @return bool
	 */
	public static function isUseSeperateLogfile() : bool {
		return self::$use_seperate_logfile;
	}


	/**
	 * @param bool $use_seperate_logfile
	 * @return void
	 */
	public static function setUseSeperateLogfile(bool $use_seperate_logfile) {
		self::$use_seperate_logfile = $use_seperate_logfile;
	}

	/**
	 * @return int[]
	 */
	public function getAppliedCheckingMethods() : array {
		return $this->applied_checking_methods;
	}


	/**
	 * @param int[] $applied_checking_methods
	 * @return void
	 */
	public function setAppliedCheckingMethods(array $applied_checking_methods) {
		$this->applied_checking_methods = $applied_checking_methods;
	}


	/**
	 * @param int $method
	 * @return void
	 */
	protected function addAppliedCheckingMethod(int $method) {
		$this->applied_checking_methods[] = $method;
	}
}
