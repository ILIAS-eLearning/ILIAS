<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author jposselt@databay.de
 */
class ilChatroomObjectDefinition
{
    /**
     * Module name, defaults to 'Chatroom'
     * @var string
     */
    private $moduleName;

    /**
     * Module base path, set to "Modules/$this->moduleName/"
     * @var string
     */
    private $moduleBasePath;

    /**
     * always set to 'classes'
     * @var string
     */
    private $relativeClassPath;

    /**
     * GUIScope
     * set to '' for single instance or 'admin' for general administration
     * @var string
     */
    private $guiScope;

    /**
     * Sets class parameters using given parameters.
     * @param string $moduleName
     * @param string $moduleBasePath
     * @param string $relativeClassPath Optional.
     * @param string $guiScope          Optional.
     */
    public function __construct($moduleName, $moduleBasePath, $relativeClassPath = 'classes', $guiScope = '')
    {
        $this->moduleName        = $moduleName;
        $this->moduleBasePath    = rtrim($moduleBasePath, '/\\');
        $this->relativeClassPath = rtrim($relativeClassPath);
        $this->guiScope          = rtrim($guiScope);
    }

    /**
     * Returns an Instance of ilChatroomObjectDefinition, using given $moduleName
     * as parameter.
     * @param string $moduleName
     * @return ilChatroomObjectDefinition
     */
    public static function getDefaultDefinition($moduleName)
    {
        $object = new self($moduleName, 'Modules/' . $moduleName . '/');

        return $object;
    }

    /**
     * Returns an Instance of ilChatroomObjectDefinition, using given $moduleName
     * and $guiScope as parameters.
     * @param string $moduleName
     * @param string $guiScope Optional. 'admin' or ''. Default ''
     * @return ilChatroomObjectDefinition
     */
    public static function getDefaultDefinitionWithCustomGUIPath($moduleName, $guiScope = '')
    {
        $object = new self(
            $moduleName,
            'Modules/' . $moduleName . '/',
            'classes',
            $guiScope
        );

        return $object;
    }

    /**
     * Returns true if file exists.
     * @param string $gui
     * @return boolean
     */
    public function hasGUI($gui)
    {
        return file_exists($this->getGUIPath($gui));
    }

    /**
     * Builds gui path using given $gui and returns it.
     * @param string $gui
     * @return string
     */
    public function getGUIPath($gui)
    {
        return $this->moduleBasePath . '/' . $this->relativeClassPath . '/' .
        $this->guiScope . 'gui/class.' . $this->getGUIClassName($gui) . '.php';
    }

    /**
     * Builds gui classname using given $gui and returns it.
     * @param string $gui
     * @return string
     */
    public function getGUIClassName($gui)
    {
        return 'il' . $this->moduleName . ucfirst($this->guiScope) . ucfirst($gui) . 'GUI';
    }

    /**
     * Requires file, whereby given $gui is used as parameter in getGUIPath
     * method to build the filename of the file to required.
     * @param string $gui
     */
    public function loadGUI($gui)
    {
        require_once $this->getGUIPath($gui);
    }

    /**
     * Builds and returns new gui using given $gui and $gui
     * @param string              $gui
     * @param ilChatroomObjectGUI $chatroomObjectGUI
     * @return ilChatroomGUIHandler
     */
    public function buildGUI($gui, ilChatroomObjectGUI $chatroomObjectGUI)
    {
        $className   = $this->getGUIClassName($gui);
        $guiInstance = new $className($chatroomObjectGUI);

        return $guiInstance;
    }
}
