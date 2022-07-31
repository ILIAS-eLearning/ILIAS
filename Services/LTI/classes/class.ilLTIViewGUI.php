<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\LTI\Screen\LtiViewLayoutProvider;

/**
 * @classDescription class for ILIAS ViewLTI
 *
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de
 * @version $id$
 * @ingroup ServicesLTI
 * @ilCtrl_IsCalledBy ilLTIViewGUI: ilLTIRouterGUI
 *
 */
class ilLTIViewGUI
{
    /**
     * contstants
     */
    const CHECK_HTTP_REFERER = true;

    /**
     * private variables
     */
    private ?ILIAS\DI\Container $dic = null;
    private ?int $user = null;
    private ?ilLogger $log = null;
    private string $link_dir = "";

    private ?int $effectiveRefId = null;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    private \ILIAS\Refinery\KindlyTo\Group $kindlyTo;
    private ilLocatorGUI $locator;

    /**
     * public variables
     */
    public ?ilLanguage $lng = null;

    /**
     *
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->log = ilLoggerFactory::getLogger('ltis');
        $this->lng = $this->dic->language();
        $this->lng->loadLanguageModule('lti');
        $this->wrapper = $DIC->http()->wrapper();
        $this->kindlyTo = $DIC->refinery()->kindlyTo();
        $this->locator = $DIC['ilLocator'];
    }

    /**
     * Init LTI mode for lti authenticated users
     */
    public function init() : void
    {
        $this->link_dir = (defined("ILIAS_MODULE")) ? "../" : "";
        if ($this->isLTIUser()) {
            $context = $this->dic->globalScreen()->tool()->context();
            $context->claim()->lti();
            $this->initGUI();
        }
    }

    /**
     * for compatiblity with ilLTIRouterGUI
     * @return mixed
     */
    public static function getInstance()
    {
        global $DIC;
        return $DIC["lti"];
    }

    /**
     * get LTI Mode from Users->getAuthMode
     * @return bool
     */
    private function isLTIUser() : bool
    {
        if (!$this->dic->user() instanceof ilObjUser) {
            return false;
        }
        if ($this->dic->user()->getAuthMode() == null) {
            return false;
        }
        return (strpos($this->dic->user()->getAuthMode(), 'lti_') === 0);
    }

    /**
     * @return void
     */
    public function executeCommand() : void
    {
        global $ilCtrl;
        $cmd = $ilCtrl->getCmd();
        switch ($cmd) {
            case 'exit':
                $this->exitLti();
                break;
        }
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->isLTIUser();
    }

    /**
     * @return void
     */
    public function initGUI() : void
    {
        $this->log->debug("initGUI");
        $baseclass = '';
        if ($this->wrapper->query()->has('baseClass')) {
            $baseclass = strtolower($this->wrapper->query()->retrieve('baseClass', $this->kindlyTo->string()));
        }
        if ($this->wrapper->query()->has('cmdClass')) {
            $cmdclass = strtolower($this->wrapper->query()->retrieve('cmdClass', $this->kindlyTo->string()));
        }
        if ($baseclass == 'illtiroutergui') {
            return;
        }
    }

