<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract parent class for all page component plugin gui classes.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
abstract class ilPageComponentPluginGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    protected $plugin;
    protected $pc_gui;
    protected $pc;
    
    /**
     * Set pc gui object
     *
     * @param object $a_val pc gui object
     */
    public function setPCGUI($a_val)
    {
        $this->pc_gui = $a_val;
    }
    
    /**
     * Get pc gui object
     *
     * @return object pc gui object
     */
    public function getPCGUI()
    {
        return $this->pc_gui;
    }
    
    /**
     * Set plugin object
     *
     * @param object $a_val plugin object
     */
    public function setPlugin($a_val)
    {
        $this->plugin = $a_val;
    }
    
    /**
     * Get plugin object
     *
     * @return object plugin object
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
    
    /**
     * Set Mode.
     *
     * @param	string	$a_mode	Mode
     */
    final public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    /**
     * Get Mode.
     *
     * @return	string	Mode
     */
    final public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get HTML
     *
     * @param
     * @return
     */
    public function getHTML()
    {
        if ($this->getMode() == ilPageComponentPlugin::CMD_INSERT) {
            $this->insert();
        } elseif ($this->getMode() == ilPageComponentPlugin::CMD_EDIT) {
            $this->edit();
        }
    }

    abstract public function executeCommand();
    abstract public function insert();
    abstract public function edit();
    abstract public function create();
    abstract public function getElementHTML($a_mode, array $a_properties, $plugin_version);
    
    public function createElement(array $a_properties)
    {
        return $this->getPCGUI()->createElement($a_properties);
    }
    
    public function updateElement(array $a_properties)
    {
        return $this->getPCGUI()->updateElement($a_properties);
    }
    
    /**
     * Return to parent
     */
    public function returnToParent()
    {
        $this->getPCGUI()->returnToParent();
    }

    /**
     * Set properties
     *
     * @param array $a_val properties array
     */
    public function setProperties(array $a_val)
    {
        $co = $this->getPCGUI()->getContentObject();
        if (is_object($co)) {
            $co->setProperties($a_val);
        }
    }
    
    /**
     * Get properties
     *
     * @return array properties array
     */
    public function getProperties()
    {
        $co = $this->getPCGUI()->getContentObject();
        if (is_object($co)) {
            return $co->getProperties($a_val);
        }
        return array();
    }

    /**
     * Add creation button
     *
     * @param
     * @return
     */
    final protected function addCreationButton($a_form)
    {
        $lng = $this->lng;
        
        $a_form->addCommandButton("create_plug", $lng->txt("save"));
    }
}
