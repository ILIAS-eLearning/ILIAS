<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* Class ilPaymentBookings
*
* @author Stefan Meyer
* @version $Id: class.ilPaymentBookings.php 22183 2009-10-23 16:08:15Z jgoedvad $
*
* @package core
*/
include_once './Services/Payment/classes/class.ilPaymentVendors.php';
include_once './Services/Payment/classes/class.ilPaymentTrustees.php';

class ilPaymentBookings
{
	/*
	 * id of vendor, admin or trustee
	 */
	public $user_id = null;
	public $db = null;

	public $bookings = array();

	public $booking_id = null;
	public $transaction = null;
	public $pobject_id = null;
	public $customer_id = null;
	public $vendor_id = null;
	public $pay_method = null;
	public $order_date = null;
	public $duration = 0;
	public $unlimited_duration = 0;
	public $price = 0;
	public $discount = 0;
	public $transaction_extern = null;
	public $vat_unit = 0;
	public $object_title = null;
	public $payed 		= null;
	public $access 	= null;
	public $voucher 	= null;
	public $street 	= null;
	public $house_nr 	= null;
	public $po_box 	= null;
	public $zipcode 	= null;
	public $city 		= null;
	public $country 	= null;
	
	public $email_extern = null;
	public $name_extern= null;
	public $currency_unit = null;

	public $access_startdate = null;
	public $access_enddate = null;
	public $admin_view = false;
	private $price_type = null;

	public $access_extension = false;

	/*
	 * admin_view = true reads all statistic data (only_used in administration)
	 */
	public function __construct($a_user_id = '',$a_admin_view = false)
	{
		global $ilDB;
	
		$this->admin_view = $a_admin_view;
		$this->user_id = $a_user_id;
		$this->db = $ilDB;

		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		$genSet = ilPaymentSettings::_getInstance();
		$this->currency_unit = $genSet->get('currency_unit');		
		
		if($a_user_id)
		{
			$this->__read();
		}
	}

	// SET GET
	public function setBookingId($a_booking_id)
	{
		return $this->booking_id = $a_booking_id;
	}
	public function getBookingId()
	{
		return $this->booking_id;
	}
	public function setTransaction($a_transaction)
	{
		$this->transaction = $a_transaction;
	}
	public function getTransaction()
	{
		return $this->transaction;
	}
	public function setPobjectId($a_pobject_id)
	{
		$this->pobject_id = $a_pobject_id;
	}
	public function getPobjectId()
	{
		return $this->pobject_id;
	}
	public function setCustomerId($a_customer_id)
	{
		$this->customer_id = $a_customer_id;
	}
	public function getCustomerId()
	{
		return $this->customer_id;
	}
	public function setVendorId($a_vendor_id)
	{
		$this->vendor_id = $a_vendor_id;
	}
	public function getVendorId()
	{
		return $this->vendor_id;
	}
	public function setPayMethod($a_pay_method)
	{
		$this->pay_method = $a_pay_method;
	}
	public function getPayMethod()
	{
		return $this->pay_method;
	}
	public function setOrderDate($a_order_date)
	{
		$this->order_date = $a_order_date;
	}
	public function getOrderDate()
	{
		return $this->order_date;
	}
	public function setDuration($a_duration)
	{
		$this->duration = $a_duration;
	}
	public function getDuration()
	{
		return $this->duration;
	}
	public function setUnlimitedDuration($a_unlimited_duration)
	{
		if($a_unlimited_duration == '' || null) $a_unlimited_duration = 0;		
		$this->unlimited_duration = $a_unlimited_duration;
	}
	
	public function getUnlimitedDuration()
	{
		return $this->unlimited_duration;
	}
	
    public function setPriceType($a_price_type)
    {
        $this->price_type = $a_price_type;
    }
    public function getPriceType()
    {
        return $this->price_type;
    }
	
