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
 * Class ilCertificateMigrationUIElements
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationUIElements
{
	/** @var \ilObjUser */
	protected $user;

	/** @var \ILIAS\DI\UIServices */
	protected $ui;

	/** @var \ilLanguage */
	protected $lng;

	/**
	 * ilCertificateMigrationUIElements constructor.
	 * @param \ilObjUser $user
	 * @param \ILIAS\DI\UIServices $ui
	 * @param \ilLanguage $lng
	 */
	public function __construct(\ilObjUser $user = null, \ILIAS\DI\UIServices $ui = null, \ilLanguage $lng = null)
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
		$this->user = $user;
		$this->ui = $ui;
		$this->lng = $lng;
	}

	/**
	 * Get confirmation messagebox for manual migration start
	 *
	 * @param string $link
	 * @return string
	 */
	public function getMigrationMessageBox(string $link): string
	{
		if (!\ilCertificate::isActive()) {
			return '';
		}
		if ($this->user->getPref('cert_migr_finished') === 1) {
			return '';
		}
		$migrationHelper = new \ilCertificateMigration($this->user->getId());
		if (
			$migrationHelper->isTaskRunning() ||
			$migrationHelper->isTaskFinished()
		) {
			return '';
		}

		$ui_factory = $this->ui->factory();
		$ui_renderer = $this->ui->renderer();

		$message_buttons = [
			$ui_factory->button()->standard($this->lng->txt("certificate_migration_go"), $link),
		];

		if ($migrationHelper->isTaskFailed()) {
			$messagebox = $ui_factory->messageBox()
				->failure($this->lng->txt('certificate_migration_lastrun_failed'))
				->withButtons($message_buttons);
		} else {
			$messagebox = $ui_factory->messageBox()
				->confirmation($this->lng->txt('certificate_migration_confirm_start'))
				->withButtons($message_buttons);
		}

		return $ui_renderer->render($messagebox);
	}
}