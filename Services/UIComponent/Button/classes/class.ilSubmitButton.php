<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/UIComponent/Button/classes/class.ilButtonBase.php";

/**
 * Submit Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilTabsGUI.php 45109 2013-09-30 15:46:28Z akill $
 * @package ServicesUIComponent
 */
class ilSubmitButton extends ilButtonBase
{
    protected $cmd; // [string]
    
    public static function getInstance()
    {
        return new self(self::TYPE_SUBMIT);
    }
    
    
    //
    // properties
    //
    
    /**
     * Set submit command
     *
     * @param string $a_value
     */
    public function setCommand($a_value)
    {
        $this->cmd = trim($a_value);
    }
    
    /**
     * Get submit command
     *
     * @param string $a_value
     */
    public function getCommand()
    {
        return $this->cmd;
    }
    
    
    //
    // render
    //
        
    public function render()
    {
        $this->prepareRender();
        
        $attr = array();
        $attr["type"] = "submit";
        $attr["name"] = "cmd[" . $this->getCommand() . "]";
        $attr["value"] = $this->getCaption();
        
        return '<input' . $this->renderAttributes($attr) . ' />';
    }
}
