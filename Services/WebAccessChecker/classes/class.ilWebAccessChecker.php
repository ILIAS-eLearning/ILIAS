<?php
// declare(strict_types=1);

use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\Cookies\CookieWrapper;
use ILIAS\HTTP\Services;
use Psr\Http\Message\UriInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilWebAccessChecker
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWebAccessChecker
{
    public const DISPOSITION = 'disposition';
    public const STATUS_CODE = 'status_code';
    public const REVALIDATE = 'revalidate';
    public const CM_FILE_TOKEN = 1;
    public const CM_FOLDER_TOKEN = 2;
    public const CM_CHECKINGINSTANCE = 3;
    public const CM_SECFOLDER = 4;

    protected ?ilWACPath $path_object = null;
    protected bool $checked = false;
    protected string $disposition = ilFileDelivery::DISP_INLINE;
    protected string $override_mimetype = '';
    protected bool $send_status_code = false;
    protected bool $initialized = false;
    protected bool $revalidate_folder_tokens = true;
    protected static bool $use_seperate_logfile = false;
    /**
     * @var int[]
     */
    protected array $applied_checking_methods = [];
    private Services $http;
    private CookieFactory $cookieFactory;

    /**
     * ilWebAccessChecker constructor.
     */
    public function __construct(Services $httpState, CookieFactory $cookieFactory)
    {
        $this->setPathObject(new ilWACPath($httpState->request()->getRequestTarget()));
        $this->http = $httpState;
        $this->cookieFactory = $cookieFactory;
    }

    /**
     * @throws ilWACException
     */
    public function check() : bool
    {
        if ($this->getPathObject() === null) {
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

        if (ilWACSecurePath::hasCheckingInstanceRegistered($this->getPathObject())) {
            // Maybe the path has been registered, lets check
            $checkingInstance = ilWACSecurePath::getCheckingInstance($this->getPathObject());
            $this->addAppliedCheckingMethod(self::CM_CHECKINGINSTANCE);
            $canBeDelivered = $checkingInstance->canBeDelivered($this->getPathObject());
            if ($canBeDelivered) {
                $this->sendHeader('checked using fallback');
                if ($ilWACSignedPath->isFolderSigned() && $this->isRevalidateFolderTokens()) {
                    $ilWACSignedPath->revalidatingFolderToken();
                }
            }
            $this->setChecked(true);
            return $canBeDelivered;
        }

        // none of the checking mechanisms could have been applied. no access
        $this->setChecked(true);
        $this->addAppliedCheckingMethod(self::CM_SECFOLDER);
        return !$this->getPathObject()->isInSecFolder();
    }

    protected function sendHeader(string $message) : void
    {
        $response = $this->http->response()->withHeader('X-ILIAS-WebAccessChecker', $message);
        $this->http->saveResponse($response);
    }

    public function initILIAS() : void
    {
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
            $this->checkUser();
            $this->checkPublicSection();
        } catch (Exception $e) {
            if ($e instanceof ilWACException
                && $e->getCode() !== ilWACException::ACCESS_DENIED_NO_LOGIN) {
                throw  $e;
            }
            if (($e instanceof Exception && $e->getMessage() === 'Authentication failed.')
                || $e->getCode() === ilWACException::ACCESS_DENIED_NO_LOGIN) {
                $this->initAnonymousSession();
                $this->checkUser();
                $this->checkPublicSection();
            }
        }
        $this->setInitialized(true);
    }

    /**
     * @throws ilWACException
     */
    protected function checkPublicSection() : void
    {
        global $DIC;
        $on_login_page = !$this->isRequestNotFromLoginPage();
        $is_anonymous = ($DIC->user()->getId() === ANONYMOUS_USER_ID);
        $is_null_user = ($DIC->user()->getId() === 0);
        $pub_section_activated = (bool) $DIC['ilSetting']->get('pub_section');
        $isset = isset($DIC['ilSetting']);
        $instanceof = $DIC['ilSetting'] instanceof ilSetting;

        if (!$isset || !$instanceof) {
            throw new ilWACException(ilWACException::ACCESS_DENIED_NO_PUB);
        }

        if ($on_login_page && ($is_null_user || $is_anonymous)) {
            // Request is initiated from login page
            return;
        }

        if ($pub_section_activated && ($is_null_user || $is_anonymous)) {
            // Request is initiated from an enabled public area
            return;
        }

        if ($is_anonymous || $is_null_user) {
            throw new ilWACException(ilWACException::ACCESS_DENIED_NO_PUB);
        }
    }

    protected function checkUser() : void
    {
        global $DIC;

        $is_user = $DIC->user() instanceof ilObjUser;
        $user_id_is_zero = ($DIC->user()->getId() === 0);
        $not_on_login_page = $this->isRequestNotFromLoginPage();
        if (!$is_user || ($user_id_is_zero && $not_on_login_page)) {
            throw new ilWACException(ilWACException::ACCESS_DENIED_NO_LOGIN);
        }
    }

    public function isChecked() : bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked) : void
    {
        $this->checked = $checked;
    }

    public function getPathObject() : ?\ilWACPath
    {
        return $this->path_object;
    }

    public function setPathObject(ilWACPath $path_object) : void
    {
        $this->path_object = $path_object;
    }

    public function getDisposition() : string
    {
        return $this->disposition;
    }

    public function setDisposition(string $disposition) : void
    {
        $this->disposition = $disposition;
    }

    public function getOverrideMimetype() : string
    {
        return $this->override_mimetype;
    }

    public function setOverrideMimetype(string $override_mimetype) : void
    {
        $this->override_mimetype = $override_mimetype;
    }

    public function isInitialized() : bool
    {
        return $this->initialized;
    }

    public function setInitialized(bool $initialized) : void
    {
        $this->initialized = $initialized;
    }

    public function isSendStatusCode() : bool
    {
        return $this->send_status_code;
    }

    public function setSendStatusCode(bool $send_status_code) : void
    {
        $this->send_status_code = $send_status_code;
    }

    public function isRevalidateFolderTokens() : bool
    {
        return $this->revalidate_folder_tokens;
    }

    public function setRevalidateFolderTokens(bool $revalidate_folder_tokens) : void
    {
        $this->revalidate_folder_tokens = $revalidate_folder_tokens;
    }

    public static function isUseSeperateLogfile() : bool
    {
        return self::$use_seperate_logfile;
    }

    public static function setUseSeperateLogfile(bool $use_seperate_logfile) : void
    {
        self::$use_seperate_logfile = $use_seperate_logfile;
    }

    /**
     * @return int[]
     */
    public function getAppliedCheckingMethods() : array
    {
        return $this->applied_checking_methods;
    }

    /**
     * @param int[] $applied_checking_methods
     */
    public function setAppliedCheckingMethods(array $applied_checking_methods) : void
    {
        $this->applied_checking_methods = $applied_checking_methods;
    }

    protected function addAppliedCheckingMethod(int $method) : void
    {
        $this->applied_checking_methods[] = $method;
    }

    protected function initAnonymousSession() : void
    {
        global $DIC;
        session_destroy();
        ilContext::init(ilContext::CONTEXT_WAC);
        ilInitialisation::reinitILIAS();
        /**
         * @var $ilAuthSession \ilAuthSession
         */
        $ilAuthSession = $DIC['ilAuthSession'];
        $ilAuthSession->init();
        $ilAuthSession->regenerateId();
        $ilAuthSession->setUserId(ANONYMOUS_USER_ID);
        $ilAuthSession->setAuthenticated(false, ANONYMOUS_USER_ID);
        $DIC->user()->setId(ANONYMOUS_USER_ID);
    }

    protected function isRequestNotFromLoginPage() : bool
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $not_on_login_page = (strpos($referrer, 'login.php') === false
            && strpos($referrer, '&baseClass=ilStartUpGUI') === false);

        if ($not_on_login_page && $referrer !== '') {
            // In some scenarios (observed for content styles on login page, the HTTP_REFERER does not contain a PHP script
            $referrer_url_parts = parse_url($referrer);
            $ilias_url_parts = parse_url(ilUtil::_getHttpPath());
            if (
                $ilias_url_parts['host'] === $referrer_url_parts['host'] &&
                (
                    !isset($referrer_url_parts['path']) ||
                    strpos($referrer_url_parts['path'], '.php') === false
                )
            ) {
                $not_on_login_page = false;
            }
        }

        return $not_on_login_page;
    }
}
