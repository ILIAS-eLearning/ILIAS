<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilPaymentSettings
{
	private static $_instance;
	
	public $db;
	public $setting = array();
	
	public static function _getInstance()
	{
		if(!isset(self::$_instance))
		{
			self::$_instance = new ilPaymentSettings();
		}
		
		return self::$_instance;
	}

	private function __construct()
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->__getSettings();
	}	

	private function __getSettings()
	{
		$res = $this->db->query('SELECT * FROM payment_settings');

		$this->setting = array();
		while($row = $this->db->fetchAssoc($res))
		{
			$this->setting[$row["keyword"]] = $row["value"];
		}
	}

	public function getAll()
	{
		return $this->setting;
	}

	public function get($a_key)
	{
		return $this->setting[$a_key];
	}

	public function getValuesByScope($a_scope)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('
			SELECT * FROM payment_settings
			WHERE scope = %s',
			array('text'), array($a_scope));
		
		$settings = array();
		while($row = $this->db->fetchAssoc($res))
		{
			$settings[$row["keyword"]] = $row["value"];
		}
		return $settings;
	}

	/**
	 *
	 * @global <type> $ilDB
	 * @param <text> $a_scope  // define a payment-scope for settings (i.e. invoice, gui, common ... )
	 * @param <text> $a_key
	 * @param <text> $a_val
	 * @return <type>
	 */
	public function set($a_key, $a_val, $a_scope = null)
	{
		global $ilDB;
		
		if($a_scope == null)
		{
			// check if scope is already set
			$res = $ilDB->queryF('
				SELECT scope FROM payment_settings
				WHERE keyword = %s',
				array('text'), array($a_key));
			
			$row = $ilDB->fetchAssoc($res);
			$a_scope = $row['scope'];
			
		}

		self::delete($a_key);

		$ilDB->insert("payment_settings", array(
			"keyword" => array("text", $a_key),
			"value" => array("clob", $a_val),
			"scope" => array("text", $a_scope)));

		self::$_instance->setting[$a_key] = $a_val;
		
		return true;
	}

	public function delete($a_key)
	{
		global $ilDB;

		$ilDB->manipulateF('
			DELETE FROM payment_settings
			WHERE keyword = %s',
			array('text'), array($a_key));
	}

	public static function _isPaymentEnabled()
	{
		if(!isset(self::$_instance))
		{
			self::_getInstance();
		}
		
		return self::$_instance->setting['shop_enabled'];
	}
	public static function setMailUsePlaceholders($a_mail_use_placeholders)
	{
		self::set('mail_use_placeholders',$a_mail_use_placeholders);
	}

	public static function getMailUsePlaceholders()
	{
		if(!isset(self::$_instance))
		{
			self::_getInstance();
		}

		return self::$_instance->setting['mail_use_placeholders'];
	}

	public static function setMailBillingText($a_mail_billing_text)
	{
		self::set('mail_billing_text',$a_mail_billing_text);
	}

	public static function getMailBillingText()
	{
		if(!isset(self::$_instance))
		{
			self::_getInstance();
		}

		return self::$_instance->setting['mail_billing_text'];

	}

	public static function useShopSpecials()
	{
		if(!isset(self::$_instance))
		{
			self::_getInstance();
		}

		return self::$_instance->setting['use_shop_specials'];

	}
}
