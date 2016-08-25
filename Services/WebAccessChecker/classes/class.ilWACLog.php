<?php
require_once('./Services/Logging/classes/class.ilLog.php');
require_once('./Services/Init/classes/class.ilIniFile.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACLogDummy.php');

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
		if (ilWebAccessChecker::isDEBUG() && defined('ILIAS_ABSOLUTE_PATH')) {
			$ilIliasIniFile = new ilIniFile('./ilias.ini.php');
			$ilIliasIniFile->read();
			if (ilWebAccessChecker::isUseSeperateLogfile()) {
				$instance = new self($ilIliasIniFile->readVariable('log', 'path'), self::WAC_LOG, 'WAC');
			} else {
				$instance = new self($ilIliasIniFile->readVariable('log', 'path'), $ilIliasIniFile->readVariable('log', 'file'), 'WAC');
			}
			$instance->setPid($key);
			self::$instances[$key] = $instance;
		} else {
			self::$instances[$key] = new ilWACLogDummy();
		}

		return self::$instances[$key];
	}


	public function __destruct() {
		if ($this->getStack()) {
			global $ilUser;
			parent::write('WebAccessChecker Request ' . str_repeat('#', 50));
			parent::write('PID: ' . $this->getPid());
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				parent::write('User-Agent: ' . $_SERVER['HTTP_USER_AGENT']);
			}
			if (isset($_SERVER['HTTP_COOKIE'])) {
				parent::write('Cookies: ' . $_SERVER['HTTP_COOKIE']);
			}
			if ($ilUser instanceof ilObjUser) {
				parent::write('User_ID: ' . $ilUser->getId());
			}
			//			parent::write('SERVER: ' . print_r($_SERVER, true));
			foreach ($this->getStack() as $msg) {
				parent::write($msg);
			}
		}
	}


	/**
	 * @param      $a_msg
	 * @param null $a_log_level
	 */
	public function write($a_msg, $a_log_level = null) {
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

