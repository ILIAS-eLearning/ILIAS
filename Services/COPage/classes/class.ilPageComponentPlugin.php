<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all page component plugin classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
abstract class ilPageComponentPlugin extends ilPlugin
{
    const TXT_CMD_INSERT = "cmd_insert";
    const CMD_INSERT = "insert";
    const CMD_EDIT = "edit";

    /**
     *
     * @var ilPageObject|null
     */
    private $page_obj = null;
    
    /**
     * Get Component Type
     *
     * @return        string        Component Type
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }
    
    /**
     * Get Component Name.
     *
     * @return        string        Component Name
     */
    final public function getComponentName()
    {
        return "COPage";
    }
    
    /**
     * Get Slot Name.
     *
     * @return        string        Slot Name
     */
    final public function getSlot()
    {
        return "PageComponent";
    }
    
    /**
    * Get Slot ID.
    *
    * @return        string        Slot Id
    */
    final public function getSlotId()
    {
        return "pgcp";
    }
    
    /**
    * Object initialization done by slot.
    */
    final protected function slotInit()
    {
        // nothing to do here
    }
    
    /**
     * Determines the resources that allow to include the
     * new content component.
     *
     * @param	string		$a_type		Parent type (e.g. "cat", "lm", "glo", "wiki", ...)
     *
     * @return	boolean		true/false if the resource type allows
     */
    abstract public function isValidParentType($a_type);
    
    /**
     * Get Javascript files
     */
    public function getJavascriptFiles($a_mode)
    {
        return array();
    }
    
    /**
     * Get css files
     */
    public function getCssFiles($a_mode)
    {
        return array();
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
     * Get UI plugin class
     */
    public function getUIClassInstance()
    {
        $class = "il" . $this->getPluginName() . "PluginGUI";
        $this->includeClass("class." . $class . ".php");
        $obj = new $class();
        $obj->setPlugin($this);
        return $obj;
    }

    /**
     * Inject the page object
     * This must be public to be called by ilPCPlugged
     * But the page object should not directly be accessible by plugins
     * @param ilPageObject
     */
    public function setPageObj($a_page_obj)
    {
        $this->page_obj = $a_page_obj;
    }

    /**
     * Get the id of the page
     * @return int
     */
    public function getPageId()
    {
        if (isset($this->page_obj)) {
            return $this->page_obj->getId();
        }
        return 0;
    }

    /**
     * Get the object id of the parent object
     * @return int
     */
    public function getParentId()
    {
        if (isset($this->page_obj)) {
            return $this->page_obj->getParentId();
        }
        return 0;
    }

    /**
     * Get the object type og the parent object
     * @return string
     */
    public function getParentType()
    {
        if (isset($this->page_obj)) {
            return $this->page_obj->getParentType();
        }
        return '';
    }

    /**
     * This function is called when the page content is cloned
     * @param array 	$a_properties		(properties saved in the page, should be modified if neccessary)
     * @param string	$a_plugin_version	(plugin version of the properties)
     */
    public function onClone(&$a_properties, $a_plugin_version)
    {
    }

    /**
     * This function is called before the page content is deleted
     * @param array 	$a_properties		properties saved in the page (will be deleted afterwards)
     * @param string	$a_plugin_version	plugin version of the properties
     */
    public function onDelete($a_properties, $a_plugin_version)
    {
    }
}
