<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilPaymentVendors
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

class ilPaymentVendors
{
	var $db = null;

	var $vendors = array();

	/**
	* Constructor
	* @access	public
	*/
	function ilPaymentVendors()
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->__read();
	}

	function getVendors()
	{
		return $this->vendors;
	}

	function isAssigned($a_usr_id)
	{
		return isset($this->vendors[$a_usr_id]);
	}

	function add($a_usr_id)
	{
		if(isset($this->vendors[$a_usr_id]))
		{
			die("class.ilPaymentVendors::add() Vendor already exists");
		}

		$statement = $this->db->prepareManip('
			INSERT INTO payment_vendors
			SET vendor_id = ?,
				cost_center = ?',
			array('integer', 'text')
		);
		$data = array($a_usr_id, 'IL_INST_ID_'.$a_usr_id);
		$this->db->execute($statement, $data);
		
		$this->__read();

		return true;
	}
	function update($a_usr_id, $a_cost_center)
	{
		$statement = $this->db->prepareManip('
			UPDATE payment_vendors 
			SET cost_center = ?
			WHERE vendor_id = ?',
			array('text', 'integer')
		);
		$data = array($a_cost_center, $a_usr_id);	
		$this->db->execute($statement, $data);
		
		$this->__read();

		return true;
	}
	function delete($a_usr_id)
	{
		if(!isset($this->vendors[$a_usr_id]))
		{
			die("class.ilPaymentVendors::delete() Vendor does not exist");
		}

		$statement = $this->db->prepareManip('
			DELETE FROM payment_vendors WHERE vendor_id = ?',
			array('integer')
		);
		
		$data = array($a_usr_id); 
			
		$this->db->execute($statement, $data);
		$this->__read();
		
		return true;
	}

	// PRIVATE
	function __read()
	{
		$this->vendors = array();

		$statement = $this->db->prepare('SELECT * FROM payment_vendors');
		$res = $this->db->execute($statement);
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->vendors[$row->vendor_id]['vendor_id'] = $row->vendor_id;
			$this->vendors[$row->vendor_id]['cost_center'] = $row->cost_center;
		}
		return true;
	}

	// STATIC
	function _isVendor($a_usr_id)
	{
		global $ilDB;

		$statement = $ilDB->prepare('
			SELECT * FROM payment_vendors WHERE vendor_id = ?',
			array('integer')
		);
		
		$data = array($a_usr_id);
		$res = $ilDB->execute($statement, $data);
		
		return $res->numRows() ? true : false;
	}

	function _getCostCenter($a_usr_id)
	{
		global $ilDB;

		$statement = $ilDB->prepare('
			SELECT * FROM payment_vendors WHERE vendor_id = ?',
			array('integer')
		);
		
		$data = array($a_usr_id);
		$res = $ilDB->execute($statement, $data);
		
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->cost_center;
		}
		return -1;
	}		

} // END class.ilPaymentVendors
?>
