<?php

declare(strict_types=1);

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
 * @author jposselt@databay.de
 */
class ilChatroomObjectDefinition
{
    /**
     * Module name, defaults to 'Chatroom'
     * @var string
     */
    private string $moduleName;

    /**
     * Module base path, set to "Modules/$this->moduleName/"
     * @var string
     */
    private string $moduleBasePath;

    /**
     * always set to 'classes'
     * @var string
     */
    private string $relativeClassPath;

    /**
     * GUIScope
     * set to '' for single instance or 'admin' for general administration
     * @var string
     */
    private string $guiScope;

    public function __construct(
        string $moduleName,
        string $moduleBasePath,
        string $relativeClassPath = 'classes',
        string $guiScope = ''
    ) {
        $this->moduleName = $moduleName;
        $this->moduleBasePath = rtrim($moduleBasePath, '/\\');
        $this->relativeClassPath = rtrim($relativeClassPath);
        $this->guiScope = rtrim($guiScope);
    }

    /**
     * Returns an Instance of ilChatroomObjectDefinition, using given $moduleName
     * as parameter.
     * @param string $moduleName
     * @return ilChatroomObjectDefinition
     */
    public static function getDefaultDefinition(string $moduleName): self
    {
        return new self($moduleName, 'Modules/' . $moduleName . '/');
    }

    /**
     * Returns an Instance of ilChatroomObjectDefinition, using given $moduleName
     * and $guiScope as parameters.
     * @param string $moduleName
     * @param string $guiScope Optional. 'admin' or ''. Default ''
     * @return ilChatroomObjectDefinition
     */
    public static function getDefaultDefinitionWithCustomGUIPath(string $moduleName, string $guiScope = ''): self
    {
        return new self(
            $moduleName,
            'Modules/' . $moduleName . '/',
            'classes',
            $guiScope
        );
    }

    /**
     * Returns true if file exists.
     * @param string $gui
     * @return bool
     */
    public function hasGUI(string $gui): bool
    {
        return is_file($this->getGUIPath($gui));
    }

    /**
     * Builds gui path using given $gui and returns it.
     * @param string $gui
     * @return string
     */
    public function getGUIPath(string $gui): string
    {
        return (
            $this->moduleBasePath . '/' .
            $this->relativeClassPath . '/' .
            $this->guiScope . 'gui/class.' . $this->getGUIClassName($gui) . '.php'
        );
    }

    /**
     * Builds gui classname using given $gui and returns it.
     * @param string $gui
     * @return string
     */
    public function getGUIClassName(string $gui): string
    {
        return 'il' . $this->moduleName . ucfirst($this->guiScope) . ucfirst($gui) . 'GUI';
    }

    /**
     * Requires file, whereby given $gui is used as parameter in getGUIPath
     * method to build the filename of the file to required.
     * @param string $gui
     */
    public function loadGUI(string $gui): void
    {
        require_once $this->getGUIPath($gui);
    }

    /**
     * Builds and returns new gui using given $gui and $gui
     * @param string $gui
     * @param ilChatroomObjectGUI $chatroomObjectGUI
     * @return ilChatroomGUIHandler
     */
    public function buildGUI(string $gui, ilChatroomObjectGUI $chatroomObjectGUI): ilChatroomGUIHandler
    {
        $className = $this->getGUIClassName($gui);
        return new $className($chatroomObjectGUI);
    }
}
