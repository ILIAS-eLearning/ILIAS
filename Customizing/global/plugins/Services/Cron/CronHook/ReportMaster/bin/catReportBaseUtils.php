<?php
/**
 * Utils for Reports
 */
class catReportBaseUtils {
	public static function checkForURLPrefix($string) {
		assert('is_string($string)');
		assert('$string !== ""');
		$reg_exp = "/^(https:\/\/)|(http:\/\/)[\w]+/";

		return preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1;
	}
}