<?php

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

declare(strict_types=1);

/**
 * Class ilLTIConsumerContentGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.com>
 *
 * @package components\ILIAS/LTIConsumer
 */
class ilLTIConsumerContentGUI
{
    public const CMD_LAUNCH = 'launch';
    public const CMD_SHOW_EMBEDDED = 'showEmbedded';

    /**
     * Query parameter that carries the authentication request of the tool from ltiauth.php to the launch,
     * instead of the session: a parallel request of the same session writing the session back later would
     * remove it again, e.g. the asynchronous requests of the main bar while an embedded launch is running.
     */
    public const LTI13_LOGIN_PARAM = 'lti13_login';

    private const LTI13_LOGIN_KEYS = [
        'scope',
        'response_type',
        'client_id',
        'redirect_uri',
        'login_hint',
        'lti_message_hint',
        'state',
        'response_mode',
        'nonce',
        'prompt'
    ];

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
    public function executeCommand(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            throw new ilLtiConsumerException('access denied!');
        }
        $command = $DIC->ctrl()->getCmd(self::CMD_LAUNCH);
        $this->{$command}();
    }

    public function getJwtForContentSelection(string $redirectUri, string $clientId, int $deploymentId, string $nonce, ?array $additionalArguments = null): string
    {
        $jwt = "";
        $this->initCmixUser();
        $jwtArray = $this->getLaunchParametersLTI13($redirectUri, $clientId, $deploymentId, $nonce, $additionalArguments);
        if (isset($jwtArray['id_token'])) {
            $jwt = $jwtArray['id_token'];
        }
        return $jwt;
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    protected function launch(): void
    {
        if ($this->object->getProvider()->getLtiVersion() == "LTI-1p0") {
            if ($this->object->isLaunchMethodEmbedded()) {
                $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'components/ILIAS/LTIConsumer');
                $tpl->setVariable("EMBEDDED_IFRAME_SRC", $this->dic->ctrl()->getLinkTarget(
                    $this,
                    self::CMD_SHOW_EMBEDDED
                ));
                $this->dic->ui()->mainTemplate()->setContent($tpl->get());
            } else {
                $this->dic->toolbar()->addText($this->getStartButtonTxt11());
            }
        } else {
            $loginData = $this->getLTI13LoginData();
            if ($this->object->isLaunchMethodEmbedded() && $loginData === null) {
                $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'components/ILIAS/LTIConsumer');
                $tpl->setVariable("EMBEDDED_IFRAME_SRC", $this->dic->ctrl()->getLinkTarget(
                    $this,
                    self::CMD_SHOW_EMBEDDED
                ));
                $this->dic->ui()->mainTemplate()->setContent($tpl->get());
            } else {
                if ($loginData !== null) {
                    $form = $this->getLoginLTI13Form($loginData);
                    if ($form == null) {
                        //                        $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', 'initialLogin Error: ' . $err, true);
                        $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', 'initialLogin Error: ', true);
                    } else {
                        $response = $this->dic->http()->response()->withBody(ILIAS\Filesystem\Stream\Streams::ofString($form));
                        $this->dic->http()->saveResponse($response);
                        $this->dic->http()->sendResponse();
                        $this->dic->http()->close();
                    }
                } elseif (!$this->object->isLaunchMethodEmbedded()) {
                    $this->dic->toolbar()->addText($this->getStartButtonTxt13());
                }
            }
        }
    }

    protected function getLoginLTI13Form(array $loginData): ?string
    {
        if (!$this->dic->access()->checkAccess('read', '', $this->object->getRefId())) {
            return null;
        }
        $err = $this->validateLTI13InitalLogin($loginData);
        if ($err !== null) {
            return null;
        } else {
            $this->initCmixUser();
            $targetLinkUri = $loginData['target_link_uri'] ?? $this->getTargetLinkUri();
            $params = $this->getLaunchParametersLTI13($targetLinkUri, $this->object->getProvider()->getClientId(), $this->object->getProvider()->getId(), $loginData['nonce']);
            if (isset($loginData['state'])) {
                $params['state'] = $loginData['state'];
            }
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

    protected function getStartButtonTxt11(): string
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
        $contextId = (string) $context["id"];
        $contextTitle = $context["title"];

        $token = ilCmiXapiAuthToken::fillToken(
            $this->dic->user()->getId(),
            $this->object->getRefId(),
            $this->object->getId()
        );

        $returnUrl = !$this->object->isLaunchMethodOwnWin() ? '' : str_replace(
            '&amp;',
            '&',
            ilObjLTIConsumer::getIliasHttpPath() . "/" . $this->dic->ctrl()->getLinkTarget($this, "", "", false)
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
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $this->lng->txt("show_content") . '" />';
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $this->object->getProvider()->getProviderUrl() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        foreach ($launchParameters as $field => $value) {
            $output .= sprintf(
                '<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars((string) $field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            ) . "\n";
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

    protected function getStartButtonTxt13(): string
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
            ilObjLTIConsumer::getIliasHttpPath() . "/" . $this->dic->ctrl()->getLinkTarget($this, "", "", false)
        );

        $target = $this->object->getLaunchMethod() == "newWin" ? "_blank" : "_self";
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $this->lng->txt("show_content") . '" />';
        $ltiMessageHint = self::getLTI13MessageHint($this->object->getRefId());
        $html = $this->dic->refinery()->encode()->htmlSpecialCharsAsEntities();
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $html->transform($this->object->getProvider()->getInitiateLogin()) . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';

        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'iss', $html->transform(ilObjLTIConsumer::getIliasHttpPath())) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'target_link_uri', $html->transform($this->getTargetLinkUri())) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'login_hint', $html->transform($user_ident)) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_message_hint', $ltiMessageHint) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'client_id', $html->transform($this->object->getProvider()->getClientId())) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_deployment_id', $this->object->getProvider()->getId()) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'launch_presentation_return_url', $html->transform($returnUrl)) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lis_result_sourcedid', $token) . "\n";
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
    protected function getEmbeddedAutoStartFormular(): string
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
        $ltiMessageHint = self::getLTI13MessageHint($this->object->getRefId());
        $html = $this->dic->refinery()->encode()->htmlSpecialCharsAsEntities();
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $html->transform($this->object->getProvider()->getInitiateLogin()) . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'iss', $html->transform(ilObjLTIConsumer::getIliasHttpPath())) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'target_link_uri', $html->transform($this->getTargetLinkUri())) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'login_hint', $html->transform($user_ident)) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_message_hint', $ltiMessageHint) . "\n";
        $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'client_id', $html->transform($this->object->getProvider()->getClientId())) . "\n";
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
    protected function showEmbedded(): void
    {
        if ($this->object->getProvider()->getLtiVersion() == "LTI-1p0") {
            $this->initCmixUser();
            $tpl = new ilTemplate('tpl.lti_embedded.html', true, true, 'components/ILIAS/LTIConsumer');
            foreach ($this->getLaunchParameters() as $field => $value) {
                $tpl->setCurrentBlock('launch_parameter');
                $tpl->setVariable('LAUNCH_PARAMETER', htmlspecialchars((string) $field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                $tpl->setVariable('LAUNCH_PARAM_VALUE', htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                $tpl->parseCurrentBlock();
            }

            $v = DEVMODE ? '?vers=' . time() : '?vers=' . ILIAS_VERSION_NUMERIC;
            $tpl->setVariable("DELOS_CSS_HREF", 'assets/css/delos.css' . $v);
            $tpl->setVariable("JQUERY_SRC", 'assets/js/jquery.js' . $v);

            $tpl->setVariable("LOADER_ICON_SRC", ilUtil::getImagePath("media/loader.svg"));
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

    protected function getLaunchParameters(): array
    {
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $launchContext = $ilLTIConsumerLaunch->getContext();

        $launchContextType = ilLTIConsumerLaunch::getLTIContextType($launchContext["type"]);
        $launchContextId = (string) $launchContext["id"];
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

    protected function getLaunchParametersLTI13(string $endpoint, string $clientId, int $deploymentId, string $nonce, ?array $additionalArguments = null): ?array
    {
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $launchContext = $ilLTIConsumerLaunch->getContext();

        $launchContextType = ilLTIConsumerLaunch::getLTIContextType($launchContext["type"]);
        $launchContextId = (string) $launchContext["id"];
        $launchContextTitle = $launchContext["title"];

        $token = ilCmiXapiAuthToken::fillToken(
            $this->dic->user()->getId(),
            $this->object->getRefId(),
            $this->object->getId()
        );
        $returnUrl = !$this->object->isLaunchMethodOwnWin() ? '' : str_replace(
            '&amp;',
            '&',
            ilObjLTIConsumer::getIliasHttpPath() . "/" . $this->dic->ctrl()->getLinkTarget($this, "", "", false)
        );

        $cmixUser = $this->cmixUser;
        return $this->object->buildLaunchParametersLTI13(
            $cmixUser,
            $token,
            $endpoint,
            $clientId,
            $deploymentId,
            $nonce,
            $launchContextType,
            $launchContextId,
            $launchContextTitle,
            $returnUrl,
            $additionalArguments
        );
    }

    private function getTargetLinkUri(): string
    {
        foreach (explode(';', $this->object->getCustomParams()) as $param) {
            $parts = explode('=', $param, 2);
            if (count($parts) === 2 && trim($parts[0]) === 'target_link_uri') {
                return trim($parts[1]);
            }
        }

        return $this->object->getProvider()->getProviderUrl();
    }

    public static function isEmbeddedLaunchRequest(): bool
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

    protected function initCmixUser(): void
    {
        $this->cmixUser = new ilCmiXapiUser($this->object->getId(), $this->dic->user()->getId(), $this->object->getProvider()->getPrivacyIdent());
        $user_ident = $this->cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($this->object->getProvider()->getPrivacyIdent(), $this->dic->user());
            $this->cmixUser->setUsrIdent($user_ident);
            $this->cmixUser->save();
        }
    }

    private function validateLTI13InitalLogin(array $loginData): ?string
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
            !empty($nonce) && self::isValidLTI13MessageHint((string) $ltimessagehint, $this->object->getRefId());

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

    /**
     * The lti_message_hint of a launch: the object and the ILIAS client, signed for the session and the user.
     * The tool sends it back unchanged with its authentication request, so the launch can be checked without
     * keeping anything in the session.
     */
    public static function getLTI13MessageHint(int $ref_id): string
    {
        $hint = $ref_id . ':' . CLIENT_ID;

        return $hint . '.' . self::signLTI13MessageHint($hint);
    }

    /**
     * The lti_message_hint without its signature, as ltiauth.php reads the object and the ILIAS client from it.
     */
    public static function getUnsignedLTI13MessageHint(string $hint): string
    {
        if (preg_match('/^(.+)\.[0-9a-f]{64}$/', $hint, $matches) === 1) {
            return $matches[1];
        }

        return $hint;
    }

    private static function isValidLTI13MessageHint(string $hint, int $ref_id): bool
    {
        return session_id() !== '' && hash_equals(self::getLTI13MessageHint($ref_id), $hint);
    }

    private static function signLTI13MessageHint(string $hint): string
    {
        global $DIC;

        return hash_hmac('sha256', $hint . '|' . $DIC->user()->getId(), (string) session_id());
    }

    /**
     * The link ltiauth.php sends the browser to with the authentication request of the tool, which goes on
     * with the launch of the object.
     */
    public static function getLTI13LaunchLink(int $ref_id, string $ilias_client_id, array $data): string
    {
        global $DIC;

        $login_data = [];
        foreach (self::LTI13_LOGIN_KEYS as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $login_data[$key] = $data[$key];
            }
        }
        $encoded = rtrim(strtr(base64_encode(json_encode($login_data, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

        $ctrl = $DIC->ctrl();
        $ctrl->setParameterByClass(ilObjLTIConsumerGUI::class, 'ref_id', $ref_id);
        $ctrl->setParameterByClass(self::class, self::LTI13_LOGIN_PARAM, $encoded);
        $ctrl->setParameterByClass(self::class, 'client_id', $ilias_client_id);

        return $ctrl->getLinkTargetByClass(
            [ilRepositoryGUI::class, ilObjLTIConsumerGUI::class, self::class],
            self::CMD_LAUNCH,
            '',
            false,
            false
        );
    }

    /**
     * The authentication request of the tool passed on by ltiauth.php, null when the request has none.
     */
    private function getLTI13LoginData(): ?array
    {
        $value = $this->getRequestValue(self::LTI13_LOGIN_PARAM);
        if (!is_string($value) || $value === '') {
            return null;
        }
        $json = base64_decode(strtr($value, '-_', '+/'), true);
        $data = is_string($json) ? json_decode($json, true) : null;
        if (!is_array($data)) {
            return null;
        }

        $login_data = array_fill_keys(self::LTI13_LOGIN_KEYS, null);
        foreach (self::LTI13_LOGIN_KEYS as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $login_data[$key] = $data[$key];
            }
        }

        return $login_data;
    }

    // TODO: request_wrapper?

    /**
     * @param string $key
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
