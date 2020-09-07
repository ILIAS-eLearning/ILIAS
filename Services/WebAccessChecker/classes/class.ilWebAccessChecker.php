<?php
// declare(strict_types=1);

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
class ilWebAccessChecker
{
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
     * @param GlobalHttpState            $httpState
     * @param CookieFactory              $cookieFactory
     */
    public function __construct(GlobalHttpState $httpState, CookieFactory $cookieFactory)
    {
        $this->setPathObject(new ilWACPath($httpState->request()->getRequestTarget()));
        $this->http = $httpState;
        $this->cookieFactory = $cookieFactory;
    }


    /**
     * @return bool
     * @throws ilWACException
     */
    public function check()
    {
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
     *
     * @return void
     */
    protected function sendHeader($message)
    {
        $response = $this->http->response()->withHeader('X-ILIAS-WebAccessChecker', $message);
        $this->http->saveResponse($response);
    }


    /**
     * @return void
     */
    public function initILIAS()
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
            if (($e instanceof Exception && $e->getMessage() == 'Authentication failed.')
                || $e->getCode() === ilWACException::ACCESS_DENIED_NO_LOGIN) {
                $this->initAnonymousSession();
                $this->checkUser();
                $this->checkPublicSection();
            }
        }
        $this->setInitialized(true);
    }


    /**
     * @return void
     * @throws ilWACException
     */
    protected function checkPublicSection()
    {
        global $DIC;
        $not_on_login_page = $this->isRequestNotFromLoginPage();
        $is_anonymous = ((int) $DIC->user()->getId() === (int) ANONYMOUS_USER_ID);
        $is_null_user = ($DIC->user()->getId() === 0);
        $pub_section_activated = (bool) $DIC['ilSetting']->get('pub_section');
        $isset = isset($DIC['ilSetting']);
        $instanceof = $DIC['ilSetting'] instanceof ilSetting;
        if (!$isset || !$instanceof || (!$pub_section_activated && ($is_anonymous || ($is_null_user && $not_on_login_page)))) {
            throw new ilWACException(ilWACException::ACCESS_DENIED_NO_PUB);
        }
    }


    protected function checkUser()
    {
        global $DIC;

        $is_user = $DIC->user() instanceof ilObjUser;
        $user_id_is_zero = ((int) $DIC->user()->getId() === 0);
        $not_on_login_page = $this->isRequestNotFromLoginPage();
        if (!$is_user || ($user_id_is_zero && $not_on_login_page)) {
            throw new ilWACException(ilWACException::ACCESS_DENIED_NO_LOGIN);
        }
    }


    /**
     * @return bool
     */
    public function isChecked()
    {
        return (bool) $this->checked;
    }


    /**
     * @param boolean $checked
     *
     * @return void
     */
    public function setChecked($checked)
    {
        assert(is_bool($checked));
        $this->checked = $checked;
    }


    /**
     * @return ilWACPath
     */
    public function getPathObject()
    {
        return $this->path_object;
    }


    /**
     * @param ilWACPath $path_object
     *
     * @return void
     */
    public function setPathObject(ilWACPath $path_object)
    {
        $this->path_object = $path_object;
    }


    /**
     * @return string
     */
    public function getDisposition()
    {
        return (string) $this->disposition;
    }


    /**
     * @param string $disposition
     *
     * @return void
     */
    public function setDisposition($disposition)
    {
        assert(is_string($disposition));
        $this->disposition = $disposition;
    }


    /**
     * @return string
     */
    public function getOverrideMimetype()
    {
        return (string) $this->override_mimetype;
    }


    /**
     * @param string $override_mimetype
     *
     * @return void
     */
    public function setOverrideMimetype($override_mimetype)
    {
        assert(is_string($override_mimetype));
        $this->override_mimetype = $override_mimetype;
    }


    /**
     * @return bool
     */
    public function isInitialized()
    {
        return (bool) $this->initialized;
    }


    /**
     * @param bool $initialized
     */
    public function setInitialized($initialized)
    {
        assert(is_bool($initialized));
        $this->initialized = $initialized;
    }


    /**
     * @return bool
     */
    public function isSendStatusCode()
    {
        return (bool) $this->send_status_code;
    }


    /**
     * @param bool $send_status_code
     *
     * @return void
     */
    public function setSendStatusCode($send_status_code)
    {
        assert(is_bool($send_status_code));
        $this->send_status_code = $send_status_code;
    }


    /**
     * @return bool
     */
    public function isRevalidateFolderTokens()
    {
        return (bool) $this->revalidate_folder_tokens;
    }


    /**
     * @param bool $revalidate_folder_tokens
     *
     * @return void
     */
    public function setRevalidateFolderTokens($revalidate_folder_tokens)
    {
        assert(is_bool($revalidate_folder_tokens));
        $this->revalidate_folder_tokens = $revalidate_folder_tokens;
    }


    /**
     * @return bool
     */
    public static function isUseSeperateLogfile()
    {
        return (bool) self::$use_seperate_logfile;
    }


    /**
     * @param bool $use_seperate_logfile
     *
     * @return void
     */
    public static function setUseSeperateLogfile($use_seperate_logfile)
    {
        assert(is_bool($use_seperate_logfile));
        self::$use_seperate_logfile = $use_seperate_logfile;
    }


    /**
     * @return int[]
     */
    public function getAppliedCheckingMethods()
    {
        return (array) $this->applied_checking_methods;
    }


    /**
     * @param int[] $applied_checking_methods
     *
     * @return void
     */
    public function setAppliedCheckingMethods(array $applied_checking_methods)
    {
        $this->applied_checking_methods = $applied_checking_methods;
    }


    /**
     * @param int $method
     *
     * @return void
     */
    protected function addAppliedCheckingMethod($method)
    {
        assert(is_int($method));
        $this->applied_checking_methods[] = $method;
    }


    protected function initAnonymousSession()
    {
        global $DIC;
        include_once './Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_WAC);
        require_once("Services/Init/classes/class.ilInitialisation.php");
        ilInitialisation::reinitILIAS();
        /**
         * @var $ilAuthSession \ilAuthSession
         */
        $ilAuthSession = $DIC['ilAuthSession'];
        $ilAuthSession->init();
        $ilAuthSession->regenerateId();
        $a_id = (int) ANONYMOUS_USER_ID;
        $ilAuthSession->setUserId($a_id);
        $ilAuthSession->setAuthenticated(false, $a_id);
        $DIC->user()->setId($a_id);
    }


    /**
     * @return bool
     */
    protected function isRequestNotFromLoginPage()
    {
        $referrer = !is_null($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $not_on_login_page = (strpos($referrer, 'login.php') === false
                              && strpos($referrer, '&baseClass=ilStartUpGUI') === false);

        return $not_on_login_page;
    }
}
