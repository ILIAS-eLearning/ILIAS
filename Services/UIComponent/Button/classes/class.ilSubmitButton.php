<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Submit Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
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
