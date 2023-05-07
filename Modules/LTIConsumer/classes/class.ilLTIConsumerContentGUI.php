<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    
    /**
     * @var ilObjLTIConsumer
     */
    protected $object;
    
    /**
     * @var ilCmiXapiUser
     */
    protected $cmixUser;

    /**
     * @var \ILIAS\DI\Container
     */
    private $dic;

    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var ilObjUser
     */
    private $user;
    /**
     * @param ilObjLTIConsumer $object
     */
    public function __construct(ilObjLTIConsumer $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->object = $object;
    }
    
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            throw new ilLtiConsumerException('access denied!');
        }
        
        $command = $DIC->ctrl()->getCmd(self::CMD_LAUNCH);
        
        $this->{$command}();
    }
    
//    protected function showPage()
//    {
//        global $DIC; /* @var \ILIAS\DI\Container $DIC */
//
//        $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'Modules/LTIConsumer');
//
//        $tpl->setVariable("EMBEDDED_IFRAME_SRC", $DIC->ctrl()->getLinkTarget(
//            $this,
//            self::CMD_SHOW_EMBEDDED
//        ));
//
//        $DIC->ui()->mainTemplate()->setContent($tpl->get());
//    }

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
            $this->dic->toolbar()->addText($this->getStartButtonTxt11());
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
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $this->lng->txt("show_content") . '" />';
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
    protected function showEmbedded()
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
    
    protected function getLaunchParameters()
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
    
    protected function initCmixUser()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->cmixUser = new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getProvider()->getPrivacyIdent());
        $user_ident = $this->cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($this->object->getProvider()->getPrivacyIdent(), $DIC->user());
            $this->cmixUser->setUsrIdent($user_ident);
            $this->cmixUser->save();
        }
    }
}
