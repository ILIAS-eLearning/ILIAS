<?php
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

/**
 * Settings for the error protcoll system
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorSettings {
	protected $folder;
	protected $mail;

	protected function __construct() {
		global $ilIliasIniFile;

		$this->ilias_ini = $ilIliasIniFile;
		$this->read();
	}

	public static function getInstance() {
		return new ilLoggingErrorSettings();
	}

	public function setFolder($folder) {
		assert('is_string($folder)');
		$this->folder = $folder;
	}

	public function setMail($mail) {
		assert('is_string($mail)');
		$this->mail = $mail;
	}

	public function folder() {
		return $this->folder;
	}

	public function mail() {
		return $this->mail;
	}

	/**
	 * reads the values from ilias.ini.php
	 */
	protected function read() {
		if($this->ilias_ini->groupExists("error_log")) {
			$this->setFolder($this->ilias_ini->readVariable("error_log","folder"));
			$this->setMail($this->ilias_ini->readVariable("error_log","mail"));
		}
	}

	/**
	 * writes user entries to ilias.ini.php
	 */
	public function update() {
		$this->ilias_ini->addGroup("error_log");
		$this->ilias_ini->setVariable("error_log", "folder", trim($this->folder()));
		$this->ilias_ini->setVariable("error_log", "mail", trim($this->mail()));
		$this->ilias_ini->write();
	}
}