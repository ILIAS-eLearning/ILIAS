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

namespace ILIAS\DI;

use ILIAS\BackgroundTasks\BackgroundTaskServices;
use ILIAS\Repository;
use ILIAS\Skill\Service\SkillService;

/**
 * Customizing of pimple-DIC for ILIAS.
 *
 * This just exposes some services in the container as plain methods
 * to help IDEs when using ILIAS.
 */
class Container extends \Pimple\Container
{
    private ?\ilFileServicesSettings $file_service_settings = null;

    /**
     * Get interface to the Database.
     */
    public function database(): \ilDBInterface
    {
        return $this["ilDB"];
    }

    /**
     * Get interface to get interfaces to all things rbac.
     */
    public function rbac(): \ILIAS\DI\RBACServices
    {
        return new RBACServices($this);
    }

    /**
     * Get the interface to the control structure.
     */
    public function ctrl(): \ilCtrlInterface
    {
        return $this["ilCtrl"];
    }

    /**
     * Get the current user.
     */
    public function user(): \ilObjUser
    {
        return $this["ilUser"];
    }

    /**
     * Get interface for access checks.
     */
    public function access(): \ilAccessHandler
    {
        return $this["ilAccess"];
    }

    /**
     * Get interface to the repository tree.
     */
    public function repositoryTree(): \ilTree
    {
        return $this["tree"];
    }

    /**
     * Get interface to the i18n service.
     */
    public function language(): \ilLanguage
    {
        return $this["lng"];
    }

    /**
     * Get interface to get interfaces to different loggers.
     */
    public function logger(): \ILIAS\DI\LoggingServices
    {
        return new LoggingServices($this);
    }

    /**
     * Get interface to the toolbar.
     */
    public function toolbar(): \ilToolbarGUI
    {
        return $this["ilToolbar"];
    }

    /**
     * Get interface to the tabs
     */
    public function tabs(): \ilTabsGUI
    {
        return $this["ilTabs"];
    }

    /**
     * Get the interface to get services from UI framework.
     */
    public function ui(): \ILIAS\DI\UIServices
    {
        return new UIServices($this);
    }

    /**
     * Get the interface to the settings
     */
    public function settings(): \ilSetting
    {
        return $this["ilSetting"];
    }


    /**
     * Get the Filesystem service interface.
     */
    public function filesystem(): \ILIAS\Filesystem\Filesystems
    {
        return $this['filesystem'];
    }


    /**
     * Gets the file upload interface.
     */
    public function upload(): \ILIAS\FileUpload\FileUpload
    {
        return $this['upload'];
    }

    public function backgroundTasks(): BackgroundTaskServices
    {
        return new BackgroundTaskServices($this);
    }


    public function globalScreen(): \ILIAS\GlobalScreen\Services
    {
        return $this['global_screen'];
    }


    /**
     * @return \ILIAS\HTTP\Services
     */
    public function http(): \ILIAS\HTTP\Services
    {
        return $this['http'];
    }

    public function event(): \ilAppEventHandler
    {
        return $this['ilAppEventHandler'];
    }

    public function iliasIni(): \ilIniFile
    {
        return $this['ilIliasIniFile'];
    }

    public function clientIni(): \ilIniFile
    {
        return $this['ilClientIniFile'];
    }

    public function systemStyle(): \ilStyleDefinition
    {
        return $this['styleDefinition'];
    }

    public function help(): \ilHelpGUI
    {
        return $this['ilHelp'];
    }

    public function question(): \ilAsqFactory
    {
        return new \ilAsqFactory();
    }

    /**
     * Get conditions service
     */
    public function conditions(): \ilConditionService
    {
        return \ilConditionService::getInstance(new \ilConditionObjectAdapter());
    }

    public function learningHistory(): \ilLearningHistoryService
    {
        return new \ilLearningHistoryService(
            $this->user(),
            $this->language(),
            $this->ui(),
            $this->access(),
            $this->repositoryTree()
        );
    }

    public function news(): \ilNewsService
    {
        return new \ilNewsService($this->language(), $this->settings(), $this->user());
    }

    public function object(): \ilObjectService
    {
        return new \ilObjectService($this->language(), $this->settings(), $this->filesystem(), $this->upload());
    }

    public function exercise(): \ILIAS\Exercise\Service
    {
        return new \ILIAS\Exercise\Service();
    }

    public function task(): \ilTaskService
    {
        return new \ilTaskService($this->user(), $this->language(), $this->ui(), $this->access());
    }


    public function refinery(): \ILIAS\Refinery\Factory
    {
        return $this['refinery'];
    }


