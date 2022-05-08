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
        if ($this->object->isLaunchMethodEmbedded()) {
            $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'Modules/LTIConsumer');
            $tpl->setVariable("EMBEDDED_IFRAME_SRC", $this->dic->ctrl()->getLinkTarget(
                $this,
                self::CMD_SHOW_EMBEDDED
            ));
            $this->dic->ui()->mainTemplate()->setContent($tpl->get());
        } else {
            if ($this->object->getProvider()->getLtiVersion() == "LTI-1p0") {
                $this->dic->toolbar()->addText($this->getStartButtonTxt11());
            } else {
                //LTI13
            }
        }
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
        if ($this->object->getProvider()->getLtiVersion() == "LTI-1p0") {
            $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $this->object->getProvider()->getProviderUrl() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
            foreach ($launchParameters as $field => $value) {
                $output .= sprintf('<input type="hidden" name="%s" value="%s" />', $field, $value) . "\n";
            }
        } else {
            ilSession::set('lti_message_hint', (string) $this->object->getRefId());
            $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $this->object->getProvider()->getInitiateLogin() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'iss', ILIAS_HTTP_PATH) . "\n";
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'target_link_uri', $this->object->getProvider()->getProviderUrl()) . "\n";
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'login_hint', $user_ident) . "\n";
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_message_hint', $this->object->getRefId()) . "\n";
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'client_id', $this->object->getProvider()->getClientId()) . "\n";
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', 'lti_deployment_id', $this->object->getProvider()->getId()) . "\n";
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

    /**
     * @throws ilTemplateException
     */
    protected function showEmbedded() : void
    {
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
        exit;
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
}
