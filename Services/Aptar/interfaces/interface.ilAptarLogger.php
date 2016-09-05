<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarLogger
 */
interface ilAptarLogger
{
	/**
	 * @const int defined from the BSD Syslog message severities
	 * @link http://tools.ietf.org/html/rfc3164
	 */
	const EMERG  = 0;
	const ALERT  = 1;
	const CRIT   = 2;
	const ERR    = 3;
	const WARN   = 4;
	const NOTICE = 5;
	const INFO   = 6;
	const DEBUG  = 7;

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function emerg($message, $extra = array());

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function alert($message, $extra = array());

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function crit($message, $extra = array());

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function err($message, $extra = array());
	
	/**
	 * @param string $message
	 * @param array
	 * @return void
	 */
	public function info($message, $extra = array());

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function warn($message, $extra = array());

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function notice($message, $extra = array());

	/**
	 * @param string $message
	 * @param array $extra
	 * @return void
	 */
	public function debug($message, $extra = array());
}