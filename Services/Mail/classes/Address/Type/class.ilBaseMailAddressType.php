<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/interfaces/interface.ilMailAddressType.php';

/**
 * Class ilBaseMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailAddressType implements ilMailAddressType
{
	/**
	 * @var \ilMailAddress
	 */
	protected $address;

	/**
	 * @var \ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var \ilRbacReview
	 */
	protected $rbacreview;

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * ilBaseMailAddressType constructor.
	 * @param \ilMailAddress $a_address
	 */
	public function __construct(\ilMailAddress $a_address)
	{
		global $DIC;

		$this->rbacsystem = $DIC->rbac()->system();
		$this->rbacreview = $DIC->rbac()->review();

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
	 * {@inheritdoc}
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