	public function setPrice($a_price)
	{
		$this->price = $a_price;
	}
	public function getPrice()
	{
		return $this->price;
	}
	public function setDiscount($a_discount)
	{
		if($a_discount == null) $a_discount = 0;
		$this->discount = $a_discount;
	}
	public function getDiscount()
	{		
		if($this->discount == null) $this->discount = 0;
		return $this->discount;
	}
	public function setPayed($a_payed)
	{
		$this->payed = $a_payed;
	}
	public function getPayedStatus()
	{
		return $this->payed;
	}
	public function setAccess($a_access)
	{
		$this->access = $a_access;
	}
	public function getAccessStatus()
	{
		return $this->access;
	}
	public function setVoucher($a_voucher)
	{
		$this->voucher = $a_voucher;
	}
	public function getVoucher()
	{
		return $this->voucher;
	}
	public function setTransactionExtern($a_transaction_extern)
	{
		$this->transaction_extern = $a_transaction_extern;
	}
	public function getTransactionExtern()
	{
		return $this->transaction_extern;
	}
	
	 public function getStreet()
	 {
	 	return $this->street;
	 }
	 public function setStreet($a_street, $a_house_nr)
	 {
	 	$street = $a_street.' '.$a_house_nr;
	 	$this->street = $street;
	 }
	 public function getPoBox()
	 {
	 	return $this->po_box;
	 }
	 public function setPoBox($a_po_box)
	 {
	 	$this->po_box = $a_po_box;
	 }
	 
	 public function getZipcode()
	 {
	 	return $this->zipcode;
	 }
	 public function setZipcode($a_zipcode)
	 {
	 	$this->zipcode = $a_zipcode;
	 }
	 public function getCity()
	 {
	 	return $this->city;
	 }
	 public function setCity($a_city)
	 {
	 	$this->city = $a_city;
	 }
	 
	 public function getCountry()
	 {
	 	return $this->country;
	 }	
	 public function setCountry($a_country)
	 {
	 	$this->country = $a_country;
	 }
	public function setVatUnit($a_vat_unit)
	{

		$this->vat_unit = $a_vat_unit;
	} 
	public function getVatUnit()
	{
		
		return $this->vat_unit;
	}		
	public function setVatRate($a_vat_rate)
	{
		$this->vat_rate = $a_vat_rate;
	}
	public function getVatRate()
	{
		return $this->vat_rate;
	}
	public function setObjectTitle($a_object_title)
	{
		$this->object_title = $a_object_title;
	}
	function getObjectTitle()
	{
		return $this->object_title;
	}
	 
	public function setEmailExtern($a_email_extern)
	{
		$this->email_extern = $a_email_extern;
	}
	public function getEmailExtern()
	{
		return $this->email_extern;
	}
		
	public function setNameExtern($a_name_extern)
	{
		$this->name_extern = $a_name_extern;
	}
	public function getNameExtern()
	{
		return $this->name_extern;
	}
	public function setCurrencyUnit($a_currency_unit)
	{
		$this->currency_unit = $a_currency_unit; 
	}
	public function getCurrencyUnit()
	{
		return $this->currency_unit;
	}

	public function setAccessStartdate($a_access_startdate)
	{
		$this->access_startdate = $a_access_startdate;
	}
	public function getAccessStartdate()
	{
		return $this->access_startdate;
	}

	public function setAccessEnddate($a_access_enddate)
	{
		$this->access_enddate = $a_access_enddate;
	}
	public function getAccessEnddate()
	{
		return $this->access_enddate;
	}
	
	public function setAccessExtension($a_access_extension)
	{
		$this->access_extension = $a_access_extension;
	}
	public function getAccessExtension()
	{
		return $this->access_extension;
	}

