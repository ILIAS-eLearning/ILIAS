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
* Class ilPaymentBookings
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentVendors.php';
include_once './payment/classes/class.ilPaymentTrustees.php';


class ilPaymentBookings
{
	/*
	 * id of vendor, admin or trustee
	 */
	var $user_id = null;
	var $db = null;

	var $bookings = array();

	function ilPaymentBookings($a_user_id)
	{
		global $ilDB;

		$this->user_id = $a_user_id;
		$this->db =& $ilDB;

		$this->__read();
	}

	function getBookings()
	{
		return $this->bookings ? $this->bookings : array();
	}

	// STATIC
	function _getCountBookingsByObject($a_pobject_id)
	{
		global $ilDB;

		$query = "SELECT COUNT(booking_id) AS bid FROM payment_statistic ".
			"WHERE pobject_id = '".$a_pobject_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->bid;
		}
		return 0;
	}
	
	function _getCountBookingsByPayMethod($a_pm)	
	{
		switch($a_pm)
		{
			case 'pm_bill':
				$query = "SELECT COUNT(booking_id) AS bid FROM payment_statistic ".
					"WHERE pay_method = '1'";

				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return $row->bid;
				}
				return 0;

			case 'pm_bmf':
				$query = "SELECT COUNT(booking_id) AS bid FROM payment_statistic ".
					"WHERE pay_method = '2'";

				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return $row->bid;
				}
				return 0;

			default:
				return 0;
		}
	}


	// PRIVATE
	function __read()
	{
		$vendors = $this->__getVendorIds();

		$in = 'ps.b_vendor_id IN (';
		$in .= implode(',',$vendors);
		$in .= ')';

		$query = 'SELECT * FROM payment_statistic as ps, payment_objects as po '.
			"WHERE ps.pobject_id = po.pobject_id ".
			"AND ".$in." ".
			"ORDER BY order_date DESC";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->bookings[$row->booking_id]['booking_id'] = $row->booking_id;
			$this->bookings[$row->booking_id]['transaction'] = $row->transaction;
			$this->bookings[$row->booking_id]['pobject_id'] = $row->pobject_id;
			$this->bookings[$row->booking_id]['customer_id'] = $row->customer_id;
			$this->bookings[$row->booking_id]['order_date'] = $row->order_date;
			$this->bookings[$row->booking_id]['duration'] = $row->duration;
			$this->bookings[$row->booking_id]['price'] = $row->price;
			$this->bookings[$row->booking_id]['payed'] = $row->payed;
			$this->bookings[$row->booking_id]['access'] = $row->access;
			$this->bookings[$row->booking_id]['ref_id'] = $row->ref_id;
			$this->bookings[$row->booking_id]['status'] = $row->status;
			$this->bookings[$row->booking_id]['pay_method'] = $row->pay_method;
			$this->bookings[$row->booking_id]['vendor_id'] = $row->vendor_id;
			$this->bookings[$row->booking_id]['b_vendor_id'] = $row->b_vendor_id;
			$this->bookings[$row->booking_id]['b_pay_method'] = $row->b_pay_method;
			
		}

	}

	function __getVendorIds()
	{
		if(ilPaymentVendors::_isVendor($this->user_id))
		{
			$vendors[] = $this->user_id;
		}
		if($vend = ilPaymentTrustees::_getVendorsForObjects($this->user_id))
		{
			foreach($vend as $v)
			{
				if(ilPaymentTrustees::_hasStatisticPermissionByVendor($this->user_id,$v))
				{
					$vendors[] = $v;
					#vendors = array_merge($vendors,$v);
				}
			}
		}
		return $vendors ? $vendors : array();
	}
		
		
}
?>