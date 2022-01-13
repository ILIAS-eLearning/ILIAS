<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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
    private $dic = null;
    private $user = null;
    private $log = null;
    private $link_dir = "";

    /**
     * public variables
     */
    public $lng = null;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->log = $this->dic->logger()->lti();
        $this->lng = $this->dic->language();
        $this->lng->loadLanguageModule('lti');
    }

    /**
     * Init LTI mode for lti authenticated users
     */
    public function init()
    {
        $this->link_dir = (defined("ILIAS_MODULE")) ? "../" : "";
		if ($this->isLTIUser())
		{
			$context = $this->dic->globalScreen()->tool()->context();
			$context->claim()->lti();
			$this->initGUI();
        }
    }

    /**
     * for compatiblity with ilLTIRouterGUI
     */
    public static function getInstance()
    {
        global $DIC;
        return $DIC["lti"];
    }

    /**
     * get LTI Mode from Users->getAuthMode
     * @return boolean
     */
    private function isLTIUser()
    {
        if (!$this->dic->user() instanceof ilObjUser) {
            return false;
        }
        return (strpos($this->dic->user()->getAuthMode(), 'lti_') === 0);
    }

    public function executeCommand()
    {
        global $ilCtrl;
        $cmd = $ilCtrl->getCmd();
        switch ($cmd) {
            case 'exit':
                $this->exitLti();
                break;
        }
    }

    public function isActive() : bool
    {
		return $this->isLTIUser();
    }

    public function initGUI()
    {
        $this->log->debug("initGUI");
        $baseclass = strtolower($_GET['baseClass']);
        $cmdclass = strtolower($_GET['cmdClass']);
		switch ($baseclass)
		{
			case 'illtiroutergui' :
				return;
				break;
		}
	}

	public function getContextId() {
        global $ilLocator;

        // forced lti_context_id for example request command in exitLTI
        if (isset($_GET['lti_context_id']) && $_GET['lti_context_id'] !== '') {
            $this->log->debug("find context_id by GET param: " . $_GET['lti_context_id']);
			return $_GET['lti_context_id'];
        }
        
        $ref_id = $this->findEffectiveRefId();
        $this->log->debug("Effective ref_id: ". $ref_id);
        // context_id = ref_id in request
        if (isset($_SESSION['lti_' . $ref_id . '_post_data'])) {
            $this->log->debug("lti context session exists for " . $ref_id);
            return $ref_id;
        }

        // sub item request
        $this->log->debug("ref_id not exists as context_id, walking tree backwards to find a valid context_id");
        $locator_items = $ilLocator->getItems();
		if (is_array($locator_items) && count($locator_items) > 0) {
            for ($i = count($locator_items)-1;$i>=0;$i--) {
                if (isset($_SESSION['lti_' . $locator_items[$i]['ref_id'] . '_post_data'])) {
                    $this->log->debug("found valid ref_id in locator: " . $locator_items[$i]['ref_id']);
                    return $locator_items[$i]['ref_id'];
                }
            }
        }
        $this->log->warning("no valid context_id found for ref_id request: " . $ref_id);

        if (ilLTIViewGUI::CHECK_HTTP_REFERER) {
            $ref_id = '';
            $obj_type = '';
            $context_id = '';
            $referer = '';

            // first try to get real http referer
            if (isset($_SERVER['HTTP_REFERER'])) {
                $referer = $this->findEffectiveRefId($_SERVER['HTTP_REFERER']);
            }
            else { // only fallback and not reliable on multiple browser LTi contexts
                if (isset($_SESSION['referer_ref_id'])) {
                    $referer = $_SESSION['referer_ref_id'];
                }
            }

            if ($referer != '') {
                if (isset($_SESSION['lti_' . $referer . '_post_data'])) {
                    $ref_id =$referer;
                    $context_id = $referer;
                    $obj_type = ilObject::_lookupType($ref_id,true);
                    $this->log->debug("referer obj_type: " . $obj_type);
                }
                else {
                    $this->log->debug("search tree of referer...");
                    if ($this->dic->repositoryTree()->isInTree($referer)) {
                        $path = $this->dic->repositoryTree()->getPathId($referer);
                        for ($i = count($path)-1;$i>=0;$i--) {
                            if (isset($_SESSION['lti_' . $path[$i] . '_post_data'])) {
                                // redirect to referer, because it is valid
                                $ref_id = $referer;
                                $context_id = $path[$i];
                                $obj_type = ilObject::_lookupType($ref_id,true); 
                                break;
                            }
                        }
                    }
                }
            }
            if ($ref_id != '' && $obj_type != '') {
                if ((isset($_GET['baseClass']) && $_GET['baseClass'] === 'ilDashboardGUI')
                    && (isset($_GET['cmdClass']) && $_GET['cmdClass'] === 'ilpersonalprofilegui')) {
                    return $context_id;
                }
                ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
                $redirect = $this->link_dir."goto.php?target=".$obj_type."_".$ref_id."&lti_context_id=".$context_id;
                $this->log->debug("redirect: " . $redirect);
                ilUtil::redirect($redirect);
            }
        }
        $lti_context_ids = $_SESSION['lti_context_ids'];
        if (is_array($lti_context_ids) && count($lti_context_ids) > 0) {
            if (count($lti_context_ids) == 1) {
                $this->log->debug("using context_id from only LTI session");
                return $lti_context_ids[0];
            }
            else {
                $this->log->warning("Multiple LTI sessions exists. The context_id can not be clearly detected");
            }
        }
		return '';
    }

	public function getPostData() {
        $context_id = $this->getContextId();
        if ($context_id == '') {
            $this->log->warning("could not find any valid context_id!");
            return null;
        }
		$post_data = $_SESSION['lti_' . $this->getContextId() . '_post_data'];
		if (!is_array($post_data)) {
			$this->log->warning("no session post_data: " . "lti_" . $this->getContextId() . "_post_data");
			return null;
        }
		return $post_data;
    }

	public function getExternalCss() {
		$post_data = $this->getPostData();
		if ($post_data !== null) {
			return (isset($post_data['launch_presentation_css_url'])) ? $post_data['launch_presentation_css_url'] : '';
        }
		return '';
    }

    public function getTitle() : string
    {
		$post_data = $this->getPostData();
		if ($post_data !== null) {
			return (isset($post_data['resource_link_title'])) ? "LTI - " . $post_data['resource_link_title'] : "LTI";
		}
		return "LTI";
    }

    public function getTitleForExitPage() : string
    {
        return $this->lng->txt('lti_exited');
    }

    public function getShortTitle() : string
    {
        return $this->lng->txt('lti_mode');
    }
    
    /**
     * exit LTI session and if defined redirecting to returnUrl
     * ToDo: Standard Template with delos ...
     */
    public function exitLti()
    {
        $this->dic->logger()->lti()->info("exitLTI");
        $force_ilias_logout = false;
        $context_id = $this->getContextId();
        if ($context_id == '') {
            $this->log->warning("could not find any valid context_id!");
            $force_ilias_logout = true;
        }
		$post_data = $this->getPostData();
		$return_url = ($post_data !== null) ? $post_data['launch_presentation_return_url'] : '';
        $this->removeContextFromSession($context_id);
       
		if (isset($_SESSION['lti_' . $context_id . '_post_data'])) {
			unset($_SESSION['lti_' . $context_id . '_post_data']);
			$this->dic->logger()->lti()->debug('unset SESSION["' . 'lti_' . $context_id . '_post_data"]');
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
    public function logout($force_ilias_logout=false)
    {
        if ($force_ilias_logout) {
            $this->log->warning("forcing logout ilias session, maybe a broken LTI context");
        }
        else {
            if (is_array($_SESSION['lti_context_ids']) && count($_SESSION['lti_context_ids']) > 0) {
			    $this->log->debug("there is another valid consumer session: ilias session logout refused.");
                return;
            }
        }
        $this->dic->logger()->lti()->info("logout");
        $GLOBALS['DIC']->user()->setAuthMode(AUTH_LOCAL);
        //ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER); // needed?
        $auth = $GLOBALS['DIC']['ilAuthSession'];
        //$auth->logout(); // needed?
        $auth->setExpired($auth::SESSION_AUTH_EXPIRED,ilAuthStatus::STATUS_UNDEFINED);
        session_destroy();
        $client_id = $_COOKIE["ilClientId"];
        ilUtil::setCookie("ilClientId", "");
		ilUtil::setCookie("PHPSESSID","");
    }

    public function getCmdLink(String $cmd) : String
    {
        global $ilCtrl;
		$lti_context_id = $this->getContextId();
		$lti_context_id_param = ($lti_context_id  != '') ? "&lti_context_id=".$lti_context_id : '';
        $targetScript = ($ilCtrl->getTargetScript() !== 'ilias.php') ? "ilias.php" : "";
		return $this->link_dir.$targetScript.$ilCtrl->getLinkTargetByClass(array('illtiroutergui',strtolower(get_class($this))),$cmd)."&baseClass=illtiroutergui".$lti_context_id_param;
    }

    private function getSessionValue(String $sess_key) : String
    {
        if (isset($_SESSION[$sess_key]) && $_SESSION[$sess_key] != '') {
            return $_SESSION[$sess_key];
        } else {
            return '';
        }
    }

	private function getCookieValue(String $cookie_key) : String
	{
		if (isset($_COOKIE[$cookie_key]) && $_COOKIE[$cookie_key] != '') {
			return $_COOKIE[$cookie_key];
		}
		else {
			return '';
		}
	}

	private function removeContextFromSession($context_id) {
		$lti_context_ids = $_SESSION['lti_context_ids'];
		if (is_array($lti_context_ids) && in_array($context_id,$lti_context_ids)) {
			array_splice($lti_context_ids,array_search($context_id,$lti_context_ids),1);
			$_SESSION['lti_context_ids'] = $lti_context_ids;
		}
    }
    
    /**
     * Find effective ref_id for request
     */
    private function findEffectiveRefId($url=null)
    {
        if ($url === null) {
            $query = $_GET;
        }
        else {
            parse_str(parse_url($url, PHP_URL_QUERY),$query);
        } 
        if ((int) $query['ref_id']) {
            return (int) $query['ref_id'];
        }
        $target_arr = explode('_', (string) $query['target']);
        if (isset($target_arr[1]) and (int) $target_arr[1]) {
            return (int) $target_arr[1];
        }
        return '';
    }
}
