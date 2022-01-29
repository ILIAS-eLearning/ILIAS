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
    protected ilObjLTIConsumer $object;
    
    /**
     * @var ilCmiXapiUser
     */
    protected ilCmiXapiUser $cmixUser;
    
    /**
     * @param ilObjLTIConsumer $object
     */
    public function __construct(ilObjLTIConsumer $object)
    {
        $this->object = $object;
    }

    /**
     * @return void
     * @throws ilLtiConsumerException
     */
    public function executeCommand() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            throw new ilLtiConsumerException('access denied!');
        }
        
        $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_PAGE);
        
        $this->{$command}();
    }

    /**
     * @return void
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    protected function showPage() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $tpl = new ilTemplate('tpl.lti_content.html', true, true, 'Modules/LTIConsumer');
        
        $tpl->setVariable("EMBEDDED_IFRAME_SRC", $DIC->ctrl()->getLinkTarget(
            $this,
            self::CMD_SHOW_EMBEDDED
        ));
        
        $DIC->ui()->mainTemplate()->setContent($tpl->get());
    }

    /**
     * @return void
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
    
    /**
     * @return array
     */
    protected function getLaunchParameters() : array
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
