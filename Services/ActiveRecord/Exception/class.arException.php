<?php

/**
 * Class arException
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 *
 * @version 2.0.7
 */
class arException extends Exception {

	const UNKNONWN_EXCEPTION = - 1;
	const COLUMN_DOES_NOT_EXIST = 1001;
	const COLUMN_DOES_ALREADY_EXIST = 1002;
	const RECORD_NOT_FOUND = 1003;
	const GET_UNCACHED_OBJECT = 1004;
	const LIST_WRONG_LIMIT = 1005;
	const LIST_ORDER_BY_WRONG_FIELD = 1006;
	const LIST_JOIN_ON_WRONG_FIELD = 1007;
	const COPY_DESTINATION_ID_EXISTS = 1008;
	const PRIVATE_CONTRUCTOR = 1009;
	const FIELD_UNKNOWN = 1010;
	/**
	 * @var array
	 */
	protected static $message_strings = array(
		self::UNKNONWN_EXCEPTION => 'Unknown Exception',
		self::COLUMN_DOES_NOT_EXIST => 'Column does not exist:',
		self::COLUMN_DOES_ALREADY_EXIST => 'Column does already exist:',
		self::RECORD_NOT_FOUND => 'No Record found with PrimaryKey:',
		self::GET_UNCACHED_OBJECT => 'Get uncached Object from Cache:',
		self::LIST_WRONG_LIMIT => 'Limit, to value smaller than from value:',
		self::LIST_JOIN_ON_WRONG_FIELD => 'Join on non existing field: ',
		self::COPY_DESTINATION_ID_EXISTS => 'Copy Record: A record with the Destination-ID already exists.',
		self::PRIVATE_CONTRUCTOR => 'Constructor cannot be accessed.',
		self::FIELD_UNKNOWN => 'Field Unknown.',
	);
	/**
	 * @var string
	 */
	protected $message = '';
	/**
	 * @var int
	 */
	protected $code = self::UNKNONWN_EXCEPTION;
	/**
	 * @var string
	 */
	protected $additional_info = '';


	/**
	 * @param int    $exception_code
	 * @param string $additional_info
	 */
	public function __construct($exception_code = self::UNKNONWN_EXCEPTION, $additional_info = '') {
		$this->code = $exception_code;
		$this->additional_info = $additional_info;
		$this->assignMessageToCode();
		parent::__construct($this->message, $this->code);
	}


	protected function assignMessageToCode() {
		$this->message = 'ActiveRecord Exeption: ' . self::$message_strings[$this->code] . $this->additional_info;
	}


	/**
	 * @return string
	 */
	public function __toString() {
		return implode('<br>', array( get_class($this), $this->message ));
	}
}

?>