    /**
    * @return int|null
    */
    protected function getContextId() : ?int
    {
        global $DIC;
        // forced lti_context_id for example request command in exitLTI
        if ($this->wrapper->query()->has('lti_context_id') &&
            $this->wrapper->query()->retrieve('lti_context_id', $this->kindlyTo->string()) !== '') {
            $contextId = (int) $this->wrapper->query()->retrieve('lti_context_id', $this->kindlyTo->int());
            $this->log->debug("find context_id by GET param: " . (string) $contextId);
            return $contextId;
        }
        
        $this->findEffectiveRefId();
        $ref_id = $this->effectiveRefId;

        $this->log->debug("Effective ref_id: " . $ref_id);
        // context_id = ref_id in request
        if (ilSession::has('lti_' . $ref_id . '_post_data')) {
            $this->log->debug("lti context session exists for " . $ref_id);
//            return $ref_id;
        }
        // sub item request
        $this->log->debug("ref_id not exists as context_id, walking tree backwards to find a valid context_id");
        $locator_items = $this->locator->getItems();
        if (is_array($locator_items) && count($locator_items) > 0) {
            for ($i = count($locator_items) - 1;$i >= 0;$i--) {
                if (ilSession::has('lti_' . $locator_items[$i]['ref_id'] . '_post_data')) {
                    $this->log->debug("found valid ref_id in locator: " . $locator_items[$i]['ref_id']);
                    return $locator_items[$i]['ref_id'];
                }
            }
        }
        $this->log->warning("no valid context_id found for ref_id request: " . $ref_id);

        if (ilLTIViewGUI::CHECK_HTTP_REFERER) {
            $ref_id = $this->effectiveRefId;
            $obj_type = ilObject::_lookupType($ref_id, true);
            $context_id = '';
            $referer = 0;

            // first try to get real http referer
            if (isset($_SERVER['HTTP_REFERER'])) {
                $this->findEffectiveRefId($_SERVER['HTTP_REFERER']);
            } else { // only fallback and not reliable on multiple browser LTi contexts
                if (ilSession::has('referer_ref_id')) {
                    $this->effectiveRefId = ilSession::get('referer_ref_id');
                }
            }

            $referrer = $this->effectiveRefId;

            if ($referer > 0) {
                if (ilSession::has('lti_' . $referer . '_post_data')) {
                    $ref_id = $referer;
                    $context_id = $referer;
                    $obj_type = ilObject::_lookupType($ref_id, true);
                    $this->log->debug("referer obj_type: " . $obj_type);
                } else {
                    $this->log->debug("search tree of referer...");
                    if ($this->dic->repositoryTree()->isInTree($referer)) {
                        $path = $this->dic->repositoryTree()->getPathId($referer);
                        for ($i = count($path) - 1;$i >= 0;$i--) {
                            if (ilSession::has('lti_' . $path[$i] . '_post_data')) {
                                // redirect to referer, because it is valid
                                $ref_id = $referer;
                                $context_id = $path[$i];
                                $obj_type = ilObject::_lookupType($ref_id, true);
                                break;
                            }
                        }
                    }
                }
            }
            if ($ref_id > 0 && $obj_type != '') {
                if (
                    (
                        $this->wrapper->query()->has('baseClass') &&
                        $this->wrapper->query()->retrieve('baseClass', $this->kindlyTo->string()) === 'ilDashboardGUI'
                    )
                    &&
                    (
                        $this->wrapper->query()->has('cmdClass') &&
                        $this->wrapper->query()->retrieve('cmdClass', $this->kindlyTo->string()) === 'ilpersonalprofilegui'
                    )
                ) {
                    return $context_id;
                }
//                $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                $redirect = $this->link_dir . "goto.php?target=" . $obj_type . "_" . $ref_id . "&lti_context_id=" . $context_id;
                $this->log->debug("redirect: " . $redirect);
                ilUtil::redirect($redirect);
            }
        }
        $lti_context_ids = ilSession::get('lti_context_ids');
        if (is_array($lti_context_ids) && count($lti_context_ids) > 0) {
            if (count($lti_context_ids) == 1) {
                $this->log->debug("using context_id from only LTI session");
                return $lti_context_ids[0];
            } else {
                $this->log->warning("Multiple LTI sessions exists. The context_id can not be clearly detected");
            }
        }
        return null;
    }

    /**
     * @return array|null
     */
    public function getPostData() : ?array
    {
        $context_id = $this->getContextId();
        if ($context_id == 0) {
            $this->log->warning("could not find any valid context_id!");
            return null;
        }
        $post_data = ilSession::get('lti_' . $context_id . '_post_data');
        if (!is_array($post_data)) {
            $this->log->warning("no session post_data: " . "lti_" . $context_id . "_post_data");
            return null;
        }
        return $post_data;
    }

