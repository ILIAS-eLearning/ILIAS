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
 ********************************************************************
 */

use ILIAS\GlobalScreen\Provider\PluginProviderCollection;
use ILIAS\GlobalScreen\Provider\ProviderCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ILIAS\Setup\Environment;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Data\Version;

/**
 * @author   Richard Klees <richard.klees@concepts-and-training.de>
 * @author   Alex Killing <alex.killing@gmx.de>
 * @author   Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilPlugin
{
    protected ilDBInterface $db;
    protected ilComponentRepositoryWrite $component_repository;
    protected string $id;
    protected ?ilPluginLanguage $language_handler = null;
    protected bool $lang_initialised = false;
    protected ProviderCollection $provider_collection;
    protected string $message = '';

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    // ------------------------------------------
    // Initialisation
    // ------------------------------------------

    public function __construct(
        \ilDBInterface $db,
        \ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        if (!$component_repository->hasPluginId($id)) {
            throw new \LogicException(
                "You tried to instantiate a plugin with an inexisting id '$id'." .
                "This is odd... Please use ilComponentFactory to instantiate plugins."
            );
        }

        $this->db = $db;
        $this->component_repository = $component_repository;
        $this->id = $id;

        $this->provider_collection = new PluginProviderCollection();

        // Fix for authentication plugins
        $this->loadLanguageModule();

        // Custom initialisation for plugin.
        $this->init();
    }

    /**
     * Object initialization. Can be overwritten by plugin class
     * (and should be made protected)
     *
     * TODO: Maybe this should be removed or be documented better. This is called
     * during __construct. If this contains expensive stuff this will be bad for
     * overall system performance, because Plugins tend to be constructed a lot.
     */
    protected function init() : void
    {
    }


    // ------------------------------------------
    // General Information About Plugin
    // ------------------------------------------

    public function getPluginName() : string
    {
        return $this->getPluginInfo()->getName();
    }

    public function getId() : string
    {
        return $this->getPluginInfo()->getId();
    }

    /**
     * Only very little classes seem to care about this:
     *     - Services/COPage/classes/class.ilPCPluggedGUI.php
     *
     * @return string
     */
    public function getVersion() : string
    {
        return (string) $this->getPluginInfo()->getAvailableVersion();
    }

    /**
     * Only very little classes seem to care about this:
     *     - Services/COPage/classes/class.ilPCPlugged.php
     *     - Modules/DataCollection/classes/Fields/class.ilDclFieldFactory.php
     */
    public function getDirectory() : string
    {
        return $this->getPluginInfo()->getPath();
    }

    /**
     * Only very little classes seem to care about this:
     *    - Services/Component/classes/class.ilObjComponentSettingsGUI.php
     *    - Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php
     */
    public function isActive() : bool
    {
        return $this->getPluginInfo()->isActive();
    }

    public function needsUpdate() : bool
    {
        return $this->getPluginInfo()->isUpdateRequired();
    }

    protected function getPluginInfo() : ilPluginInfo
    {
        return $this->component_repository
            ->getPluginById(
                $this->id
            );
    }

    protected function getComponentInfo() : ilComponentInfo
    {
        return $this->getPluginInfo()->getComponent();
    }

    protected function getPluginSlotInfo() : ilPluginSlotInfo
    {
        return $this->getPluginInfo()->getPluginSlot();
    }


    // ------------------------------------------
    // (De-)Installation
    // ------------------------------------------

    public function install() : void
    {
        $this->afterInstall();
    }

    public function uninstall() : bool
    {
        if (!$this->beforeUninstall()) {
            return false;
        }

        $this->getLanguageHandler()->uninstall();
        $this->clearEventListening();
        $this->component_repository->removeStateInformationOf($this->getId());
        $this->afterUninstall();
        return true;
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            install method in your subclass instead.
     */
    protected function afterInstall() : void
    {
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            uninstall method in your subclass instead.
     */
    protected function beforeUninstall() : bool
    {
        return true;
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            uninstall method in your subclass instead.
     */
    protected function afterUninstall() : void
    {
    }


    // ------------------------------------------
    // (De-)Activation
    // ------------------------------------------

    /**
     * This will update (if required) and activate the plugin.
     */
    public function activate() : bool
    {
        if ($this->needsUpdate() && !$this->update()) {
            return false;
        }

        if (!$this->beforeActivation()) {
            return false;
        }

        $this->component_repository->setActivation($this->getId(), true);
        $this->afterActivation();

        return true;
    }

    public function deactivate() : bool
    {
        $this->component_repository->setActivation($this->getId(), false);
        $this->afterDeactivation();

        return true;
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            activate method in your subclass instead.
     */
    protected function beforeActivation() : bool
    {
        return true;
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            activate method in your subclass instead.
     */
    protected function afterActivation() : void
    {
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            activate method in your subclass instead.
     */
    protected function afterDeactivation() : void
    {
    }


    // ------------------------------------------
    // Update
    // ------------------------------------------

    public function update() : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $this->beforeUpdate();
        if ($result === false) {
            return false;
        }

        // Load language files
        $this->getLanguageHandler()->updateLanguages();

        // DB update
        $db_version = $this->updateDatabase();

        $this->readEventListening();

        $this->readEventListening();

        // set last update version to current version
        $this->component_repository->setCurrentPluginVersion(
            $this->getPluginInfo()->getId(),
            $this->getPluginInfo()->getAvailableVersion(),
            $db_version
        );
        $this->afterUpdate();

        return true;
    }

    protected function updateDatabase() : int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $dbupdate = new ilPluginDBUpdate(
            $this->db,
            $this->getPluginInfo()
        );

        $dbupdate->applyUpdate();

        return $dbupdate->getCurrentVersion();
    }

    /**
     * @deprecate If you cannot get rid of the requirement to use this, adjust the
     *            update method in your subclass instead.
     */
    protected function beforeUpdate() : bool
    {
        return true;
    }


    protected function afterUpdate() : void
    {
    }


    // ------------------------------------------
    // Language Handling
    // ------------------------------------------

    protected function getLanguageHandler() : ilPluginLanguage
    {
        if ($this->language_handler === null) {
            $this->language_handler = $this->buildLanguageHandler();
        }
        return $this->language_handler;
    }

    protected function buildLanguageHandler() : ilPluginLanguage
    {
        return new ilPluginLanguage($this->getPluginInfo());
    }

    /**
     * Load language module for plugin
     *
     * @deprecate Just use `txt`, this will automatically load the language module.
     */
    public function loadLanguageModule() : void
    {
        $this->getLanguageHandler()->loadLanguageModule();
    }

    /**
     * Get Language Variable (prefix will be prepended automatically)
     */
    public function txt(string $a_var) : string
    {
        return $this->getLanguageHandler()->txt($a_var);
    }


    // ------------------------------------------
    // Rendering and Style
    // ------------------------------------------

    /**
     * @deprecate ILIAS is moving towards UI components and plugins are expected
     *            to use these components. Hence, this method will be removed.
     */
    public function getTemplate(string $a_template, bool $a_par1 = true, bool $a_par2 = true) : ilTemplate
    {
        return new ilTemplate($this->getDirectory() . "/templates/" . $a_template, $a_par1, $a_par2);
    }

    /**
     * @deprecate ILIAS is moving towards UI components and plugins are expected
     *            to use these components. Hence, this method will be removed.
     */
    public function getStyleSheetLocation(string $a_css_file) : string
    {
        $d2 = $this->getComponentInfo()->getId() . "_" . $this->getPluginSlotInfo()->getId() . "_" . $this->getPluginInfo()->getId();

        $css = ilUtil::getStyleSheetLocation("output", $a_css_file, $d2);
        if (is_int(strpos($css, "Customizing"))) {
            return $css;
        }

        return $this->getDirectory() . "/templates/" . $a_css_file;
    }


    /**
     * @deprecate ILIAS is moving towards UI components and plugins are expected
     *            to use these components. Hence, this method will be removed.
     */
    public function addBlockFile($a_tpl, $a_var, $a_block, $a_tplname) : void
    {
        $a_tpl->addBlockFile(
            $a_var,
            $a_block,
            $this->getDirectory() . "/templates/" . $a_tplname
        );
    }


    // ------------------------------------------
    // Event Handling
    // ------------------------------------------

    protected function readEventListening() : void
    {
        $reader = new ilPluginReader(
            $this->getDirectory() . '/plugin.xml',
            $this->getComponentInfo()->getType(),
            $this->getComponentInfo()->getName(),
            $this->getPluginSlotInfo()->getId(),
            $this->getPluginInfo()->getName()
        );
        $reader->clearEvents();
        $reader->startParsing();
    }

    protected function clearEventListening() : void
    {
        $reader = new ilPluginReader(
            $this->getDirectory() . '/plugin.xml',
            $this->getComponentInfo()->getType(),
            $this->getComponentInfo()->getName(),
            $this->getPluginSlotInfo()->getId(),
            $this->getPluginInfo()->getName()
        );
        $reader->clearEvents();
    }


    // ------------------------------------------
    // Global Screen
    // ------------------------------------------

    final public function getGlobalScreenProviderCollection() : PluginProviderCollection
    {
        return $this->provider_collection;
    }


    // ------------------------------------------
    // Initialisation
    // ------------------------------------------

    /**
     * This methods allows to replace the UI Renderer (see src/UI) of ILIAS after initialization
     * by returning a closure returning a custom renderer. E.g:
     *
     * return function(\ILIAS\DI\Container $c){
     *   return new CustomRenderer();
     * };
     *
     * Note: Note that plugins might conflict by replacing the renderer, so only use if you
     * are sure, that no other plugin will do this for a given context.
     */
    public function exchangeUIRendererAfterInitialization(\ILIAS\DI\Container $dic) : Closure
    {
        //This returns the callable of $c['ui.renderer'] without executing it.
        return $dic->raw('ui.renderer');
    }


    /**
     * This methods allows to replace some factory for UI Components (see src/UI) of ILIAS
     * after initialization by returning a closure returning a custom factory. E.g:
     *
     * if($key == "ui.factory.nameOfFactory"){
     *    return function(\ILIAS\DI\Container  $c){
     *       return new CustomFactory($c['ui.signal_generator'],$c['ui.factory.maincontrols.slate']);
     *    };
     * }
     *
     * Note: Note that plugins might conflict by replacing the same factory, so only use if you
     * are sure, that no other plugin will do this for a given context.
     */
    public function exchangeUIFactoryAfterInitialization(string $dic_key, \ILIAS\DI\Container $dic) : Closure
    {
        //This returns the callable of $c[$key] without executing it.
        return $dic->raw($dic_key);
    }
}
