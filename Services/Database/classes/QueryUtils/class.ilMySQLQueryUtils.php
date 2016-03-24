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


	/**
	 * @param array $values
	 * @param bool $allow_null
	 * @return string
	 */
	public function concat(array $values, $allow_null = true) {
		if (!count($values)) {
			return ' ';
		}

		$concat = ' CONCAT(';
		$first = true;
		foreach ($values as $field_info) {
			$val = $field_info[0];

			if (!$first) {
				$concat .= ',';
			}

			if ($allow_null) {
				$concat .= 'COALESCE(';
			}
			$concat .= $val;

			if ($allow_null) {
				$concat .= ",''";
				$concat .= ')';
			}

			$first = false;
		}
		$concat .= ') ';

		return $concat;
	}


	/**
	 * @param $a_needle
	 * @param $a_string
	 * @param int $a_start_pos
	 * @return string
	 */
	public function locate($a_needle, $a_string, $a_start_pos = 1) {
		$locate = ' LOCATE( ';
		$locate .= $a_needle;
		$locate .= ',';
		$locate .= $a_string;
		$locate .= ',';
		$locate .= $a_start_pos;
		$locate .= ') ';

		return $locate;
	}


	/**
	 * @param \ilPDOStatement $statement
	 * @return bool
	 */
	public function free(ilPDOStatement $statement) {
		$statement->closeCursor();
		return true;
	}
}