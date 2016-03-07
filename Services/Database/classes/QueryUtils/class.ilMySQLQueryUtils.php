<?php
require_once 'Services/Database/interfaces/interface.ilQueryUtils.php';

/**
 * Class ilMySQLQueryUtils
 *
 */
class ilMySQLQueryUtils implements ilQueryUtils {

	/**
	 * @var ilMySQLQueryUtils
	 */
	protected static $instance = null;


	/**
	 * @return ilMySQLQueryUtils
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new \ilMySQLQueryUtils();
		}

		return self::$instance;
	}


	protected function __construct() {
	}


	/**
	 * @param string $field
	 * @param string[] $values
	 * @param bool $negate
	 * @param string $type
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = "") {
		if (count($values) == 0) {
			// BEGIN fixed mantis #0014191:
			//return " 1=2 ";		// return a false statement on empty array
			return $negate ? ' 1=1 ' : ' 1=2 ';
			// END fixed mantis #0014191:
		}
		if ($type == "")        // untyped: used ? for prepare/execute
		{
			$str = $field . (($negate) ? " NOT" : "") . " IN (?" . str_repeat(",?", count($values) - 1) . ")";
		} else                    // typed, use values for query/manipulate
		{
			$str = $field . (($negate) ? " NOT" : "") . " IN (";
			$sep = "";
			foreach ($values as $v) {
				$str .= $sep . $this->quote($v, $type);
				$sep = ",";
			}
			$str .= ")";
		}

		return $str;
	}


	/**
	 * @param $query mixed
	 * @param $type  string
	 * @return string
	 */
	public function quote($query, $type) {
		if ($type = 'text') {
			return "'$query'";
		}

		return $query;
	}
}