	private function __checkExtensionDependencies()
	{
		global $ilDB;
		
		$ilDB->setLimit(1,0);

		// check unlimited_duration
		$res_1 = $ilDB->queryF('SELECT * FROM payment_statistic
			WHERE pobject_id = %s
			AND customer_id = %s
			AND duration = %s
			AND access_enddate = %s',
			array('integer','integer', 'integer', 'timestamp'),
			array($this->getPobjectId(), $this->getCustomerId(), 0, NULL)
		);

		$row_1 = $ilDB->fetchAssoc($res_1);
		if(count($row_1) > 0 )
		{
			// error: user already has unlimited duration. not able to extend ...
			return false;
		}

		// get last access_enddate
		$res_2 = $ilDB->queryF('SELECT * FROM payment_statistic
			WHERE pobject_id = %s
			AND customer_id = %s
			ORDER BY access_enddate DESC',
			array('integer','integer'),
			array($this->getPobjectId(), $this->getCustomerId())
		);

		$row_2 = $ilDB->fetchAssoc($res_2);
		if(count($row_2) > 0)
		{
			//user has already bought this object
			// use access_enddate as startdate
			$old_enddate = $row_2['access_enddate']; # Y-m-d H:i:s
			$current_date = date("Y-m-d H:i:s", $this->getOrderDate());

			if($old_enddate <= $current_date)
			{
				// Zugriff ist abgelaufen, nimm das aktuelle Datum  als startdate
				$this->setAccessStartdate($current_date);
			}
			else
			{
				// der Zugriff ist noch nicht abgelaufen, nimm enddate als startdate
				$this->setAccessStartdate($old_enddate);
			}

			if($this->getDuration() > 0)
			{
				$this->__calculateAccessEnddate();
			}
		}
		return true;
	}
/*
 * calculate AccessEnddate for TYPE_DURATION_MONTH !!
 */
	private function __calculateAccessEnddate()
	{
		include_once('Services/Calendar/classes/class.ilDateTime.php');

		$tmp_ts = new ilDateTime($this->getAccessStartdate(),IL_CAL_DATETIME);
		$start_date = $tmp_ts->getUnixTime();
		$duration = $this->getDuration();

		$startDateYear = date("Y", $start_date);
		$startDateMonth = date("m", $start_date);
		$startDateDay = date("d", $start_date);
		$startDateHour = date("H", $start_date);
		$startDateMinute = date("i",$start_date);
		$startDateSecond = date("s", $start_date);

		$access_enddate = date("Y-m-d H:i:s", mktime($startDateHour, $startDateMinute, $startDateSecond,
		$startDateMonth + $duration, $startDateDay, $startDateYear));

		$this->setAccessEnddate($access_enddate);		
	}

	public function add()
	{
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';
		switch($this->getPriceType())
		{
			case ilPaymentPrices::TYPE_DURATION_DATE:
				// do nothing. access_startdate, access_enddate is already set
				break;
			case ilPaymentPrices::TYPE_DURATION_MONTH:
			case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
				if($this->getAccessExtension() == 1)
				{
					$this->__checkExtensionDependencies();
				}
				else
				{
					$this->setAccessStartdate(date('Y-m-d H:i:s', $this->getOrderDate()));
					if($this->getDuration() > 0)
					{
						$this->__calculateAccessEnddate();
					}
				}			
				break;
			default:
				return false; // price_type_not_set!!!
				break;
		}
		
		$next_id = $this->db->nextId('payment_statistic');
		if(ilPayMethods::_EnabledSaveUserAddress((int)$this->getPayMethod()) == 1)
		{
			$this->db->insert('payment_statistic',
			array(
				'booking_id'	=> array('integer', $next_id),
				'transaction'	=> array('text',	$this->getTransaction()),
				'pobject_id'	=> array('integer',	$this->getPobjectId()),
				'customer_id'	=> array('integer',	$this->getCustomerId()),
				'b_vendor_id'	=> array('integer',	$this->getVendorId()),
				'b_pay_method'	=> array('integer',	$this->getPayMethod()),
				'order_date'	=> array('integer',	$this->getOrderDate()),
				'duration'		=> array('text',	$this->getDuration()),
				'price'			=> array('float',	$this->getPrice()),
				'discount'		=> array('float',	$this->getDiscount()),
		
				'payed'			=> array('integer',	$this->getPayedStatus()),
				'access_granted'=> array('integer',	$this->getAccessStatus()),
				'voucher'		=> array('text',	$this->getVoucher()),
				'transaction_extern'=> array('text',$this->getTransactionExtern()),
				'street'		=> array('text',	$this->getStreet()),
				'po_box'		=> array('text',	$this->getPoBox()),
				'zipcode'		=> array('text',	$this->getZipcode()),
				'city'			=> array('text',	$this->getCity()),
				'country'		=> array('text',	$this->getCountry()),
				'vat_rate'		=> array('float',	$this->getVatRate()),
				
				'vat_unit'		=> array('float',	$this->getVatUnit()),
				'object_title'	=> array('text',	$this->getObjectTitle()),
				'email_extern'	=> array('text',	$this->getEmailExtern()),
				'name_extern'	=> array('text',	$this->getNameExtern()),
				'currency_unit'	=> array('text',	$this->getCurrencyUnit()),
				'access_startdate'=> array('timestamp', $this->getAccessStartdate()),
				'access_enddate'=> array('timestamp', $this->getAccessEnddate())
				));
		}
		else
		{
			$this->db->insert('payment_statistic',
			array(
				'booking_id'	=> array('integer', $next_id),
				'transaction'	=> array('text',	$this->getTransaction()),
				'pobject_id'	=> array('integer',	$this->getPobjectId()),
				'customer_id'	=> array('integer',	$this->getCustomerId()),
				'b_vendor_id'	=> array('integer',	$this->getVendorId()),
				'b_pay_method'	=> array('integer',	$this->getPayMethod()),
				'order_date'	=> array('integer',	$this->getOrderDate()),
				'duration'		=> array('text',	$this->getDuration()),
				'price'			=> array('float',	$this->getPrice()),
				'discount'		=> array('float',	$this->getDiscount()),
				
				'payed'			=> array('integer',	$this->getPayedStatus()),
				'access_granted'=> array('integer',	$this->getAccessStatus()),
				'voucher'		=> array('text',	$this->getVoucher()),
				'transaction_extern'=> array('text',$this->getTransactionExtern()),
#				'street'		=> array('text',	$this->getStreet()),
#				'po_box'		=> array('text',	$this->getPoBox()),
#				'zipcode'		=> array('text',	$this->getZipcode()),
#				'city'			=> array('text',	$this->getCity()),
#				'country'		=> array('text',	$this->getCountry()),
				'vat_rate'		=> array('float',	$this->getVatRate()),
			
				'vat_unit'		=> array('float',	$this->getVatUnit()),
				'object_title'	=> array('text',	$this->getObjectTitle()),
				'email_extern'	=> array('text',	$this->getEmailExtern()),
				'name_extern'	=> array('text',	$this->getNameExtern()),
				'currency_unit'	=> array('text',	$this->getCurrencyUnit()),
				'access_startdate'=> array('timestamp', $this->getAccessStartdate()),
				'access_enddate'=> array('timestamp', $this->getAccessEnddate())
				));	
		}
		
		return  $next_id;
	}
						 
