<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilPaymentVendors
 * @author  Nadia Ahmad  <nahmad@databay.de>
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilPaymentVendors.php 22133 2009-10-16 08:09:11Z nkrzywon $
 * @package ilias-core
 */

class ilPaymentVendors
{
	private static $_instance;

	/**
	 * @var $db ilDB
	 */
	public $db = null;

	public $vendors = array();


	public static function getInstance()
	{
		if(!isset(self::$_instance))
		{
			self::$_instance = new ilPaymentVendors();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 * @access    private
	 */
	private function __construct()
	{
		global $ilDB;

		$this->db = $ilDB;

		$this->__read();
	}

	public function getVendors()
	{
		return $this->vendors;
	}

	public function isAssigned($a_usr_id)
	{
		return isset($this->vendors[$a_usr_id]);
	}

	public function add($a_usr_id)
	{
		if(isset($this->vendors[$a_usr_id]))
		{
			die("class.ilPaymentVendors::add() Vendor already exists");
		}

		$this->db->insert('payment_vendors',
			array(
				'vendor_id'       => array('integer', $a_usr_id),
				'cost_center'     => array('text', 'IL_INST_ID_' . $a_usr_id)
			));

		$this->__read();

		return true;
	}

	public function update($a_usr_id, $a_cost_center)
	{
		$this->db->update('payment_vendors',
			array('cost_center'    => array('text', $a_cost_center)),
			array('vendor_id'    => array('integer', $a_usr_id)));

		$this->__read();

		return true;
	}

	public function delete($a_usr_id)
	{
		if(!isset($this->vendors[$a_usr_id]))
		{
			die("class.ilPaymentVendors::delete() Vendor does not exist");
		}

		$this->db->manipulateF('
			DELETE FROM payment_vendors WHERE vendor_id = %s',
			array('integer'),
			array($a_usr_id));

		// deleting of trustees is done by gui as a next step    

		$this->__read();

		return true;
	}

	// PRIVATE
	private function __read()
	{
		$this->vendors = array();

		$res = $this->db->query('SELECT * FROM payment_vendors');


		while($row = $this->db->fetchObject($res))
		{
			$this->vendors[$row->vendor_id]['vendor_id']   = $row->vendor_id;
			$this->vendors[$row->vendor_id]['cost_center'] = $row->cost_center;
		}
		return true;
	}

	// STATIC
	public static function _isVendor($a_usr_id)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_vendors WHERE vendor_id = %s',
			array('integer'), array($a_usr_id));

		return $ilDB->numRows($res) ? true : false;
	}

	public static function _getCostCenter($a_usr_id)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_vendors WHERE vendor_id = %s',
			array('integer'), array($a_usr_id));

		while($row = $ilDB->fetchObject($res))
		{
			return $row->cost_center;
		}
		return -1;
	}

} // END class.ilPaymentVendors
?>
