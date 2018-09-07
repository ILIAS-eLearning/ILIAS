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

include_once("./Services/Certificate/classes/class.ilCertificate.php");
include_once("./Services/Certificate/classes/Migration/class.ilCertificateMigration.php");
include_once("./Services/Certificate/classes/BackgroundTasks/class.ilCertificateMigrationJob.php");

/**
 * Class ilCertificateMigrationGUI
 * @author Ralph Dittrich <dittrich@qualitus.de>
 *
 * @ilCtrl_IsCalledBy ilCertificateMigrationGUI: ilCertificateGUI, ilPersonalProfileGUI
 *
 */
class ilCertificateMigrationGUI
{

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilLanguage */
    protected $lng;

    /** @var ilAccessHandler */
    protected $access;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * ilCertificateMigrationGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
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
     * @return string
     */
    public function startMigration()
    {
        global $DIC;

        $factory = $DIC->backgroundTasks()->taskFactory();
        $taskManager = $DIC->backgroundTasks()->taskManager();

        $bucket = new \ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket();
        $bucket->setUserId($DIC->user()->getId());

        $task = $factory->createTask(\ilCertificateMigrationJob::class, [(int)$DIC->user()->getId()]);

        $bucket->setTask($task);
        $bucket->setTitle('Certificate Migration');
        $bucket->setDescription('Migrates certificates for active user');

        $taskManager->run($bucket);

        return $DIC->language()->txt('certificate_migration_confirm_started');
    }

    /**
     * Get confirmation messagebox for manual migration start
     * @return string
     */
    static function getMigrationMessageBox($link)
    {
        global $DIC;

        if (!\ilCertificate::isActive()) {
            return '';
        }
        $migrationHelper = new \ilCertificateMigration($DIC->user()->getId());
        if (
            $migrationHelper->isTaskRunning() ||
            $migrationHelper->isTaskFinished()
        ) {
            return '';
        }

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $message_buttons = [
            $ui_factory->button()->standard($DIC->language()->txt("certificate_migration_go"), $link),
        ];

        if ($migrationHelper->isTaskFailed()) {
            $messagebox = $ui_factory->messageBox()
                ->failure($DIC->language()->txt('certificate_migration_lastrun_failed'))
                ->withButtons($message_buttons);
        } else {
            $messagebox = $ui_factory->messageBox()
                ->confirmation($DIC->language()->txt('certificate_migration_confirm_start'))
                ->withButtons($message_buttons);
        }

        return $ui_renderer->render($messagebox);

    }

}