	public function update()
	{
		if($this->getBookingId())
		{
			$this->db->manipulateF('
				UPDATE payment_statistic 
				SET payed = %s, 
					access_granted = %s
				WHERE booking_id = %s', 
				array('integer', 'integer', 'integer'),
				array((int) $this->getPayedStatus(), (int) $this->getAccessStatus(), $this->getBookingId()));

			return true;
		}
		return false;
	}

	public function delete()
	{
		if($this->getBookingId())
		{
			$this->db->manipulateF('
				DELETE FROM payment_statistic WHERE booking_id = %s', 
				array('integer'),
				array((int)$this->getBookingId())
			);
			
			return true;
		}
		return false;
	}

	public static function getBookingsOfCustomer($a_usr_id)
	{
		global $ilDB;

		if(ANONYMOUS_USER_ID == $a_usr_id)
		return array();
		
		$booking = array();
		$res = $ilDB->queryf('
			SELECT * from payment_statistic ps, payment_objects po
			WHERE ps.pobject_id = po.pobject_id
			AND customer_id = %s
			ORDER BY order_date DESC',
			array('integer'),
			array($a_usr_id)
		);

		while($row = $ilDB->fetchAssoc($res))
		{ 
			$booking[$row['booking_id']] = $row;
		}

		return $booking;
	}

	public function getBookings()
	{
		return $this->bookings ? $this->bookings : array();
	}

	public function getBooking($a_booking_id)
	{
		$booking = array();
		$res = $this->db->queryf('
			SELECT * FROM payment_statistic ps, payment_objects po
			WHERE ps.pobject_id = po.pobject_id
			AND booking_id = %s',
			array('integer'),
		 	array($a_booking_id));
		
		while($row = $this->db->fetchObject($res))
		{
			$booking = $row;
		}
		return $booking ? $booking : array();
	}

	// STATIC
	public static function _getCountBookingsByVendor($a_vendor_id)
	{
		global $ilDB;

		$res = $ilDB->queryf(
			'SELECT COUNT(booking_id) bid FROM payment_statistic
			WHERE b_vendor_id = %s',
			array('integer'),
			array($a_vendor_id));

		while($row = $ilDB->fetchAssoc($res))
		{
			return $row['bid'];
		}
		return 0;
	}

