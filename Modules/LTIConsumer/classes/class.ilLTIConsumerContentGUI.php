<?php declare(strict_types=1);

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
 * Class ilLTIConsumerContentGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.com>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerContentGUI
{
    const CMD_LAUNCH = 'launch';
    const CMD_SHOW_EMBEDDED = 'showEmbedded';

    protected ilObjLTIConsumer $object;

    protected ilCmiXapiUser $cmixUser;

    private \ILIAS\DI\Container $dic;

    private ilLanguage $lng;

    private ilObjUser $user;

    public function __construct(ilObjLTIConsumer $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->object = $object;
    }

    /**
     * @throws ilLtiConsumerException
     */
    public function executeCommand() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            throw new ilLtiConsumerException('access denied!');
        }

        $command = $DIC->ctrl()->getCmd(self::CMD_LAUNCH);

        $this->{$command}();
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    protected function launch() : void
    {
        if ($this->object->getProvider()->getLtiVersion() == "LTI-1p0") {
            if ($this->object->isLaunchMethodEmbedded()) {
                $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'Modules/LTIConsumer');
                $tpl->setVariable("EMBEDDED_IFRAME_SRC", $this->dic->ctrl()->getLinkTarget(
                    $this,
                    self::CMD_SHOW_EMBEDDED
                ));
                $this->dic->ui()->mainTemplate()->setContent($tpl->get());
            } else {
                $this->dic->toolbar()->addText($this->getStartButtonTxt11());
            }
        } else {
            if ($this->object->isLaunchMethodEmbedded() && (ilSession::get('lti13_login_data') == null)) {
                $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'Modules/LTIConsumer');
                $tpl->setVariable("EMBEDDED_IFRAME_SRC", $this->dic->ctrl()->getLinkTarget(
                    $this,
                    self::CMD_SHOW_EMBEDDED
                ));
                $this->dic->ui()->mainTemplate()->setContent($tpl->get());
            } else {
                if (ilSession::get('lti13_login_data') != null) {
                    $form = $this->getLoginLTI13Form();
                    if ($form == null) {
//                        $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', 'initialLogin Error: ' . $err, true);
                        $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', 'initialLogin Error: ', true);
                    } else {
                        $response = $this->dic->http()->response()->withBody(ILIAS\Filesystem\Stream\Streams::ofString($form));
                        $this->dic->http()->saveResponse($response);
                        $this->dic->http()->sendResponse();
                        $this->dic->http()->close();
                    }
                } else {
                    $this->dic->toolbar()->addText($this->getStartButtonTxt13());
                }
            }
        }
    }

    protected function getLoginLTI13Form() : ?string
    {
        $loginData = ilSession::get('lti13_login_data');
        ilSession::clear('lti13_login_data');
        $err = $this->validateLTI13InitalLogin($loginData);
        if ($err !== null) {
            return null;
        } else {
            $this->initCmixUser();
            $params = $this->getLaunchParametersLTI13($loginData['redirect_uri'], $this->object->getProvider()->getClientId(), $this->object->getProvider()->getId(), $loginData['nonce']);
            if (isset($loginData['state'])) {
                $params['state'] = $loginData['state'];
            }
            ilSession::clear('lti_message_hint');
            $r = '<form action="' . $loginData['redirect_uri'] . "\" name=\"ltiAuthForm\" id=\"ltiAuthForm\" " .
                "method=\"post\" enctype=\"application/x-www-form-urlencoded\">\n";
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $key = htmlspecialchars($key);
                    $value = htmlspecialchars($value);
                    $r .= "  <input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>\n";
                }
            }
            $r .= "</form>\n";
            $r .= "<script type=\"text/javascript\">\n" .
                "//<![CDATA[\n" .
                "document.ltiAuthForm.submit();\n" .
                "//]]>\n" .
                "</script>\n";
            return $r;
        }
        return null;
    }

    protected function getStartButtonTxt11() : string
    {
        if ($this->object->getOfflineStatus() ||
            $this->object->isLaunchMethodEmbedded() ||
            $this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            return "";
        }

        $cmixUser = new ilCmiXapiUser(
            $this->object->getId(),
            $this->user->getId(),
            $this->object->getProvider()->getPrivacyIdent()
        );
        $user_ident = $cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($this->object->getProvider()->getPrivacyIdent(), $this->dic->user());
            $cmixUser->setUsrIdent($user_ident);
            $cmixUser->save();
        }
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $context = $ilLTIConsumerLaunch->getContext();
        $contextType = $ilLTIConsumerLaunch::getLTIContextType($context["type"]);
        $contextId = $context["id"];
        $contextTitle = $context["title"];

        $token = ilCmiXapiAuthToken::fillToken(
            $this->dic->user()->getId(),
            $this->object->getRefId(),
            $this->object->getId()
        );

        $returnUrl = !$this->object->isLaunchMethodOwnWin() ? '' : str_replace(
            '&amp;',
            '&',
            ILIAS_HTTP_PATH . "/" . $this->dic->ctrl()->getLinkTarget($this, "", "", false)
        );

        $launchParameters = $this->object->buildLaunchParameters(
            $cmixUser,
            $token,
            $contextType,
            $contextId,
            $contextTitle,
            $returnUrl
        );

        $target = $this->object->getLaunchMethod() == "newWin" ? "_blank" : "_self";
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $this->lng->txt("launch") . '" />';
        $output = '';

        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $this->object->getProvider()->getProviderUrl() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        foreach ($launchParameters as $field => $value) {
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', $field, $value) . "\n";
        }
        $output .= $button;
        $output .= '</form>';
        $output .= '<span id ="lti_launched" style="display:none">' . $this->lng->txt("launched") . '</span>';
        $output .= '<script type="text/javascript">
        function ltilaunch() {
            document.lti_launch_form.submit();
            document.getElementById("lti_launch_form").style.display = "none";
            document.getElementById("lti_launched").style.display = "inline";
        }</script>';
        return($output);
    }

    protected function getStartButtonTxt13() : string
    {
        if ($this->object->getOfflineStatus() ||
            $this->object->isLaunchMethodEmbedded() ||
            $this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            return "";
        }
        $this->initCmixUser();
        $user_ident = $this->cmixUser->getUsrIdent();
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $context = $ilLTIConsumerLaunch->getContext();
        $contextType = $ilLTIConsumerLaunch::getLTIContextType($context["type"]);
        $contextId = $context["id"];
        $contextTitle = $context["title"];

        $token = ilCmiXapiAuthToken::fillToken(
            $this->dic->user()->getId(),
            $this->object->getRefId(),
            $this->object->getId()
        );

        $returnUrl = !$this->object->isLaunchMethodOwnWin() ? '' : str_replace(
            '&amp;',
            '&',
            ILIAS_HTTP_PATH . "/" . $this->dic->ctrl()->getLinkTarget($this, "", "", false)
        );

        $target = $this->object->getLaunchMethod() == "newWin" ? "_blank" : "_self";
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $this->lng->txt("launch") . '" />';
        $output = '';
        $ltiMessageHint = (string) $this->object->getRefId() . ":" . CLIENT_ID;
        ilSession::set('lti_message_hint', $ltiMessageHint);
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $this->object->getProvider()->getInitiateLogin() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'iss', ILIAS_HTTP_PATH) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'target_link_uri', $this->object->getProvider()->getProviderUrl()) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'login_hint', $user_ident) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_message_hint', $ltiMessageHint) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'client_id', $this->object->getProvider()->getClientId()) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_deployment_id', $this->object->getProvider()->getId()) . "\n";
        $output .= $button;
        $output .= '</form>';
        $output .= '<span id ="lti_launched" style="display:none">' . $this->lng->txt("launched") . '</span>';
        $output .= '<script type="text/javascript">
        function ltilaunch() {
            document.lti_launch_form.submit();
            document.getElementById("lti_launch_form").style.display = "none";
            document.getElementById("lti_launched").style.display = "inline";
        }</script>';
        return($output);
    }

    // TODO: merge with getStartButtonTxt13 (paramter)
    protected function getEmbeddedAutoStartFormular() : string
    {
        $this->initCmixUser();
        $user_ident = $this->cmixUser->getUsrIdent();
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $context = $ilLTIConsumerLaunch->getContext();
        $contextType = $ilLTIConsumerLaunch::getLTIContextType($context["type"]);
        $contextId = $context["id"];
        $contextTitle = $context["title"];

        $target = "_self";
        $output = '';
        $ltiMessageHint = (string) $this->object->getRefId() . ":" . CLIENT_ID;
        ilSession::set('lti_message_hint', $ltiMessageHint);
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $this->object->getProvider()->getInitiateLogin() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'iss', ILIAS_HTTP_PATH) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'target_link_uri', $this->object->getProvider()->getProviderUrl()) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'login_hint', $user_ident) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_message_hint', $ltiMessageHint) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'client_id', $this->object->getProvider()->getClientId()) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_deployment_id', $this->object->getProvider()->getId()) . "\n";
        $output .= '</form>';

        $output .= "<script type=\"text/javascript\">\n" .
                "//<![CDATA[\n" .
                "document.lti_launch_form.submit();\n" .
                "//]]>\n" .
                "</script>\n";

        return($output);
    }


    /**
     * @throws ilTemplateException
     */
    protected function showEmbedded() : void
    {
        if ($this->object->getProvider()->getLtiVersion() == "LTI-1p0") {
            $this->initCmixUser();
            $tpl = new ilTemplate('tpl.lti_embedded.html', true, true, 'Modules/LTIConsumer');
            foreach ($this->getLaunchParameters() as $field => $value) {
                $tpl->setCurrentBlock('launch_parameter');
                $tpl->setVariable('LAUNCH_PARAMETER', $field);
                $tpl->setVariable('LAUNCH_PARAM_VALUE', $value);
                $tpl->parseCurrentBlock();
            }

            $v = DEVMODE ? '?vers=' . time() : '?vers=' . ILIAS_VERSION_NUMERIC;
            $tpl->setVariable("DELOS_CSS_HREF", 'templates/default/delos.css' . $v);
            $tpl->setVariable("JQUERY_SRC", 'libs/bower/bower_components/jquery/dist/jquery.js' . $v);

            $tpl->setVariable("LOADER_ICON_SRC", ilUtil::getImagePath("loader.svg"));
            $tpl->setVariable('LAUNCH_URL', $this->object->getProvider()->getProviderUrl());

            #$DIC->ui()->mainTemplate()->getStandardTemplate();
            #$DIC->ui()->mainTemplate()->setContent($tpl->get());

            echo $tpl->get();
            exit; //TODO: no exit
        } else {
            $response = $this->dic->http()->response()->withBody(ILIAS\Filesystem\Stream\Streams::ofString($this->getEmbeddedAutoStartFormular()));
            $this->dic->http()->saveResponse($response);
            $this->dic->http()->sendResponse();
            $this->dic->http()->close();
        }
    }

    protected function getLaunchParameters() : array
    {
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $launchContext = $ilLTIConsumerLaunch->getContext();

        $launchContextType = ilLTIConsumerLaunch::getLTIContextType($launchContext["type"]);
        $launchContextId = $launchContext["id"];
        $launchContextTitle = $launchContext["title"];

        $token = ilCmiXapiAuthToken::fillToken(
            $this->dic->user()->getId(),
            $this->object->getRefId(),
            $this->object->getId()
        );

        return $this->object->buildLaunchParameters(
            $this->cmixUser,
            $token,
            $launchContextType,
            $launchContextId,
            $launchContextTitle
        );
    }

    protected function getLaunchParametersLTI13(string $endpoint, string $clientId, int $deploymentId, string $nonce) : array
    {
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $launchContext = $ilLTIConsumerLaunch->getContext();

        $launchContextType = ilLTIConsumerLaunch::getLTIContextType($launchContext["type"]);
        $launchContextId = $launchContext["id"];
        $launchContextTitle = $launchContext["title"];

        $cmixUser = $this->cmixUser;
        return $this->object->buildLaunchParametersLTI13(
            $cmixUser,
            $endpoint,
            $clientId,
            $deploymentId,
            $nonce,
            $launchContextType,
            $launchContextId,
            $launchContextTitle
        );
    }

    public static function isEmbeddedLaunchRequest() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($DIC->ctrl()->getNextClass() != strtolower(self::class)) {
            return false;
        }

        if ($DIC->ctrl()->getCmd() != self::CMD_SHOW_EMBEDDED) {
            return false;
        }

        return true;
    }

    protected function initCmixUser() : void
    {
        $this->cmixUser = new ilCmiXapiUser($this->object->getId(), $this->dic->user()->getId(), $this->object->getProvider()->getPrivacyIdent());
        $user_ident = $this->cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($this->object->getProvider()->getPrivacyIdent(), $this->dic->user());
            $this->cmixUser->setUsrIdent($user_ident);
            $this->cmixUser->save();
        }
    }

    private function validateLTI13InitalLogin(array $loginData) : ?string
    {
        $scope = $loginData['scope'];
        $responsetype = $loginData['response_type'];
        $clientid = $loginData['client_id'];
        $redirecturi = $loginData['redirect_uri'];
        $loginhint = $loginData['login_hint'];
        $ltimessagehint = $loginData['lti_message_hint'];
        $state = $loginData['state'];
        $responsemode = $loginData['response_mode'];
        $nonce = $loginData['nonce'];
        $prompt = $loginData['prompt'];

        $ok = !empty($scope) && !empty($responsetype) && !empty($clientid) &&
            !empty($redirecturi) && !empty($loginhint) &&
            !empty($nonce) && (ilSession::get('lti_message_hint') != null);

        if (!$ok) {
            $error = 'invalid_request';
        }
        if ($ok && ($scope !== 'openid')) {
            $ok = false;
            $error = 'invalid_scope';
        }
        if ($ok && ($responsetype !== 'id_token')) {
            $ok = false;
            $error = 'unsupported_response_type';
        }
        if ($ok) {
            list($ref_id, $ilias_client_id) = explode(':', ilSession::get('lti_message_hint'), 2);
            if ((int) $this->object->getRefId() !== (int) $ref_id) {
                $ok = false;
                $error = 'invalid_request';
            }
            if ($this->object->getProvider()->getClientId() !== $clientid) {
                $ok = false;
                $error = 'unauthorized_client';
            }
        }

        if ($ok) {
            $cmixUser = new ilCmiXapiUser(
                $this->object->getId(),
                $this->user->getId(),
                $this->object->getProvider()->getPrivacyIdent()
            );
            $user_ident = $cmixUser->getUsrIdent();
            // required?
            if ($user_ident == '' || $user_ident == null) {
                $user_ident = ilCmiXapiUser::getIdent($this->object->getProvider()->getPrivacyIdent(), $this->dic->user());
                $cmixUser->setUsrIdent($user_ident);
                $cmixUser->save();
            }

            if ((string) $loginhint !== $user_ident) {
                $ok = false;
                $error = 'access_denied';
            }
        }
        $uris = array_map("trim", explode(",", $this->object->getProvider()->getRedirectionUris()));
        if (!in_array($redirecturi, $uris)) {
            $ok = false;
            $error = 'invalid_request';
            //throw new moodle_exception('invalidrequest', 'error');
        }

        if ($ok) {
            if (isset($responsemode)) {
                $ok = ($responsemode === 'form_post');
                if (!$ok) {
                    $error = 'invalid_request';
                    $desc = 'Invalid response_mode';
                }
            } else {
                $ok = false;
                $error = 'invalid_request';
                $desc = 'Missing response_mode';
            }
        }
        if ($ok && !empty($prompt) && ($prompt !== 'none')) {
            $ok = false;
            $error = 'invalid_request';
            $desc = 'Invalid prompt';
        }
        if ($ok) {
            return null;
        } else {
            return $error;
        }
    }

    // TODO: request_wrapper?
    /**
     * @param mixed  $default
     * @return mixed|null
     */
    protected function getRequestValue(string $key, $default = null)
    {
        global $DIC;
        if (isset($DIC->http()->request()->getQueryParams()[$key])) {
            return $DIC->http()->request()->getQueryParams()[$key];
        }

        if (isset($DIC->http()->request()->getParsedBody()[$key])) {
            return $DIC->http()->request()->getParsedBody()[$key];
        }

        return $default ?? null;
    }
}
