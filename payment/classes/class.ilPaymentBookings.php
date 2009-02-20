<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
	var $payed 		= null;
	var $access 	= null;
	var $voucher 	= null;
	var $street 	= null;
	var $house_nr 	= null;
	var $po_box 	= null;
	var $zipcode 	= null;
	var $city 		= null;
	var $country 	= null;
	
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
	
	 function getStreet()
	 {
	 	return $this->street;
	 }
	 function setStreet($a_street, $a_house_nr)
	 {
	 	$street = $a_street.' '.$a_house_nr;
	 	$this->street = $street;
	 }
	 
	 function getPoBox()
	 {
	 	return $this->po_box;
	 }
	 function setPoBox($a_po_box)
	 {
	 	$this->po_box = $a_po_box;
	 }
	 
	 function getZipcode()
	 {
	 	return $this->zipcode;
	 }
	 function setZipcode($a_zipcode)
	 {
	 	$this->zipcode = $a_zipcode;
	 }
	 function getCity()
	 {
	 	return $this->city;
	 }
	 function setCity($a_city)
	 {
	 	$this->city = $a_city;
	 }
	 
	 function getCountry()
	 {
	 	return $this->country;
	 }	
	 function setCountry($a_country)
	 {
	 	$this->country = $a_country;
	 }
		
		

	function add()
	{
		$statement = $this->db->manipulateF('
			INSERT INTO payment_statistic
			(
				transaction,
				pobject_id,
				customer_id,
				b_vendor_id,
				b_pay_method,
				order_date,
				duration,
				price,
				discount,
				payed,
				access,
				voucher,
				transaction_extern,
				street,
				po_box,
				zipcode,
				city,
				country
			)
			VALUES 
				( %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)',
			array(	
					'text', 
					'integer', 
					'integer', 
					'integer',
					'integer',
					'integer',
					'text',
					'text',
					'text',
					'integer',
					'integer',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text'),
			array(
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
					$this->getTransactionExtern(),
					$this->getStreet(),
					$this->getPoBox(),
					$this->getZipcode(),
					$this->getCity(),
					$this->getCountry()
				));

		return $this->db->getLastInsertId();
	}
						 
	function update()
	{
		if($this->getBookingId())
		{
			$statement = $this->db->manipulateF('
				UPDATE payment_statistic 
				SET payed = %s, 
					access = %s
				WHERE booking_id = %s', 
				array('integer', 'integer', 'integer'),
				array((int) $this->getPayedStatus(), (int) $this->getAccessStatus(), $this->getBookingId()));

			return true;
		}
		return false;
	}

	function delete()
	{
		if($this->getBookingId())
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_statistic WHERE booking_id = %s', 
				array('integer'),
				array((int)$this->getBookingId())
			);
			
			return true;
		}
		return false;
	}

	function getBookingsOfCustomer($a_usr_id)
	{
		$res = $this->db->queryf('
			SELECT * from payment_statistic ps, payment_objects po
			WHERE ps.pobject_id = po.pobject_id
			AND customer_id = %s
			ORDER BY order_date DESC',
			array('integer'),
			array($a_usr_id)
		);

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
			$booking[$row->booking_id]['street'] = $row->street;
			$booking[$row->booking_id]['po_box'] = $row->po_box;
			$booking[$row->booking_id]['zipcode'] = $row->zipcode;
			$booking[$row->booking_id]['city'] = $row->city;
			$booking[$row->booking_id]['country'] = $row->country;
			
		}

		return $booking ? $booking : array();
	}

	function getBookings()
	{
		return $this->bookings ? $this->bookings : array();
	}

	function getBooking($a_booking_id)
	{
		$res = $this->db->queryf('
			SELECT * FROM payment_statistic ps, payment_objects po
			WHERE ps.pobject_id = po.pobject_id
			AND booking_id = %s',
			array('integer'),
		 	array($a_booking_id));
		
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
			$booking['street'] = $row->street;
			$booking['po_box'] = $row->po_box;
			$booking['zipcode'] = $row->zipcode;
			$booking['city'] = $row->city;
			$booking['country'] = $row->country;			
		}
		return $booking ? $booking : array();
	}

	// STATIC
	function _getCountBookingsByVendor($a_vendor_id)
	{
		global $ilDB;

		$res = $ilDB->queryf(
			'SELECT COUNT(booking_id) bid FROM payment_statistic
			WHERE b_vendor_id = %s',
			array('integer'),
			array($a_vendor_id));

		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $row['bid'];
		}
		return 0;
	}

	function _getCountBookingsByCustomer($a_vendor_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT COUNT(booking_id) bid FROM payment_statistic
			WHERE customer_id = %s',
			array('integer'),
			array($a_vendor_id));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->bid;
		}
		return 0;
	}
	
	function _getCountBookingsByObject($a_pobject_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT COUNT(booking_id) bid FROM payment_statistic
			WHERE pobject_id = %s',
			array('integer'),
			array($a_pobject_id));
		
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

		$res = $ilDB->queryf('
			SELECT * FROM payment_statistic
			WHERE pobject_id = %s
			AND customer_id = %s
			AND payed = %s
			AND access = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array($a_pobject_id, $usr_id, '1', '1'));
		
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

		$res = $this->db->queryf('
			SELECT * FROM payment_statistic
			WHERE pobject_id = %s
			AND customer_id = %s
			AND payed = %s
			AND access = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array($a_pobject_id, $usr_id, '1', '1'));
		
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
				$res = $this->db->queryf ('
					SELECT COUNT(booking_id) bid FROM payment_statistc
					WHERE pay_method = %s',
					array('integer'),
					array('1'));
				
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return $row->bid;
				}
				return 0;

			case 'pm_bmf':
				$res = $this->db->queryf ('
					SELECT COUNT(booking_id) bid FROM payment_statistc
					WHERE pay_method = %s',
					array('integer'),
					array('2'));
				
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					return $row->bid;
				}
				return 0;

			case 'pm_paypal':
				$res = $this->db->queryf ('
					SELECT COUNT(booking_id) bid FROM payment_statistc
					WHERE pay_method = %s',
					array('integer'),
					array('3'));

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

		$data = array();
		$data_types = array();
		
		$query = 'SELECT * FROM payment_statistic ps, payment_objects po';
		if ($_SESSION['pay_statistics']['customer'] or $_SESSION['pay_statistics']['vendor'])
		{
			$query .= ', usr_data ud';
		}
		$query .= ' WHERE ps.pobject_id = po.pobject_id ';

		if ($_SESSION['pay_statistics']['transaction_value'] != '')
		{
			if ($_SESSION['pay_statistics']['transaction_type'] == 0)
			{
				$query .= "AND transaction_extern LIKE %s ";
				array_push($data, $_SESSION['pay_statistics']['transaction_value'].'%');
				array_push($data_types, 'text');
			}
			else if ($_SESSION['pay_statistics']['transaction_type'] == 1)
			{
				$query .= "AND transaction_extern LIKE %s ";
				array_push($data, '%'.$_SESSION['pay_statistics']['transaction_value']);
				array_push($data_types, 'text');				
			}
		}
		if ($_SESSION['pay_statistics']['customer'] != '')
		{
			$query .= "AND ud.login LIKE %s
					  AND ud.usr_id = ps.customer_id ";
			array_push($data, '%'.$_SESSION['pay_statistics']['customer'].'%'); 
			array_push($data_types, 'text');			
		}
		if ($_SESSION['pay_statistics']['from']['day'] != '' &&
			$_SESSION['pay_statistics']['from']['month'] != '' &&
			$_SESSION['pay_statistics']['from']['year'] != '')
		{
			$from = mktime(0, 0, 0, $_SESSION['pay_statistics']['from']['month'], 
						   $_SESSION['pay_statistics']['from']['day'], $_SESSION['pay_statistics']['from']['year']);
			$query .= 'AND order_date >= %s ';
			 array_push($data, $from);
			 array_push($data_types, 'integer');
		}
		if ($_SESSION['pay_statistics']['til']['day'] != '' &&
			$_SESSION['pay_statistics']['til']['month'] != '' &&
			$_SESSION['pay_statistics']['til']['year'] != '')
		{
			$til = mktime(23, 59, 59, $_SESSION['pay_statistics']['til']['month'], 
						  $_SESSION['pay_statistics']['til']['day'], $_SESSION['pay_statistics']['til']['year']);
			$query .= 'AND order_date <= %s '; 
			array_push($data, $til);
			array_push($data_types, 'integer');
		}
		if ($_SESSION['pay_statistics']['payed'] == '0' ||
			$_SESSION['pay_statistics']['payed'] == '1')
		{
			$query .= 'AND payed = %s ';
			array_push($data, $_SESSION['pay_statistics']['payed']);
			array_push($data_types, 'integer');
		}
		if ($_SESSION['pay_statistics']['access'] == '0' ||
			$_SESSION['pay_statistics']['access'] == '1')
		{
			$query .= 'AND access = %s ';
			array_push($data, $_SESSION['pay_statistics']['access']);
			array_push($data_types, 'integer');
		}
		if ($_SESSION['pay_statistics']['pay_method'] == '1' ||
			$_SESSION['pay_statistics']['pay_method'] == '2' ||
			$_SESSION['pay_statistics']['pay_method'] == '3')
		{
			$query .= 'AND b_pay_method = %s ';
			array_push($data, $_SESSION['pay_statistics']['pay_method']);
			array_push($data_types, 'integer');
		}

		if(!$this->admin_view)
		{
		
			$vendors = $this->__getVendorIds();
			if (is_array($vendors) &&
				count($vendors) > 1)
			{
				$in = 'ps.b_vendor_id IN (';
				$in .= implode(',',$vendors);
				$in .= ')';
				
				$query .= ' AND %s ';
				array_push($data, $in);
				array_push($data_types, 'integer');
			}
		}
		else
		{
			if($_SESSION['pay_statistics']['vendor'])
			{
				$query .= 'AND ud.login LIKE %s
							AND ud.usr_id = ps.b_vendor_id ';
				
				array_push($data, '%'.$_SESSION['pay_statistics']['vendor'].'%');
				array_push($data_types, 'text');
			}
		}
		$query .= 'ORDER BY order_date DESC';	

		$cnt_data = count($data);
		$cnt_data_types = count($data_types);
		
		if($cnt_data == 0 || $cnt_data_types == 0)
		{
			$res = $this->db->query($query);

		}
		else
		{
			$res= $this->db->queryf($query, $data_types, $data);
		} 

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
			$this->bookings[$row->booking_id]['street'] = $row->street;
			$this->bookings[$row->booking_id]['po_box'] = $row->po_box;
			$this->bookings[$row->booking_id]['zipcode'] = $row->zipcode;
			$this->bookings[$row->booking_id]['city'] = $row->city;
			$this->bookings[$row->booking_id]['country'] = $row->country;		
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