    /**
     * @return string
     */
    public function getExternalCss() : string
    {
        $post_data = $this->getPostData();
        if ($post_data !== null) {
            return (isset($post_data['launch_presentation_css_url'])) ? $post_data['launch_presentation_css_url'] : '';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        $post_data = $this->getPostData();
        if ($post_data !== null) {
            return (isset($post_data['resource_link_title'])) ? "LTI - " . $post_data['resource_link_title'] : "LTI";
        }
        return "LTI";
    }

    /**
     * @return string
     */
    public function getTitleForExitPage() : string
    {
        return $this->lng->txt('lti_exited');
    }

    /**
     * @return string
     */
    public function getShortTitle() : string
    {
        return $this->lng->txt('lti_mode');
    }
    
    /**
     * exit LTI session and if defined redirecting to returnUrl
     * ToDo: Standard Template with delos ...
     */
    public function exitLti() : void
    {
        $logger = ilLoggerFactory::getLogger('ltis');
        $logger->info("exitLTI");
        $force_ilias_logout = false;
        $context_id = $this->getContextId();
        if ($context_id == 0) {
            $this->log->warning("could not find any valid context_id!");
            $force_ilias_logout = true;
        }
        $post_data = $this->getPostData();
        //$return_url = ($post_data !== null) ? $post_data['launch_presentation_return_url'] : '';
        $return_url = '';
        if (isset($post_data['launch_presentation_return_url'])) {
            $return_url = $post_data['launch_presentation_return_url'];
        }
        $this->removeContextFromSession((string) $context_id);

        if (ilSession::has('lti_' . $context_id . '_post_data')) {
            ilSession::clear('lti_' . $context_id . '_post_data');
            $logger->debug('unset SESSION["' . 'lti_' . $context_id . '_post_data"]');
        }

        if (!isset($return_url) || $return_url === '') {
            $cc = $this->dic->globalScreen()->tool()->context()->current();
            $cc->addAdditionalData(LtiViewLayoutProvider::GS_EXIT_LTI, true);
            $ui_factory = $this->dic->ui()->factory();
            $renderer = $this->dic->ui()->renderer();
            $content = [
                $ui_factory->messageBox()->info($this->lng->txt('lti_exited_info'))
            ];
            $tpl = $this->dic["tpl"];
            $tpl->setContent($renderer->render($content));
            $this->logout($force_ilias_logout);
            $tpl->printToStdout();
        } else {
            $this->logout($force_ilias_logout);
            header('Location: ' . $return_url);
        }
    }

    /**
     * logout ILIAS and destroys Session and ilClientId cookie if no consumer is still open in the LTI User Session
     */
    public function logout(bool $force_ilias_logout = false) : void
    {
        if ($force_ilias_logout) {
            $this->log->warning("forcing logout ilias session, maybe a broken LTI context");
        } else {
            if (is_array(ilSession::get('lti_context_ids')) && count(ilSession::get('lti_context_ids')) > 0) {
                $this->log->debug("there is another valid consumer session: ilias session logout refused.");
                return;
            }
        }
        $this->log->info("logout");
        $this->dic->user()->setAuthMode((string) ilAuthUtils::AUTH_LOCAL);
        //ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER); // needed?
        $auth = $this->dic['ilAuthSession'];
        //$auth->logout(); // needed?
//        $auth->setExpired($auth::SESSION_AUTH_EXPIRED, ilAuthStatus::STATUS_UNDEFINED);
        $auth->setExpired(true);
        session_destroy();
        ilUtil::setCookie("ilClientId", "");
        ilUtil::setCookie("PHPSESSID", "");
    }

    /**
     * @param String $cmd
     * @return String
     * @throws ilCtrlException
     */
    public function getCmdLink(String $cmd) : String
    {
        global $ilCtrl;
        $lti_context_id = $this->getContextId();
        $lti_context_id_param = ($lti_context_id != '') ? "&lti_context_id=" . $lti_context_id : '';
        $targetScript = "";
        return $this->link_dir . $targetScript . $this->dic->ctrl()->getLinkTargetByClass(array('illtiroutergui',strtolower(get_class($this))), $cmd) . "&baseClass=illtiroutergui" . $lti_context_id_param;
    }

    private function getSessionValue(string $sess_key) : string
    {
        if (ilSession::has($sess_key) && ilSession::get($sess_key) != '') {
            return ilSession::get($sess_key);
        } else {
            return '';
        }
    }

    private function getCookieValue(string $cookie_key) : string
    {
        if ($this->dic->wrapper->cookie()->has($cookie_key) && $this->dic->wrapper->cookie()->retrieve($cookie_key, $this->dic->refinery()->kindlyTo()->string() != '')) {
            return $this->dic->wrapper->cookie()->retrieve($cookie_key, $this->dic->refinery()->kindlyTo()->string());
        } else {
            return '';
        }
    }

    private function removeContextFromSession(string $context_id) : void
    {
        $lti_context_ids = ilSession::get('lti_context_ids');
        if (is_array($lti_context_ids) && in_array($context_id, $lti_context_ids)) {
            array_splice($lti_context_ids, array_search($context_id, $lti_context_ids), 1);
            ilSession::set('lti_context_ids', $lti_context_ids);
        }
    }

    /**
     * Find effective ref_id for request
     * @param string|null $url
     */
    private function findEffectiveRefId(?string $url = null) : void
    {
        $query = [];
        if ($url === null) {
            if ($this->wrapper->query()->has('ref_id')) {
                $query['ref_id'] = $this->wrapper->query()->retrieve('ref_id', $this->kindlyTo->string());
            }
            if ($this->wrapper->query()->has('target')) {
                $query['target'] = $this->wrapper->query()->retrieve('target', $this->kindlyTo->string());
            }
        } else {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        }
        if (isset($query['ref_id']) && (int) $query['ref_id']) {
            $this->effectiveRefId = (int) $query['ref_id'];
            return;
        }
        if (ilSession::has('lti_init_target') && ilSession::get('lti_init_target') != "") {
            $target_arr = explode('_', ilSession::get('lti_init_target'));
            ilSession::set('lti_init_target', "");
        } else {
            if (isset($query['target'])) {
                $target_arr = explode('_', (string) $query['target']);
            }
        }
        if (isset($target_arr[1]) and (int) $target_arr[1]) {
            $this->effectiveRefId = (int) $target_arr[1];
        }
    }
}