	public static function _getCountBookingsByCustomer($a_vendor_id)
	{
		global $ilDB;
		
		if(ANONYMOUS_USER_ID == $a_vendor_id)
		return 0;
		
		$res = $ilDB->queryf('
			SELECT COUNT(booking_id) bid FROM payment_statistic
			WHERE customer_id = %s',
			array('integer'),
			array($a_vendor_id));
		
		while($row = $ilDB->fetchObject($res))
		{
			return $row->bid;
		}
		return 0;
	}
	
	public static function _getCountBookingsByObject($a_pobject_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT COUNT(booking_id) bid FROM payment_statistic
			WHERE pobject_id = %s',
			array('integer'),
			array($a_pobject_id));
		
		while($row = $ilDB->fetchObject($res))
		{
			return $row->bid;
		}
		return 0;
	}

	public static function _hasAccess($a_pobject_id, $a_user_id = 0, $a_transaction = 0)
	{
		global $ilDB, $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId() && !$a_transaction)
		{
			return false;
		}
		else				
		if($a_transaction)
		{
			$res = $ilDB->queryf('
				SELECT * FROM payment_statistic
				WHERE pobject_id = %s
				AND transaction = %s
				AND payed = %s
				AND access_granted = %s',
				array('integer','text', 'integer', 'integer'),
				array($a_pobject_id, $a_transaction, '1', '1'));
		}
		else	
		{
			$usr_id = $a_user_id ? $a_user_id : $ilUser->getId();
	
			$res = $ilDB->queryf('
				SELECT order_date, duration, access_startdate, access_enddate
				FROM payment_statistic
				WHERE pobject_id = %s
				AND customer_id = %s
				AND payed = %s
				AND access_granted = %s',
				array('integer', 'integer', 'integer', 'integer'),
				array($a_pobject_id, $usr_id, '1', '1'));
		}
		
		while($row = $ilDB->fetchAssoc($res))
		{
			if($row['duration'] == 0 && $row['access_enddate'] == NULL)
			{
				return true;
			}
			else 
			if($row['access_startdate'] == NULL)
			{
				if(time() >= $row['order_date'] 
					&& date("Y-m-d H:i:s") <= $row['access_enddate'])
				{
					return true;
				}
			}
			else
			if (date("Y-m-d H:i:s") >= $row['access_startdate'] 
				&& date("Y-m-d H:i:s") <= $row['access_enddate'])
			{
				return true;
			}
		}			
		return false;
	}
	
	// PRIVATE
	public function __read()
	{
		global $ilUser;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		return false;

		$data = array();
		$data_types = array();
		
		$query = 'SELECT * FROM payment_statistic ps '
			   . 'INNER JOIN payment_objects po ON po.pobject_id = ps.pobject_id ';
		if($_SESSION['pay_statistics']['customer'])
		{
			$query .= 'LEFT JOIN usr_data ud ON ud.usr_id = ps.customer_id ';
		}
		if($_SESSION['pay_statistics']['vendor'] && $this->admin_view)
		{
			$query .= 'LEFT JOIN usr_data udv ON udv.usr_id = ps.b_vendor_id ';
		}
		$query .= 'WHERE 1 = 1 ';
		if ($_SESSION['pay_statistics']['transaction_value'] != '')
		{
			if ($_SESSION['pay_statistics']['transaction_type'] == 0)
			{
				$query .= "AND transaction_extern LIKE %s ";
				$data_types[] = 'text';
				$data[] =  $_SESSION['pay_statistics']['transaction_value'].'%';
			}
			else if ($_SESSION['pay_statistics']['transaction_type'] == 1)
			{
				$query .= "AND transaction_extern LIKE %s ";
				$data_types[] = 'text';
				$data[] =  '%'.$_SESSION['pay_statistics']['transaction_value'];
			}
		}
		if ($_SESSION['pay_statistics']['customer'] != '')
		{
			$query .= "AND ud.login LIKE %s ";
			$data_types[] = 'text';
			$data[] =  '%'.$_SESSION['pay_statistics']['customer'].'%';
		}

		if ($_SESSION['pay_statistics']['from']['date']['d'] != '' &&
			$_SESSION['pay_statistics']['from']['date']['m'] != '' &&
			$_SESSION['pay_statistics']['from']['date']['y'] != '')
		{

			$from = mktime(0, 0, 0, $_SESSION['pay_statistics']['from']['date']['m'],
						   $_SESSION['pay_statistics']['from']['date']['d'],
						   $_SESSION['pay_statistics']['from']['date']['y']);
			$query .= 'AND order_date >= %s ';
			$data_types[] = 'integer';
			$data[] =  $from;
		}
		if ($_SESSION['pay_statistics']['til']['date']['d'] != '' &&
			$_SESSION['pay_statistics']['til']['date']['m'] != '' &&
			$_SESSION['pay_statistics']['til']['date']['y'] != '')
		{
			$til = mktime(23, 59, 59, $_SESSION['pay_statistics']['til']['date']['m'],
						  $_SESSION['pay_statistics']['til']['date']['d'],
						  $_SESSION['pay_statistics']['til']['date']['y']);
			$query .= 'AND order_date <= %s ';
			$data_types[] = 'integer';
			$data[] =  $til;
		}
		if ($_SESSION['pay_statistics']['payed'] == '0' ||
			$_SESSION['pay_statistics']['payed'] == '1')
		{
			$query .= 'AND payed = %s ';
			$data_types[] = 'integer';
			$data[] =  $_SESSION['pay_statistics']['payed'];
		}
		if ($_SESSION['pay_statistics']['access'] == '0' ||
			$_SESSION['pay_statistics']['access'] == '1')
		{
			$query .= 'AND access_granted = %s ';
			$data_types[] = 'integer';
			$data[] =  $_SESSION['pay_statistics']['access'];
		}
		if ($_SESSION['pay_statistics']['pay_method'] == '1' ||
			$_SESSION['pay_statistics']['pay_method'] == '2' ||
			$_SESSION['pay_statistics']['pay_method'] == '3')
		{
			$query .= 'AND b_pay_method = %s ';
			$data_types[] = 'integer';
			$data[] =  $_SESSION['pay_statistics']['pay_method'];
		}

		if(!$this->admin_view)
		{
			$vendors = $this->__getVendorIds();

			if (is_array($vendors) &&
				count($vendors) > 1)
			{
                $query .= ' AND '.$this->db->in('ps.b_vendor_id', $vendors, false, 'integer').' ';
			}
			else if(is_array($vendors) && count($vendors) == 1)
			{
				$query .= 'AND ps.b_vendor_id = %s ';
				$data[] = $vendors['0'];
				$data_types[] = 'integer';
			}
			if((int)$_SESSION['pay_statistics']['filter_title_id'])
			{
				$query .= "AND po.ref_id = ".(int)$_SESSION['pay_statistics']['filter_title_id']." ";
			}
		}
		else
		{
			if($_SESSION['pay_statistics']['vendor'])
			{
				$query .= 'AND udv.login LIKE %s ';

				$data[] = '%'.$_SESSION['pay_statistics']['vendor'].'%';
				$data_types[] = 'text';
			}

			if((int)$_SESSION['pay_statistics']['adm_filter_title_id'])
			{
				$query .= "AND po.ref_id = ".(int)$_SESSION['pay_statistics']['adm_filter_title_id']." ";
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

		while($row = $this->db->fetchAssoc($res))
		{
			$this->bookings[$row['booking_id']] = $row;
		}/**/
		return true;
	}

	public function __getVendorIds()
	{
		if(ilPaymentVendors::_isVendor($this->user_id))
		{
			$vendors[] = $this->user_id;
		}

		$vend = ilPaymentTrustees::_getVendorsForStatisticsByTrusteeId($this->user_id)	;
	
		if(isset ($vend))
		{
			foreach($vend as $v)
			{
				if(ilPaymentTrustees::_hasStatisticPermissionByVendor($this->user_id,$v))
				{
					$vendors[] = $v;
				}
			}
		}
		return $vendors ? $vendors : array();
	}
	

 	public static function __readBillByTransaction($a_user_id, $a_transaction_nr)
	{
		global $ilDB;
	
		$query = 'SELECT * FROM payment_statistic as ps, payment_objects as po 
					WHERE ps.pobject_id = po.pobject_id
					AND customer_id = %s
					AND transaction = %s';
		
		$res = $ilDB->queryF($query, array('integer','text'), array($a_user_id, $a_transaction_nr));
		while($row = $ilDB->fetchAssoc($res))
		{	
			$bookings[] = $row;
		}
		
		return $bookings;
	}	
		
	public function getDistinctTransactions($a_user_id)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM payment_statistic
			WHERE customer_id = %s
			GROUP BY transaction
			ORDER BY order_date DESC';

		$res = $ilDB->queryF($query, array('integer'), array($a_user_id));
		while($row = $ilDB->fetchAssoc($res))
		{
			$booking[$row['booking_id']] = $row;
		}
		return $booking ? $booking : array();
	}
 	
	public function getBookingsByPaymethod($pay_method)
	{
		global $ilDB;
				
		$res = $ilDB->queryF('
		SELECT * FROM payment_statistic WHERE b_pay_method = %s', array('integer'), array($pay_method));
		
		while($row = $ilDB->fetchAssoc($res))
		{
			$booking[] = $row;
		}
		return $booking ? $booking : array();
	}
	
	public function deleteAddressesByPaymethod($pay_method)
	{
		global $ilDB;

		$ilDB->manipulateF('
			UPDATE payment_statistic 
			SET street = null,
				po_box = null,
				city = null,
				zipcode = null,
				country = null
			WHERE b_pay_method = %s',
		array('integer'),
		array($pay_method));
	}
	
	public static function _readBookingByTransaction($a_transaction)
	{
		global $ilDB;
		
		$trans_exp = explode('_', $a_transaction);
		$user_id = $trans_exp[1];
		
		$res = $ilDB->queryF('SELECT * FROM payment_statistic 
		WHERE transaction = %s 
		AND payed = %s
		AND access_granted = %s
		AND customer_id = %s',
		array('text', 'integer','integer','integer'),
		array($a_transaction, 1,1, (int)$user_id));
		if($row = $ilDB->numRows($res))
		{
			return true;	
		}
		return false;
	}	
		
	public function getUniqueTitles()
	{
		$query = 'SELECT DISTINCT(po.ref_id) ref_id FROM payment_statistic ps, payment_objects po';		
		$query .= " WHERE ps.pobject_id = po.pobject_id ";
		
			if(!$this->admin_view)
		{
			$vendors = $this->__getVendorIds();
	
			if (is_array($vendors) && count($vendors) > 1)
			{
				foreach($vendors as $vendor)
				{
					$arr_data[] = '%s';
					$data[] = $vendor;
					$data_types[] = 'integer';
				}
				$str_data = implode(',',$arr_data);
				
				$query .= 'AND ps.b_vendor_id IN ('.$str_data.') ';
				
			}
			else if(is_array($vendors) && count($vendors) == 1)
			{
				$query .= 'AND ps.b_vendor_id = %s ';
				$data[] = $vendors['0'];
				$data_types[] = 'integer';				 			
			}
		}
		$query .= "ORDER BY order_date DESC";

		if(!$data_types && !$data)
		{
			$res = $this->db->query($query);
		}
		else $res = $this->db->queryf($query, $data_types, $data);

		$rows = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rows[]	= $row->ref_id;
		}
		return is_array($rows) ? $rows : array();
	}

	public static function _hasAccesstoExtensionPrice($a_user_id, $a_pobject_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM payment_statistic
			WHERE customer_id = %s AND pobject_id = %s
			AND duration > %s',
		array('integer','integer', 'integer'),
		array($a_user_id, $a_pobject_id, 0));

		if($row = $ilDB->numRows($res))
		{
			return true;
		}
		return false;
	}
	public static function _lookupOrder($a_pobject_id)
	{
		global $ilUser, $ilDB;

		$booking = array();
		$res = $ilDB->queryF('
			SELECT order_date, duration
			FROM payment_statistic
			WHERE pobject_id = %s
			AND customer_id = %s
			ORDER BY order_date DESC',
			array('integer', 'integer'),
			array($a_pobject_id, $ilUser->getId()));

		while($row = $ilDB->fetchAssoc($res))
		{
			$booking =  $row;
			break;
		}

		return $booking;
	}
}