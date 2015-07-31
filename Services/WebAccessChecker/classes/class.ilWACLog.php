<?php
require_once('./Services/Logging/classes/class.ilLog.php');
require_once('./Services/Init/classes/class.ilIniFile.php');

/**
 * Class ilWACLog
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACLog extends ilLog {

	const WAC_LOG = 'wac.log';
	/**
	 * @var ilWACLog
	 */
	protected static $instance;


	/**
	 * @return ilWACLog
	 */
	public static function getInstance() {
		if (! isset(self::$instance)) {
			if (ilWebAccessChecker::isDEBUG()) {
				$ilIliasIniFile = new ilIniFile('./ilias.ini.php');
				$ilIliasIniFile->read();;
				self::$instance = new self($ilIliasIniFile->readVariable('clients', 'datadir'), self::WAC_LOG);
			} else {

				self::$instance = new ilWACLogDummy();
			}
		}

		return self::$instance;
	}
}

/**
 * Class ilWACLogDummy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACLogDummy {

	/**
	 * @param $dummy
	 */
	public function write($dummy) {
		unset($dummy);
	}
}

?>