    public function uiService(): \ilUIService
    {
        return new \ilUIService($this->http()->request(), $this->ui());
    }


    public function bookingManager(): \ILIAS\BookingManager\Service
    {
        return new \ILIAS\BookingManager\Service($this);
    }

    public function skills(): \ILIAS\Skill\Service\SkillService
    {
        return new SkillService();
    }

    public function resourceStorage(): \ILIAS\ResourceStorage\Services
    {
        return $this['resource_storage'];
    }

    public function repository(): Repository\Service
    {
        return new Repository\Service($this);
    }

    public function container(): \ILIAS\Container\Service
    {
        return new \ILIAS\Container\Service($this);
    }

    public function containerReference(): \ILIAS\ContainerReference\Service
    {
        return new \ILIAS\ContainerReference\Service($this);
    }

    public function category(): \ILIAS\Category\Service
    {
        return new \ILIAS\Category\Service($this);
    }

    public function folder(): \ILIAS\Folder\Service
    {
        return new \ILIAS\Folder\Service($this);
    }

    public function rootFolder(): \ILIAS\RootFolder\Service
    {
        return new \ILIAS\RootFolder\Service($this);
    }

    public function copage(): \ILIAS\COPage\Service
    {
        return new \ILIAS\COPage\Service($this);
    }

    public function learningModule(): \ILIAS\LearningModule\Service
    {
        return new \ILIAS\LearningModule\Service($this);
    }

    public function wiki(): \ILIAS\Wiki\Service
    {
        return new \ILIAS\Wiki\Service($this);
    }

    public function mediaObjects(): \ILIAS\MediaObjects\Service
    {
        return new \ILIAS\MediaObjects\Service($this);
    }

    public function survey(): \ILIAS\Survey\Service
    {
        return new \ILIAS\Survey\Service();
    }

    public function surveyQuestionPool(): \ILIAS\SurveyQuestionPool\Service
    {
        return new \ILIAS\SurveyQuestionPool\Service($this);
    }

    public function test(): \ILIAS\Test\Service
    {
        return new \ILIAS\Test\Service($this);
    }

    public function testQuestionPool(): \ILIAS\TestQuestionPool\Service
    {
        return new \ILIAS\TestQuestionPool\Service($this);
    }

    public function workflowEngine(): \ILIAS\WorkflowEngine\Service
    {
        return new \ILIAS\WorkflowEngine\Service($this);
    }

    public function mediaPool(): \ILIAS\MediaPool\Service
    {
        return new \ILIAS\MediaPool\Service($this);
    }

    public function notes(): \ILIAS\Notes\Service
    {
        return new \ILIAS\Notes\Service($this);
    }

    public function glossary(): \ILIAS\Glossary\Service
    {
        return new \ILIAS\Glossary\Service($this);
    }

    public function portfolio(): \ILIAS\Portfolio\Service
    {
        return new \ILIAS\Portfolio\Service($this);
    }

    public function blog(): \ILIAS\Blog\Service
    {
        return new \ILIAS\Blog\Service($this);
    }

    public function mediaCast(): \ILIAS\MediaCast\Service
    {
        return new \ILIAS\MediaCast\Service($this);
    }

    public function itemGroup(): \ILIAS\ItemGroup\Service
    {
        return new \ILIAS\ItemGroup\Service($this);
    }

    public function htmlLearningModule(): \ILIAS\HTMLLearningModule\Service
    {
        return new \ILIAS\HTMLLearningModule\Service($this);
    }

    public function awareness(): \ILIAS\Awareness\Service
    {
        return new \ILIAS\Awareness\Service($this);
    }

    public function fileServiceSettings(): \ilFileServicesSettings
    {
        if ($this->file_service_settings === null) {
            $this->file_service_settings = new \ilFileServicesSettings(
                $this->settings(),
                $this->clientIni(),
                $this->database()
            );
        }
        return $this->file_service_settings;
    }

    public function contentStyle(): \ILIAS\Style\Content\Service
    {
        return new \ILIAS\Style\Content\Service($this);
    }

    public function notifications(): \ILIAS\Notifications\Service
    {
        return new \ILIAS\Notifications\Service($this);
    }

    public function cron(): \ilCronServices
    {
        return new \ilCronServicesImpl($this);
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
     */
    public function isDependencyAvailable(string $name): bool
    {
        try {
            $this->$name();
        } catch (\InvalidArgumentException $e) {
            return false;
        } catch (\TypeError $e) {
            return false;
        }

        return true;
    }
}
