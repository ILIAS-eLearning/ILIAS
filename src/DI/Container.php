<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\GlobalScreen\Services;

/**
 * Customizing of pimple-DIC for ILIAS.
 *
 * This just exposes some of the services in the container as plain methods
 * to help IDEs when using ILIAS.
 */
class Container extends \Pimple\Container
{
    /**
     * Get interface to the Database.
     *
     * @return	\ilDBInterface
     */
    public function database()
    {
        return $this["ilDB"];
    }

    /**
     * Get interface to get interfaces to all things rbac.
     *
     * @return	RBACServices
     */
    public function rbac()
    {
        return new RBACServices($this);
    }

    /**
     * Get the interface to the control structure.
     *
     * @return	\ilCtrl
     */
    public function ctrl()
    {
        return $this["ilCtrl"];
    }

    /**
     * Get the current user.
     *
     * @return	\ilObjUser
     */
    public function user()
    {
        return $this["ilUser"];
    }

    /**
     * Get interface for access checks.
     *
     * @return	\ilAccessHandler
     */
    public function access()
    {
        return $this["ilAccess"];
    }

    /**
     * Get interface to the repository tree.
     *
     * @return	\ilTree
     */
    public function repositoryTree()
    {
        return $this["tree"];
    }

    /**
     * Get interface to the i18n service.
     *
     * @return	\ilLanguage
     */
    public function language()
    {
        return $this["lng"];
    }

    /**
     * Get interface to get interfaces to different loggers.
     *
     * @return	LoggingServices
     */
    public function logger()
    {
        return new LoggingServices($this);
    }

    /**
     * Get interface to the toolbar.
     *
     * @return	\ilToolbarGUI
     */
    public function toolbar()
    {
        return $this["ilToolbar"];
    }

    /**
     * Get interface to the tabs
     *
     * @return	\ilTabsGUI
     */
    public function tabs()
    {
        return $this["ilTabs"];
    }

    /**
     * Get the interface to get services from UI framework.
     *
     * @return	UIServices
     */
    public function ui()
    {
        return new UIServices($this);
    }

    /**
     * Get the interface to the settings
     *
     * @return \ilSetting
     */
    public function settings()
    {
        return $this["ilSetting"];
    }


    /**
     * Get the Filesystem service interface.
     *
     * @return Filesystems
     */
    public function filesystem()
    {
        return $this['filesystem'];
    }


    /**
     * Gets the file upload interface.
     *
     * @return FileUpload
     */
    public function upload()
    {
        return $this['upload'];
    }


    /**
     * @return BackgroundTaskServices
     */
    public function backgroundTasks()
    {
        return new BackgroundTaskServices($this);
    }


    /**
     * @return Services
     */
    public function globalScreen()
    {
        return $this['global_screen'];
    }


    /**
     * @return HTTPServices
     */
    public function http()
    {
        return $this['http'];
    }

    /**
     * @return \ilAppEventHandler
     */
    public function event()
    {
        return $this['ilAppEventHandler'];
    }

    /**
     * @return \ilIniFile
     */
    public function iliasIni()
    {
        return $this['ilIliasIniFile'];
    }

    /**
     * @return \ilIniFile
     */
    public function clientIni()
    {
        return $this['ilClientIniFile'];
    }

    /**
     *  @return \ilStyleDefinition
     */
    public function systemStyle()
    {
        return $this['styleDefinition'];
    }

    /**
     *  @return \ilHelpGUI
     */
    public function help()
    {
        return $this['ilHelp'];
    }

    /**
     * Get conditions service
     *
     * @return	\ilConditionService
     */
    public function conditions()
    {
        return \ilConditionService::getInstance(new \ilConditionObjectAdapter());
    }

    /**
     * @return \ilLearningHistoryService
     */
    public function learningHistory()
    {
        return new \ilLearningHistoryService(
            $this->user(),
            $this->language(),
            $this->ui(),
            $this->access(),
            $this->repositoryTree()
        );
    }

    /**
     * @return \ilNewsService
     */
    public function news()
    {
        return new \ilNewsService($this->language(), $this->settings(), $this->user());
    }

    /**
     * @return \ilObjectService
     */
    public function object()
    {
        return new \ilObjectService($this->language(), $this->settings(), $this->filesystem(), $this->upload());
    }


    /**
     * Note: Only use isDependencyAvailable if strictly required. The need for this,
     * mostly points to some underlying problem needing to be solved instead of using this.
     * This was introduced as temporary workaround. See: https://github.com/ILIAS-eLearning/ILIAS/pull/1064
     *
     * This is syntactic sugar for executing the try catch statement in the clients code.
     * Note that the use of the offsetSet code of the default container should be avoided,
     * since knowledge about the containers internal mechanism is injected.
     *
     * Example:
     * //This is bad since the client should not need to know about the id's name
     * $DIC->offsetSet("styleDefinition")
     *
     * //This is better, since the client just needs to know the name defined in the
     * //interface of the component
     * $DIC->isDependencyAvailable("systemStyle")
     *
     * @param $name
     * @return bool
     */
    public function isDependencyAvailable($name)
    {
        try {
            $this->$name();
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }
}
