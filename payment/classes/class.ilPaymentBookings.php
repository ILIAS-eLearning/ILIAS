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
	var $admin_view = false;

	/*
	 * admin_view = true reads all statistic data (only_used in administration)
	 */
	function ilPaymentBookings($a_user_id = '',$a_admin_view = false)
	{
		global $ilDB;

		$this->admin_view = $a_admin_view;
		$this->user_id = $a_user_id;
		$this->db =& $ilDB;

		if($a_user_id)
		{
			$this->__read();
		}
	}

	// SET GET
	function setBookingId($a_booking_id)
	{
		return $this->booking_id = $a_booking_id;
	}
	function getBookingId()
	{
		return $this->booking_id;
	}
	function setTransaction($a_transaction)
	{
		$this->transaction = $a_transaction;
	}
	function getTransaction()
	{
		return $this->transaction;
	}
	function setPobjectId($a_pobject_id)
	{
		$this->pobject_id = $a_pobject_id;
	}
	function getPobjectId()
	{
		return $this->pobject_id;
	}
	function setCustomerId($a_customer_id)
	{
		$this->customer_id = $a_customer_id;
	}
	function getCustomerId()
	{
		return $this->customer_id;
	}
	function setVendorId($a_vendor_id)
	{
		$this->vendor_id = $a_vendor_id;
	}
	function getVendorId()
	{
		return $this->vendor_id;
	}
	function setPayMethod($a_pay_method)
	{
		$this->pay_method = $a_pay_method;
	}
	function getPayMethod()
	{
		return $this->pay_method;
	}
	function setOrderDate($a_order_date)
	{
		$this->order_date = $a_order_date;
	}
	function getOrderDate()
	{
		return $this->order_date;
	}
	function setDuration($a_duration)
	{
		$this->duration = $a_duration;
	}
	function getDuration()
	{
		return $this->duration;
	}
	function setPrice($a_price)
	{
		$this->price = $a_price;
	}
	function getPrice()
	{
		return $this->price;
	}
	function setDiscount($a_discount)
	{
		$this->discount = $a_discount;
	}
	function getDiscount()
	{
		return $this->discount;
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
	function setVoucher($a_voucher)
	{
		$this->voucher = $a_voucher;
	}
	function getVoucher()
	{
		return $this->voucher;
	}
	function setTransactionExtern($a_transaction_extern)
	{
		$this->transaction_extern = $a_transaction_extern;
	}
	function getTransactionExtern()
	{
		return $this->transaction_extern;
	}

	function add()
	{
		$query = sprintf("INSERT INTO payment_statistic VALUES('',".
						 "'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
						 $this->getTransaction(),
						 $this->getPobjectId(),
						 $this->getCustomerId(),
						 $this->getVendorId(),
						 $this->getPayMethod(),
						 $this->getOrderDate(),
						 $this->getDuration(),
						 $this->getPrice(),
						 $this->getDiscount(),
						 $this->getPayedStatus(),
						 $this->getAccessStatus(),
						 $this->getVoucher(),
						 $this->getTransactionExtern());

		$this->db->query($query);

		return $this->db->getLastInsertId();
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

	function getBookingsOfCustomer($a_usr_id)
	{
		$query = 'SELECT * FROM payment_statistic as ps, payment_objects as po '.
			"WHERE ps.pobject_id = po.pobject_id ".
			"AND customer_id = '".$a_usr_id."' ".
			"ORDER BY order_date DESC";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$booking[$row->booking_id]['booking_id'] = $row->booking_id;
			$booking[$row->booking_id]['transaction'] = $row->transaction;
			$booking[$row->booking_id]['pobject_id'] = $row->pobject_id;
			$booking[$row->booking_id]['customer_id'] = $row->customer_id;
			$booking[$row->booking_id]['order_date'] = $row->order_date;
			$booking[$row->booking_id]['duration'] = $row->duration;
			$booking[$row->booking_id]['price'] = $row->price;
			$booking[$row->booking_id]['discount'] = $row->discount;
			$booking[$row->booking_id]['payed'] = $row->payed;
			$booking[$row->booking_id]['access'] = $row->access;
			$booking[$row->booking_id]['ref_id'] = $row->ref_id;
			$booking[$row->booking_id]['status'] = $row->status;
			$booking[$row->booking_id]['pay_method'] = $row->pay_method;
			$booking[$row->booking_id]['vendor_id'] = $row->vendor_id;
			$booking[$row->booking_id]['b_vendor_id'] = $row->b_vendor_id;
			$booking[$row->booking_id]['b_pay_method'] = $row->b_pay_method;
			$booking[$row->booking_id]['voucher'] = $row->voucher;
			$booking[$row->booking_id]['transaction_extern'] = $row->transaction_extern;
		}
		return $booking ? $booking : array();
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
			$booking['discount'] = $row->discount;			
			$booking['payed'] = $row->payed;
			$booking['access'] = $row->access;
			$booking['ref_id'] = $row->ref_id;
			$booking['status'] = $row->status;
			$booking['pay_method'] = $row->pay_method;
			$booking['vendor_id'] = $row->vendor_id;
			$booking['b_vendor_id'] = $row->b_vendor_id;
			$booking['b_pay_method'] = $row->b_pay_method;
			$booking['voucher'] = $row->voucher;
			$booking['transaction_extern'] = $row->transaction_extern;
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

	function _getCountBookingsByCustomer($a_vendor_id)
	{
		global $ilDB;
		
		$query = "SELECT COUNT(booking_id) AS bid FROM payment_statistic ".
			"WHERE customer_id = '".$a_vendor_id."'";

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

	function _hasAccess($a_pobject_id,$a_user_id = 0)
	{
		global $ilDB,$ilias;

		$usr_id = $a_user_id ? $a_user_id : $ilias->account->getId();
		
		$query = "SELECT * FROM payment_statistic ".
			"WHERE pobject_id = '".$a_pobject_id."' ".
			"AND customer_id = '".$usr_id."' ".
			"AND payed = '1' ".
			"AND access = '1'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$orderDateYear = date("Y", $row->order_date);
			$orderDateMonth = date("m", $row->order_date);
			$orderDateDay = date("d", $row->order_date);
			$orderDateHour = date("H", $row->order_date);
			$orderDateMinute = date("i", $row->order_date);
			$orderDateSecond = date("s", $row->order_date);
			if (($orderDateMonth + $row->duration) > 12)
			{
				$years = floor(($orderDateMonth + $row->duration) / 12);
				$months = ($orderDateMonth + $row->duration) - (12 * $years);
				$orderDateYear += $years;
				$orderDateMonth = $months;
			}
			else
			{
				$orderDateMonth += $row->duration;
			}
			$startDate =  date("Y-m-d H:i:s", $row->order_date);
			$endDate = date("Y-m-d H:i:s", mktime($orderDateHour, $orderDateMinute, $orderDateSecond, $orderDateMonth, $orderDateDay, $orderDateYear));
			if (date("Y-m-d H:i:s") >= $startDate &&
				date("Y-m-d H:i:s") <= $endDate)
			{
				return true;
			}
		}			
		return false;
	}
	
	function _getActivation($a_pobject_id,$a_user_id = 0)
	{
		global $ilDB,$ilias;

		$usr_id = $a_user_id ? $a_user_id : $ilias->account->getId();
		
		$query = "SELECT * FROM payment_statistic ".
			"WHERE pobject_id = '".$a_pobject_id."' ".
			"AND customer_id = '".$usr_id."' ".
			"AND payed = '1' ".
			"AND access = '1'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$orderDateYear = date("Y", $row->order_date);
			$orderDateMonth = date("m", $row->order_date);
			$orderDateDay = date("d", $row->order_date);
			$orderDateHour = date("H", $row->order_date);
			$orderDateMinute = date("i", $row->order_date);
			$orderDateSecond = date("s", $row->order_date);
			if (($orderDateMonth + $row->duration) > 12)
			{
				$years = floor(($orderDateMonth + $row->duration) / 12);
				$months = ($orderDateMonth + $row->duration) - (12 * $years);
				$orderDateYear += $years;
				$orderDateMonth = $months;
			}
			else
			{
				$orderDateMonth += $row->duration;
			}
			$startDate =  date("Y-m-d H:i:s", $row->order_date);
			$endDate = date("Y-m-d H:i:s", mktime($orderDateHour, $orderDateMinute, $orderDateSecond, $orderDateMonth, $orderDateDay, $orderDateYear));
			if (date("Y-m-d H:i:s") >= $startDate &&
				date("Y-m-d H:i:s") <= $endDate)
			{
				$activation = array(
					"activation_start" => $row->order_date,
					"activation_end" => mktime($orderDateHour, $orderDateMinute, $orderDateSecond, $orderDateMonth, $orderDateDay, $orderDateYear)
				);
				return $activation;
			}
		}			
		return false;
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

			case 'pm_paypal':
				$query = "SELECT COUNT(booking_id) AS bid FROM payment_statistic ".
					"WHERE pay_method = '3'";

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
		$query = 'SELECT * FROM payment_statistic as ps, payment_objects as po';
		if ($_SESSION["pay_statistics"]["customer"] or $_SESSION['pay_statistics']['vendor'])
		{
			$query .= ', usr_data as ud';
		}
		$query .= " WHERE ps.pobject_id = po.pobject_id ";

		if ($_SESSION["pay_statistics"]["transaction_value"] != "")
		{
			if ($_SESSION["pay_statistics"]["transaction_type"] == 0)
			{
				$query .= "AND transaction_extern LIKE '" . $_SESSION["pay_statistics"]["transaction_value"] . "%' ";
			}
			else if ($_SESSION["pay_statistics"]["transaction_type"] == 1)
			{
				$query .= "AND transaction_extern LIKE '%" . $_SESSION["pay_statistics"]["transaction_value"] . "' ";
			}
		}
		if ($_SESSION["pay_statistics"]["customer"] != "")
		{
			$query .= "AND ud.login LIKE '%" . $_SESSION["pay_statistics"]["customer"] . "%' " .
					  "AND ud.usr_id = ps.customer_id ";
		}
		if ($_SESSION["pay_statistics"]["from"]["day"] != "" &&
			$_SESSION["pay_statistics"]["from"]["month"] != "" &&
			$_SESSION["pay_statistics"]["from"]["year"] != "")
		{
			$from = mktime(0, 0, 0, $_SESSION["pay_statistics"]["from"]["month"], 
						   $_SESSION["pay_statistics"]["from"]["day"], $_SESSION["pay_statistics"]["from"]["year"]);
			$query .= "AND order_date >= '" . $from . "' ";
		}
		if ($_SESSION["pay_statistics"]["til"]["day"] != "" &&
			$_SESSION["pay_statistics"]["til"]["month"] != "" &&
			$_SESSION["pay_statistics"]["til"]["year"] != "")
		{
			$til = mktime(23, 59, 59, $_SESSION["pay_statistics"]["til"]["month"], 
						  $_SESSION["pay_statistics"]["til"]["day"], $_SESSION["pay_statistics"]["til"]["year"]);
			$query .= "AND order_date <= '" . $til . "' ";
		}
		if ($_SESSION["pay_statistics"]["payed"] == "0" ||
			$_SESSION["pay_statistics"]["payed"] == "1")
		{
			$query .= "AND payed = '" . $_SESSION["pay_statistics"]["payed"] . "' ";
		}
		if ($_SESSION["pay_statistics"]["access"] == "0" ||
			$_SESSION["pay_statistics"]["access"] == "1")
		{
			$query .= "AND access = '" . $_SESSION["pay_statistics"]["access"] . "' ";
		}
		if ($_SESSION["pay_statistics"]["pay_method"] == "1" ||
			$_SESSION["pay_statistics"]["pay_method"] == "2" ||
			$_SESSION["pay_statistics"]["pay_method"] == "3")
		{
			$query .= "AND b_pay_method = '" . $_SESSION["pay_statistics"]["pay_method"] . "' ";
		}
		
		if(!$this->admin_view)
		{
			$vendors = $this->__getVendorIds();
			if (is_array($vendors) &&
				count($vendors) > 0)
			{
				$in = 'ps.b_vendor_id IN (';
				$in .= implode(',',$vendors);
				$in .= ')';
				
				$query .= "AND ".$in." ";
			}
		}
		else
		{
			if($_SESSION['pay_statistics']['vendor'])
			{
				$query .= "AND ud.login LIKE '%" . $_SESSION["pay_statistics"]["vendor"] . "%' " .
					"AND ud.usr_id = ps.b_vendor_id ";
			}
		}
		$query .= "ORDER BY order_date DESC";

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
			$this->bookings[$row->booking_id]['discount'] = $row->discount;
			$this->bookings[$row->booking_id]['payed'] = $row->payed;
			$this->bookings[$row->booking_id]['access'] = $row->access;
			$this->bookings[$row->booking_id]['ref_id'] = $row->ref_id;
			$this->bookings[$row->booking_id]['status'] = $row->status;
			$this->bookings[$row->booking_id]['pay_method'] = $row->pay_method;
			$this->bookings[$row->booking_id]['vendor_id'] = $row->vendor_id;
			$this->bookings[$row->booking_id]['b_vendor_id'] = $row->b_vendor_id;
			$this->bookings[$row->booking_id]['b_pay_method'] = $row->b_pay_method;
			$this->bookings[$row->booking_id]['voucher'] = $row->voucher;
			$this->bookings[$row->booking_id]['transaction_extern'] = $row->transaction_extern;			
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