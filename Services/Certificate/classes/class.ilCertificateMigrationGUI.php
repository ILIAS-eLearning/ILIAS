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
 * @ilCtrl_IsCalledBy ilCertificateMigrationGUI: ilPersonalProfileGUI, ilUserCertificateGUI
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

	/** @var \ilObjUser */
	protected $user;

	/** @var \ILIAS\DI\BackgroundTaskServices */
	protected $backgroundTasks;

	/**
	 * ilCertificateMigrationGUI constructor.
	 * @param \ilCtrl $ctrl
	 * @param \ilLanguage $lng
	 * @param \ilAccessHandler $acces
	 * @param \ILIAS\DI\BackgroundTaskServices $backgroundTasks
	 * @param \ilObjUser $user
	 */
	public function __construct(\ilCtrl $ctrl = null, \ilLanguage $lng = null, \ilAccessHandler $access = null, \ILIAS\DI\BackgroundTaskServices $backgroundTasks = null, \ilObjUser $user = null)
	{
		global $DIC;

		if (null === $ctrl) {
			$ctrl = $DIC->ctrl();
		}
		if (null === $lng) {
			$lng = $DIC->language();
		}
		if (null === $access) {
			$access = $DIC->access();
		}

		if (null === $backgroundTasks) {
			$backgroundTasks = $DIC->backgroundTasks();
		}
		if (null === $user) {
			$user = $DIC->user();
		}

		$this->ctrl = $ctrl;
		$lng->loadLanguageModule('cert');
		$this->lng = $lng;
		$this->access = $access;
		$this->user = $user;
		$this->backgroundTasks = $backgroundTasks;
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
	public function getCommand(string $cmd)
	{
		return $cmd;
	}

	/**
	 * @return string
	 */
	public function startMigration(): string
	{
		$factory = $this->backgroundTasks->taskFactory();
		$taskManager = $this->backgroundTasks->taskManager();

		$bucket = new \ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket();
		$bucket->setUserId($this->user->getId());

		$task = $factory->createTask(\ilCertificateMigrationJob::class, [(int)$this->user->getId()]);

		$certificates_interaction = $factory->createTask(ilCertificateMigrationInteraction::class, [
			$task,
			(int)$this->user->getId()
		]);

		$bucket->setTask($certificates_interaction);
		$bucket->setTitle('Certificate Migration');
		$bucket->setDescription('Migrates certificates for active user');

		$taskManager->run($bucket);

		return $this->lng->txt('certificate_migration_confirm_started');
	}
}