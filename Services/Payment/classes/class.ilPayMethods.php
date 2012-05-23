<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
 
/**
* Class ilObjPaymentSettingsGUI
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id: 
*
*/


class ilPayMethods
{
	public $pm_id;
	public $pm_title;
	public $pm_enabled;
	public $save_usr_adr;
	
	
	public function __construct($a_pm_id = 0)
	{
		global $ilDB;
		$this->db = $ilDB;
		
		if($a_pm_id != 0)
		{
			$this->pm_id = $a_pm_id;
			$this->read();	
		} 
		else $this->readAll();
	}
	
	// Setter / Getter
	public function setPmId($a_pm_id)
	{
		$this->pm_id = $a_pm_id;
	}
	public function getPmId()
	{
		return $this->pm_id;
	}

	public function setPmTitle($a_pm_title)
	{
		$this->pm_title = $a_pm_title;
	}
	public function getPmTitle()
	{
		return $this->pm_title;
	}	
	
	function setPmEnabled($a_pm_enabled)
	{
		$this->pm_enabled = $a_pm_enabled;
	}
	public function getPmEnabled()
	{
		return $this->pm_enabled;
	}

	public function setSaveUserAddress($a_save_usr_adr)
	{
		$this->save_usr_adr = $a_save_usr_adr;
	}
	public function getSaveUserAddress()
	{
		return $this->save_usr_adr;
	}

	public static function countPM()
	{
		global $ilDB;
		
		$res = $ilDB->query('SELECT count(pm_id) cnt FROM payment_paymethods');
		$row = $ilDB->fetchAssoc($res);
		
		return $row['cnt']; 
		
	}
	public function read()
	{
		$res = $this->db->queryF('SELECT * FROM payment_paymethods WHERE pm_id = %s',
		array('integer'), array($this->getPmId()));
	

		while($row = $this->db->fetchAssoc($res))
		{
			$this->setPmTitle($row['pm_title']);
			$this->setPmEnabled($row['pm_enabled']);
			$this->setSaveUserAddress($row['save_usr_adr']);
		} 
		return true;
	}
	
	public function readAll()
	{
		$res = $this->db->query('SELECT * FROM payment_paymethods');
		$paymethods = array();
		$counter = 0;
		while($row = $this->db->fetchAssoc($res))
		{
			$paymethods[$counter]['pm_id'] = $row['pm_id'];
			$paymethods[$counter]['pm_title'] = $row['pm_title'];
			$paymethods[$counter]['pm_enabled'] = $row['pm_enabled'];
			$paymethods[$counter]['save_usr_adr'] = $row['save_usr_adr'];
			$counter++;
		} 
		return $paymethods;
	}

	public static function _PMEnabled($a_id)
	{
		global $ilDB; 
		
		$res = $ilDB->queryF('SELECT pm_enabled FROM payment_paymethods WHERE pm_id = %s',
		array('integer'), array($a_id));
		
		$row = $ilDB->fetchAssoc($res);

		return (int)$row['pm_enabled'];
		
	}

	public static function _PMdisable($a_id)
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('UPDATE payment_paymethods SET pm_enabled = 0
			WHERE pm_id = %s',
			array('integer'), array($a_id));
	}
	
	public static function _PMenable($a_id)
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('UPDATE payment_paymethods SET pm_enabled = 1
			WHERE pm_id = %s',
			array('integer'), array($a_id));
	}

	public static function _PMdisableAll()
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('UPDATE payment_paymethods SET pm_enabled = %s',
			array('integer'), array('0'));	
	}


	public static function _disableSaveUserAddress($a_id)
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('UPDATE payment_paymethods SET save_usr_adr = 0
			WHERE pm_id = %s',
			array('integer'), array($a_id));
	}

	public static function _enableSaveUserAddress($a_id)
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('UPDATE payment_paymethods SET save_usr_adr = 1
			WHERE pm_id = %s',
			array('integer'), array($a_id));
	}

	public static function _EnabledSaveUserAddress($a_id)
	{
		global $ilDB; 
		
		$res = $ilDB->queryF('SELECT save_usr_adr FROM payment_paymethods WHERE pm_id = %s',
		array('integer'), array($a_id));
		
		$row = $ilDB->fetchAssoc($res);

		return (int)$row['save_usr_adr'];
		
	}
	public static function _getIdByTitle($a_pm_title)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('SELECT pm_id FROM payment_paymethods WHERE pm_title = %s',
		array('text'), array($a_pm_title));
		
		$row = $ilDB->fetchAssoc($res);
		return (int)$row['pm_id'];
	}
	public static function _getTitleById($a_pm_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('SELECT pm_title FROM payment_paymethods WHERE pm_id = %s',
		array('integer'), array($a_pm_id));
		
		$row = $ilDB->fetchAssoc($res);
		return $row['pm_title'];
		
	}

	public static function getStringByPaymethod($a_type)
	{	
		global $lng;
	
		switch($a_type)
		{
			case '1':
			case 'pm_bill':
			case 'bill':
			case 'PAY_METHOD_BILL': return $lng->txt('pays_bill');
				break;
				
			case '2':
			case 'pm_bmf':
			case 'bmf':
			case 'PAY_METHOD_BMF': return $lng->txt('pays_bmf');
				break;
				
			case '3':
			case 'pm_paypal':
			case 'paypal':				
			case 'PAY_METHOD_PAYPAL': return $lng->txt('pays_paypal');
				break;
				
			case '4':
			case 'pm_epay':
			case 'epay':	
			case 'PAY_METHOD_EPAY': return $lng->txt('pays_epay');
				break;
			case 'PAY_METHOD_NOT_SPECIFIED': return $lng->txt('paya_pay_method_not_specified');
				break;
			default:
				return $lng->txt('paya_pay_method_not_specified');
				break;				
		}
	}
	
/*
 * This function returns all available paymethods for SelectInputs in GUIs
 */
	public static function getPayMethodsOptions($type = 0)
	{
		global $ilDB, $lng;
		
		$res = $ilDB->query('SELECT * FROM payment_paymethods WHERE pm_enabled = 1');
		$options = array();
		
		//this is only for additional entries in SelectInputGUIs
		switch($type)
		{
			case 'all':	
				$options['all'] =  $lng->txt('pay_all');
				break;
			case 'not_specified': 	
				$options[0] =  $lng->txt('paya_pay_method_not_specified');
				break;
			//default: break;
		}
		
		while($row = $ilDB->fetchAssoc($res))
		{
			$options[$row['pm_id']] = ilPayMethods::getStringByPaymethod($row['pm_title']);
		}		
	
		return $options;
	}

	public static function _getActivePaymethods()
	{
		global $ilDB;

		$pm = array();
		
		$res = $ilDB->queryf('SELECT * FROM payment_paymethods WHERE pm_enabled = %s',
				array('integer'), array(1));

		while($row = $ilDB->fetchAssoc($res))
		{
			$pm[] = $row;
		}
		return $pm;
	}
}
?>