<?php
/*
  +----------------------------------------------------------------------------+
  | ILIAS open source                                                          |
  +----------------------------------------------------------------------------+
  | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
  |                                                                            |
  | This program is free software; you can redistribute it and/or              |
  | modify it under the terms of the GNU General Public License                |
  | as published by the Free Software Foundation; either version 2             |
  | of the License, or (at your option) any later version.                     |
  |                                                                            |
  | This program is distributed in the hope that it will be useful,            |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
  | GNU General Public License for more details.                               |
  |                                                                            |
  | You should have received a copy of the GNU General Public License          |
  | along with this program; if not, write to the Free Software                |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
  +----------------------------------------------------------------------------+
*/

/**
 * Class ilCertificateMigrationGUI
 * @author Ralph Dittrich <dittrich@qualitus.de>
 *
 * @ilCtrl_IsCalledBy ilCertificateMigrationGUI: ilCertificateGUI, ilPersonalProfileGUI
 *
 */
class ilCertificateMigrationGUI
{

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var \ilLanguage */
    protected $lng;

    /** @var ilAccessHandler */
    protected $access;

    /** @var \ilTemplate */
    protected $tpl;

    /**
     * ilCertificateMigrationGUI constructor.
     * @param \ilCtrl $ctrl
     * @param \ilLanguage $lng
     * @param \ilAccessHandler $acces
     */
    public function __construct(\ilCtrl $ctrl = null, \ilLanguage $lng = null, \ilAccessHandler $access = null)
    {
        global $DIC;

        if (null === $ctrl) {
            $this->ctrl = $DIC->ctrl();
        }
        if (null === $lng) {
            $this->lng = $DIC->language();
        }
        if (null === $access) {
            $this->access = $DIC->access();
        }
        $this->ctrl = $ctrl;
        $lng->loadLanguageModule('cert');
        $this->lng = $lng;
        $this->access = $access;
    }

    /**
     * execute command
     * @return mixed
     */
    function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->getCommand($cmd);
        switch($next_class)
        {
            default:
                $ret = $this->$cmd();
                break;
        }
        return $ret;
    }

    /**
     * Retrieves the ilCtrl command
     * @param string $cmd
     * @return mixed
     */
    public function getCommand($cmd)
    {
        return $cmd;
    }

    /**
     * @param \ILIAS\DI\BackgroundTaskServices $backgroundTasks
     * @param ilObjUser $user
     * @return string
     */
    public function startMigration(\ILIAS\DI\BackgroundTaskServices $backgroundTasks = null, \ilObjUser $user = null)
    {
        global $DIC;

        if (null === $backgroundTasks) {
            $factory = $DIC->backgroundTasks()->taskFactory();
            $taskManager = $DIC->backgroundTasks()->taskManager();
        } else {
            $factory = $backgroundTasks->taskFactory();
            $taskManager = $backgroundTasks->taskManager();
        }
        if (null === $user) {
            $user = $DIC->user();
        }

        $bucket = new \ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket();
        $bucket->setUserId($user->getId());

        $task = $factory->createTask(\ilCertificateMigrationJob::class, [(int)$user->getId()]);

        $bucket->setTask($task);
        $bucket->setTitle('Certificate Migration');
        $bucket->setDescription('Migrates certificates for active user');

        $taskManager->run($bucket);

        return $this->lng->txt('certificate_migration_confirm_started');
    }

    /**
     * Get confirmation messagebox for manual migration start
     *
     * @param string $link
     * @param ilObjUser $user
     * @param \ILIAS\DI\UIServices $ui
     * @param ilLanguage $lng
     * @return string
     */
    static function getMigrationMessageBox(string $link, \ilObjUser $user = null, \ILIAS\DI\UIServices $ui = null, \ilLanguage $lng = null)
    {
        global $DIC;

        if (null === $user) {
            $user = $DIC->user();
        }
        if (null === $ui) {
            $ui = $DIC->ui();
        }
        if (null === $lng) {
            $lng = $DIC->language();
        }
        $lng->loadLanguageModule('cert');

        if (!\ilCertificate::isActive()) {
            return '';
        }
        if ($user->getPref('cert_migr_finished') === 1) {
            return '';
        }
        $migrationHelper = new \ilCertificateMigration($user->getId());
        if (
            $migrationHelper->isTaskRunning() ||
            $migrationHelper->isTaskFinished()
        ) {
            return '';
        }

        $ui_factory = $ui->factory();
        $ui_renderer = $ui->renderer();

        $message_buttons = [
            $ui_factory->button()->standard($lng->txt("certificate_migration_go"), $link),
        ];

        if ($migrationHelper->isTaskFailed()) {
            $messagebox = $ui_factory->messageBox()
                ->failure($lng->txt('certificate_migration_lastrun_failed'))
                ->withButtons($message_buttons);
        } else {
            $messagebox = $ui_factory->messageBox()
                ->confirmation($lng->txt('certificate_migration_confirm_start'))
                ->withButtons($message_buttons);
        }

        return $ui_renderer->render($messagebox);
    }

}