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
 * Class ilLTIConsumeProviderSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProviderSettingsGUI
{
    const CMD_SHOW_SETTINGS = 'showSettings';
    const CMD_SAVE_SETTINGS = 'saveSettings';
    
    /**
     * @var ilObjLTIConsumer
     */
    protected ilObjLTIConsumer $object;
    
    /**
     * @var ilLTIConsumerAccess
     */
    protected ilLTIConsumerAccess $access;
    
    /**
     * ilLTIConsumerAccess constructor.
     * @param ilObjLTIConsumer $object
     */
    public function __construct(ilObjLTIConsumer $object, ilLTIConsumerAccess $access)
    {
        $this->object = $object;
        $this->access = $access;
    }
    
    /**
     * Execute Command
     */
    public function executeCommand() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        switch ($DIC->ctrl()->getNextClass()) {
            default:
                
                $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function showSettingsCmd(ilLTIConsumeProviderFormGUI $form = null) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($form === null) {
            $form = $this->buildForm($this->object->getProvider());
        }
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function saveSettingsCmd() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = $this->buildForm($this->object->getProvider());
        
        if ($form->checkInput()) {
            $form->initProvider($this->object->getProvider());
            $this->object->getProvider()->save();
            
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
        }
        
        $this->showSettingsCmd($form);
    }

    /**
     * @param ilLTIConsumeProvider $provider
     * @return ilLTIConsumeProviderFormGUI
     * @throws ilCtrlException
     */
    protected function buildForm(ilLTIConsumeProvider $provider) : ilLTIConsumeProviderFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = new ilLTIConsumeProviderFormGUI($provider);
        
        $form->initForm(
            $DIC->ctrl()->getFormAction($this),
            self::CMD_SAVE_SETTINGS,
            self::CMD_SHOW_SETTINGS
        );
        
        return $form;
    }
}
