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

	var $booking_id = null;
	var $payed = null;
	var $access = null;

	function ilPaymentBookings($a_user_id = '')
	{
		global $ilDB;

		$this->user_id = $a_user_id;
		$this->db =& $ilDB;

		if($a_user_id)
		{
			$this->__read();
		}
	}

	function setBookingId($a_booking_id)
	{
		return $this->booking_id = $a_booking_id;
	}
	function getBookingId()
	{
		return $this->booking_id;
	}
	function setPayed($a_payed)
	{
		$this->payed = $a_payed;
	}
	function getPayedStatus()
	{
		return $this->payed;
	}
	function setAccess($a_access)
	{
		$this->access = $a_access;
	}
	function getAccessStatus()
	{
		return $this->access;
	}

	function update()
	{
		if($this->getBookingId())
		{
			$query = "UPDATE payment_statistic ".
				"SET payed = '".(int) $this->getPayedStatus()."', ".
				"access = '".(int) $this->getAccessStatus()."' ".
				"WHERE booking_id = '".$this->getBookingId()."'";
			$this->db->query($query);

			return true;
		}
		return false;
	}

	function delete()
	{
		if($this->getBookingId())
		{
			$query = "DELETE FROM payment_statistic ".
				"WHERE booking_id = '".$this->getBookingId()."'";

			$this->db->query($query);

			return true;
		}
		return false;
	}			

	function getBookings()
	{
		return $this->bookings ? $this->bookings : array();
	}

	function getBooking($a_booking_id)
	{
		$query = 'SELECT * FROM payment_statistic as ps, payment_objects as po '.
			"WHERE ps.pobject_id = po.pobject_id ".
			"AND booking_id = '".$a_booking_id."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$booking['booking_id'] = $row->booking_id;
			$booking['transaction'] = $row->transaction;
			$booking['pobject_id'] = $row->pobject_id;
			$booking['customer_id'] = $row->customer_id;
			$booking['order_date'] = $row->order_date;
			$booking['duration'] = $row->duration;
			$booking['price'] = $row->price;
			$booking['payed'] = $row->payed;
			$booking['access'] = $row->access;
			$booking['ref_id'] = $row->ref_id;
			$booking['status'] = $row->status;
			$booking['pay_method'] = $row->pay_method;
			$booking['vendor_id'] = $row->vendor_id;
			$booking['b_vendor_id'] = $row->b_vendor_id;
			$booking['b_pay_method'] = $row->b_pay_method;
		}
		return $booking ? $booking : array();
	}
			

	// STATIC
	function _getCountBookingsByVendor($a_vendor_id)
	{
		global $ilDB;
		
		$query = "SELECT COUNT(booking_id) AS bid FROM payment_statistic ".
			"WHERE b_vendor_id = '".$a_vendor_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->bid;
		}
		return 0;
	}

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