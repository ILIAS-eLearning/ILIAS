<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerEmbeddedContentGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerEmbeddedContentGUI
{
    const CMD_SHOW_PAGE = 'showPage';
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
     * @param ilObjLTIConsumer $object
     */
    public function __construct(ilObjLTIConsumer $object)
    {
        $this->object = $object;
    }
    
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            throw new ilLtiConsumerException('access denied!');
        }
        
        $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_PAGE);
        
        $this->{$command}();
    }
    
    protected function showPage()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'Modules/LTIConsumer');
        
        $tpl->setVariable("EMBEDDED_IFRAME_SRC", $DIC->ctrl()->getLinkTarget(
            $this,
            self::CMD_SHOW_EMBEDDED
        ));
        
        $DIC->ui()->mainTemplate()->setContent($tpl->get());
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
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($this->object->getRefId());
        $launchContext = $ilLTIConsumerLaunch->getContext();
        
        $launchContextType = ilLTIConsumerLaunch::getLTIContextType($launchContext["type"]);
        $launchContextId = $launchContext["id"];
        $launchContextTitle = $launchContext["title"];
        
        $token = ilCmiXapiAuthToken::fillToken(
            $DIC->user()->getId(),
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
    
    public static function isEmbeddedLaunchRequest()
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
