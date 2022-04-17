<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Abstract parent class for all page component plugin classes.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilPageComponentPlugin extends ilPlugin
{
    public const TXT_CMD_INSERT = "cmd_insert";
    public const CMD_INSERT = "insert";
    public const CMD_EDIT = "edit";

    private ?ilPageObject $page_obj = null;
    protected string $mode;

    /**
     * Determines the resources that allow to include the
     * new content component.
     * @param string $a_type Parent type (e.g. "cat", "lm", "glo", "wiki", ...)
     * @return bool true/false if the resource type allows
     */
    abstract public function isValidParentType(string $a_type) : bool;
    
    public function getJavascriptFiles(string $a_mode) : array
    {
        return array();
    }
    
    public function getCssFiles(string $a_mode) : array
    {
        return array();
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
     * Get UI plugin class
     */
    public function getUIClassInstance() : ilPageComponentPluginGUI
    {
        $class = "il" . $this->getPluginName() . "PluginGUI";
        $obj = new $class();
        $obj->setPlugin($this);
        return $obj;
    }

    /**
     * Inject the page object
     * This must be public to be called by ilPCPlugged
     * But the page object should not directly be accessible by plugins
     */
    public function setPageObj(ilPageObject $a_page_obj) : void
    {
        $this->page_obj = $a_page_obj;
    }

    /**
     * Get the id of the page
     */
    public function getPageId() : int
    {
        if (isset($this->page_obj)) {
            return $this->page_obj->getId();
        }
        return 0;
    }

    /**
     * Get the object id of the parent object
     */
    public function getParentId() : int
    {
        if (isset($this->page_obj)) {
            return $this->page_obj->getParentId();
        }
        return 0;
    }

    /**
     * Get the object type og the parent object
     */
    public function getParentType() : string
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
    public function onClone(
        array &$a_properties,
        string $a_plugin_version
    ) : void {
    }

    /**
     * This function is called after repository (container) objects have been copied
     *
     * @param array $a_properties properties saved in the page, should be modified if neccessary
     * @param array $mapping repository object mapping array
     * @param int $source_ref_id ref id of source object
     * @param string $a_plugin_version plugin version of the properties
     */
    public function afterRepositoryCopy(
        array &$a_properties,
        array $mapping,
        int $source_ref_id,
        string $a_plugin_version
    ) : void {
    }

    /**
     * This function is called before the page content is deleted
     * @param array 	$a_properties		properties saved in the page (will be deleted afterwards)
     * @param string	$a_plugin_version	plugin version of the properties
     */
    public function onDelete(
        array $a_properties,
        string $a_plugin_version
    ) : void {
    }
}
