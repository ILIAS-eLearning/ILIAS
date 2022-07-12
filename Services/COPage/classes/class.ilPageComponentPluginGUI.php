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

/**
 * Abstract parent class for all page component plugin gui classes.
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilPageComponentPluginGUI
{
    protected string $mode;
    protected ilLanguage $lng;
    protected ilPageComponentPlugin $plugin;
    protected ilPCPluggedGUI $pc_gui;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function setPCGUI(ilPCPluggedGUI $a_val) : void
    {
        $this->pc_gui = $a_val;
    }
    
    public function getPCGUI() : ilPCPluggedGUI
    {
        return $this->pc_gui;
    }
    
    public function setPlugin(ilPageComponentPlugin $a_val) : void
    {
        $this->plugin = $a_val;
    }
    
    public function getPlugin() : ilPageComponentPlugin
    {
        return $this->plugin;
    }
    
    final public function setMode(string $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    final public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * Get HTML
     */
    public function getHTML() : void
    {
        if ($this->getMode() == ilPageComponentPlugin::CMD_INSERT) {
            $this->insert();
        } elseif ($this->getMode() == ilPageComponentPlugin::CMD_EDIT) {
            $this->edit();
        }
    }

    abstract public function executeCommand() : void;
    abstract public function insert() : void;
    abstract public function edit() : void;
    abstract public function create() : void;
    abstract public function getElementHTML(
        string $a_mode,
        array $a_properties,
        string $plugin_version
    ) : string;
    
    public function createElement(array $a_properties) : bool
    {
        return $this->getPCGUI()->createElement($a_properties);
    }
    
    public function updateElement(array $a_properties) : bool
    {
        return $this->getPCGUI()->updateElement($a_properties);
    }
    
    public function returnToParent() : void
    {
        $this->getPCGUI()->returnToParent();
    }

    /**
     * Set properties
     */
    public function setProperties(array $a_val) : void
    {
        $co = $this->getPCGUI()->getContentObject();
        if (is_object($co)) {
            $co->setProperties($a_val);
        }
    }
    
    public function getProperties() : array
    {
        $co = $this->getPCGUI()->getContentObject();
        if (is_object($co)) {
            return $co->getProperties();
        }
        return array();
    }

    final protected function addCreationButton(ilPropertyFormGUI $a_form) : void
    {
        $lng = $this->lng;
        
        $a_form->addCommandButton("create_plug", $lng->txt("save"));
    }
}
