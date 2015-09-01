<?php
require_once('./Services/Logging/classes/class.ilLog.php');
require_once('./Services/Init/classes/class.ilIniFile.php');

/**
 * Class ilWACLog
 *
 * @author      Fabian Schmid <fs@studer-raimann.ch>
 * @version     1.0.0
 *
 * @description Since the ilLoggerFactory need ILIAS to be initialized, the WebAccessChecker can't use it at the moment.
 *              Logging is disabled by default and can be activated with ilWebAccessChecker::setDEBUG(true);
 */
class ilWACLog extends ilLog {

	const WAC_LOG = 'wac.log';
	/**
	 * @var array
	 */
	protected $stack = array();
	/**
	 * @var ilWACLog[]
	 */
	protected static $instances = array();
	/**
	 * @var int
	 */
	protected $pid = 0;
	/**
	 * @var bool
	 */
	protected $is_dummy = false;


	/**
	 * @return ilWACLog
	 */
	public static function getInstance() {
		$key = getmypid();
		if (ilWebAccessChecker::isDEBUG()) {
			if (! isset(self::$instances[$key])) {
				$ilIliasIniFile = new ilIniFile('./ilias.ini.php');
				$ilIliasIniFile->read();
				//				$instance = new self($ilIliasIniFile->readVariable('log', 'path'), self::WAC_LOG);
				$instance = new self($ilIliasIniFile->readVariable('log', 'path'), $ilIliasIniFile->readVariable('log', 'file'), 'WAC');
				$instance->setPid($key);
				self::$instances[$key] = $instance;
			}
		} else {
			self::$instances[$key] = new ilWACLogDummy();
		}

		return self::$instances[$key];
	}


	public function __destruct() {
		if ($this->getStack()) {
			parent::write('WebAccessChecker Request ' . str_repeat('#', 50));
			parent::write('PID: ' . $this->getPid());
			foreach ($this->getStack() as $msg) {
				parent::write($msg);
			}
		}
	}


	/**
	 * @param      $a_msg
	 * @param null $a_log_level
	 */
	public function write($a_msg, $a_log_level = NULL) {
		$this->stack[] = $a_msg;
	}


	/**
	 * @return int
	 */
	public function getPid() {
		return $this->pid;
	}


	/**
	 * @param int $pid
	 */
	public function setPid($pid) {
		$this->pid = $pid;
	}


	/**
	 * @return array
	 */
	public function getStack() {
		return $this->stack;
	}


	/**
	 * @param array $stack
	 */
	public function setStack($stack) {
		$this->stack = $stack;
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
