<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    protected $object;
    
    /**
     * @var ilLTIConsumerAccess
     */
    protected $access;
    
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
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        switch ($DIC->ctrl()->getNextClass()) {
            default:
                
                $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function showSettingsCmd(ilLTIConsumeProviderFormGUI $form = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($form === null) {
            $form = $this->buildForm($this->object->getProvider());
        }
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function saveSettingsCmd()
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
     * @return ilLTIConsumeProviderFormGUI
     */
    protected function buildForm(ilLTIConsumeProvider $provider)
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
