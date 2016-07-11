<?php
require_once('class.ilAtomQueryTestHelperSettings.php');

/**
 * Class ilAtomQueryTestHelper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAtomQueryTestHelper {

	/**
	 * @var ilAtomQueryTestHelperSettings
	 */
	protected $settings;
	/**
	 * @var int
	 */
	protected $thrown_exceptions = 0;


	/**
	 * ilAtomQueryTestHelper constructor.
	 *
	 * @param \ilAtomQueryTestHelperSettings $settings
	 */
	public function __construct(\ilAtomQueryTestHelperSettings $settings) {
		$this->settings = $settings;
	}


	/**
	 * @param \ilDBInterface $ilDB
	 */
	public function __invoke(ilDBInterface $ilDB, $options = array()) {
		if ($this->settings->getThrowExceptions() > $this->thrown_exceptions) {
			$this->throwException();
		}
		$table = $ilDB->listTables();

	}


	/**
	 * @throws \ilDatabaseException
	 */
	protected function throwException() {
		$this->thrown_exceptions ++;
		throw new ilDatabaseException('Some Random Exception');
	}
}
