<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailAddressType
{
	/**
	 * @var ilMailAddress
	 */
	protected $address;

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * ilMailAddressType constructor.
	 * @param ilMailAddress $a_address
	 */
	public function __construct(ilMailAddress $a_address)
	{
		$this->address = $a_address;
		$this->init();
	}

	/**
	 * 
	 */
	protected function init()
	{
	}

	/**
	 * @param $a_sender_id integer
	 * @return boolean
	 */
	abstract protected function isValid($a_sender_id);

	/**
	 * Returns an array of resolbed user ids
	 * @return int[]
	 */
	abstract public function resolve();

	/**
	 * @param $a_sender_id integer
	 * @return bool
	 */
	public function validate($a_sender_id)
	{
		$this->resetErrors();
		return $this->isValid($a_sender_id);
	}

	/**
	 * 
	 */
	private function resetErrors()
	{
		$this->errors = array();
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}