<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once '.Services/Payment/classes/class.ilPaymentSettings.php';

// !!! USE ilPaymentSettings instead !!!
class ilGeneralSettings 
{
	private static $_instance;
	
	public $db;
	public $settings;
	
	public static function _getInstance()
	{
		if(!isset(self::$_instance))
		{
			self::$_instance = new ilGeneralSettings();
		}
		
		return self::$_instance;
	}

	public function ilGeneralSettings()
	{
		$pSettings = ilPaymentSettings::_getInstance();
		$this->settings = $pSettings->getAll();

//		global $ilDB;
//
//		$this->db = $ilDB;
//
//		$this->__getSettings();
	}	
//
//	/**
//	 * Fetches and sets the primary key of the payment settings
//	 *
//	 * @access	private
//	 */
//	private function fetchSettingsId()
//	{
//		$result = $this->db->query('SELECT settings_id FROM payment_settings');
//		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
//		{
//			$this->setSettingsId($row->settings_id);
//		}
//	}
//
//	public function setSettingsId($a_settings_id = 0)
//	{
//		$this->settings_id = $a_settings_id;
//
//	}
//	public function getSettingsId()
//	{
//		return $this->settings_id;
//	}
//
//	public function get($a_type)
//	{
//		return $this->settings[$a_type];
//	}
//
//	public function getAll()
//	{
//		return $this->settings;
//	}
//
//	public function clearAll()
//	{
//		$statement = $this->db->manipulateF('
//			UPDATE payment_settings
//			SET	currency_unit = %s,
//				currency_subunit = %s,
//				address = %s,
//				bank_data = %s,
//				add_info = %s,
//				pdf_path = %s,
//				topics_sorting_type = %s,
//				topics_sorting_direction = %s,
//				topics_allow_custom_sorting = %s,
//				max_hits = %s,
//				shop_enabled = %s,
//				hide_advanced_search = %s,
//				objects_allow_custom_sorting = %s,
//				hide_coupons = %s,
//				hide_news = %s,
//				hide_shop_info = %s,
//				use_shop_specials = %s
//			WHERE settings_id = %s',
//			array( 	'text',
//					'text',
//					'text',
//					'text',
//					'text',
//					'text',
//					'integer',
//					'integer',
//					'text',
//					'integer',
//					'integer',
//					'integer',
//					'integer',
//					'integer',
//					'integer',
//					'integer',
//					'integer',
//					'integer'),
//			array(	NULL,
//					NULL,
//					NULL,
//					NULL,
//					NULL,
//					NULL,
//					'1',
//					'asc',
//					'0',
//					'20',
//					'0',
//					'0',
//					'0',
//					'0',
//					'0',
//					'0',
//					'0',
//					$this->getSettingsId())
//			);
//
//		return true;
//	}
//
//	public function setAll($a_values)
//	{
//		global $ilDB;
//
//		if(!$a_values['currency_unit']) 	$a_values['currency_unit'] = NULL;
//		if(!$a_values['currency_subunit']) 	$a_values['currency_subunit'] = NULL;
//		if(!$a_values['address']) 			$a_values['address'] = NULL;
//		if(!$a_values['bank_data']) 		$a_values['bank_data'] = NULL;
//		if(!$a_values['add_info']) 			$a_values['add_info'] = NULL;
//		if(!$a_values['pdf_path']) 			$a_values['pdf_path'] = NULL;
//
//		if(!$a_values['topics_allow_custom_sorting']) $a_values['topics_allow_custom_sorting'] = 0;
//		if(!$a_values['topics_sorting_type']) 		$a_values['topics_sorting_type'] = 0;
//		if(!$a_values['topics_sorting_direction']) 	$a_values['topics_sorting_direction'] = NULL;
//		if(!$a_values['shop_enabled']) 				$a_values['shop_enabled'] = 0;
//		if(!$a_values['max_hits']) 					$a_values['max_hits'] = 0;
//		if(!$a_values['hide_advanced_search']) $a_values['hide_advanced_search'] = 0;
//		if(!$a_values['objects_allow_custom_sorting']) $a_values['objects_allow_custom_sorting'] = 0;
//		if(!$a_values['hide_coupons']) $a_values['hide_coupons'] = 0;
//		if(!$a_values['hide_news']) $a_values['hide_news'] = 0;
//		if(!$a_values['hide_shop_info']) $a_values['hide_shop_info'] = 0;
//		if(!$a_values['use_shop_specials']) $a_values['use_shop_specials'] = 0;
//		if(!$a_values['show_general_filter']) $a_values['show_general_filter'] = 0;
//		if(!$a_values['show_topics_filter']) $a_values['show_topics_filter'] = 0;
//		if(!$a_values['show_shop_explorer']) $a_values['show_shop_explorer'] = 0;
//
//		if ($this->getSettingsId())
//		{
//
//			$statement = $this->db->manipulateF('
//				UPDATE payment_settings
//				SET	currency_unit = %s,
//					currency_subunit = %s,
//					address = %s,
//					bank_data = %s,
//					add_info = %s,
//					pdf_path = %s,
//					topics_sorting_type = %s,
//					topics_sorting_direction = %s,
//					topics_allow_custom_sorting = %s,
//					max_hits = %s,
//					shop_enabled = %s,
//					hide_advanced_search = %s,
//					objects_allow_custom_sorting = %s,
//					hide_coupons = %s,
//					hide_news = %s,
//					hide_shop_info = %s,
//					use_shop_specials = %s,
//					show_general_filter = %s,
//					show_topics_filter = %s,
//					show_shop_explorer = %s
//				WHERE settings_id = %s',
//				array( 'text',
//						'text',
//						'text',
//						'text',
//						'text',
//						'text',
//						'integer',
//						'text',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer',
//						'integer'),
//				array(
//					$a_values['currency_unit'],
//					$a_values['currency_subunit'],
//					$a_values['address'],
//					$a_values['bank_data'],
//					$a_values['add_info'],
//					$a_values['pdf_path'],
//					$a_values['topics_sorting_type'],
//					$a_values['topics_sorting_direction'],
//					$a_values['topics_allow_custom_sorting'],
//					$a_values['max_hits'],
//					$a_values['shop_enabled'],
//					$a_values['hide_advanced_search'],
//					$a_values['objects_allow_custom_sorting'],
//					$a_values['hide_coupons'],
//					$a_values['hide_news'],
//					$a_values['hide_shop_info'],
//					$a_values['use_shop_specials'],
//					$a_values['show_general_filter'],
//					$a_values['show_topics_filter'],
//					$a_values['show_shop_explorer'],
//					$this->getSettingsId())
//			);
//		}
//		else
//		{
//
//			$next_id = $ilDB->nextId('payment_settings');
//			$statement = $this->db->manipulateF('
//				INSERT INTO payment_settings
//				( 	settings_id,
//					currency_unit,
//					currency_subunit,
//					address,
//					bank_data,
//					add_info,
//					pdf_path,
//					topics_allow_custom_sorting,
//					topics_sorting_type,
//					topics_sorting_direction,
//					shop_enabled,
//					max_hits,
//					hide_advanced_search,
//					objects_allow_custom_sorting,
//					hide_coupons,
//					hide_news,
//					hide_shop_info,
//					use_shop_specials,
//					show_general_filter,
//					show_topics_filter,
//					show_shop_explorer
//				)
//				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
//				array( 'integer',
//						'text',
//						'text',
//						'text',
//						'text',
//						'text',
//						'text',
//						'integer',
//						'integer',
//						'text',
//						'integer',
//						'integer',
//				       'integer',
//				       'integer',
//				       'integer',
//						'integer',
//				       'integer',
//				       'integer',
//				       'integer',
//					'integer',
//					'integer'
//						),
//				array(
//					$next_id,
//					$a_values['currency_unit'],
//					$a_values['currency_subunit'],
//					$a_values['address'],
//					$a_values['bank_data'],
//					$a_values['add_info'],
//					$a_values['pdf_path'],
//					$a_values['topics_allow_custom_sorting'],
//					$a_values['topics_sorting_type'],
//					$a_values['topics_sorting_direction'],
//					$a_values['shop_enabled'],
//					$a_values['max_hits'],
//					$a_values['hide_advanced_search'],
//					$a_values['objects_allow_custom_sorting'],
//					$a_values['hide_coupons'],
//					$a_values['hide_news'],
//					$a_values['hide_shop_info'],
//					$a_values['use_shop_specials'],
//					$a_values['show_general_filter'],
//					$a_values['show_topics_filter'],
//					$a_values['show_shop_explorer']
//
//					)
//			);
//		}
//
//		$this->__getSettings();
//
//		return true;
//	}
//
//	private function __getSettings()
//	{
//		$this->fetchSettingsId();
//
//		$result = $this->db->query('SELECT * FROM payment_settings');
//
//		$data = array();
//		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
//		{
//			$data['currency_unit'] = $row->currency_unit;
//			$data['currency_subunit'] = $row->currency_subunit;
//			$data['address'] = $row->address;
//			$data['bank_data'] = $row->bank_data;
//			$data['add_info'] = $row->add_info;
//			$data['pdf_path'] = $row->pdf_path;
//			$data['topics_allow_custom_sorting'] = $row->topics_allow_custom_sorting;
//			$data['topics_sorting_type'] = $row->topics_sorting_type;
//			$data['topics_sorting_direction'] = $row->topics_sorting_direction;
//			$data['max_hits'] = $row->max_hits;
//			$data['shop_enabled'] = $row->shop_enabled;
//			$data['hide_advanced_search'] = $row->hide_advanced_search;
//			$data['objects_allow_custom_sorting'] = $row->objects_allow_custom_sorting;
//			$data['hide_coupons'] = $row->hide_coupons;
//			$data['hide_news'] = $row->hide_news;
//			$data['hide_shop_info'] = $row->hide_shop_info;
//			$data['use_shop_specials'] = $row->use_shop_specials;
//			$data['show_general_filter'] = $row->show_general_filter;
//			$data['show_topics_filter'] = $row->show_topics_filter;
//			$data['show_shop_explorer'] = $row->show_shop_explorer;
//		}
//		$this->settings = $data;
//
//	}
//
//	public static function _isPaymentEnabled()
//	{
//		global $ilDB;
//
//		$res = $ilDB->query('SELECT shop_enabled FROM payment_settings');
//		$row = $ilDB->fetchAssoc($res);
//
//		return $row['shop_enabled'];
//	}
//	public static function setMailUsePlaceholders($a_mail_use_placeholders)
//	{
//		global $ilDB;
//
//		$res = $ilDB->manipulateF('UPDATE payment_settings
//			SET mail_use_placeholders = %s',
//			array('integer'), array($a_mail_use_placeholders));
//}
//
//	public static function getMailUsePlaceholders()
//	{
//		global $ilDB;
//
//		$res = $ilDB->query('SELECT mail_use_placeholders FROM payment_settings');
//		$row = $ilDB->fetchAssoc($res);
//
//		return $row['mail_use_placeholders'];
//	}
//
//	public static function setMailBillingText($a_mail_billing_text)
//	{
//		global $ilDB;
//
//		$ilDB->update('payment_settings',
//			array('mail_billing_text'  => array('clob', $a_mail_billing_text)),
//			array('settings_id'	  => array('integer',1)));
//
//	}
//
//	public static function getMailBillingText()
//	{
//		global $ilDB;
//
//		$res = $ilDB->query('SELECT mail_billing_text FROM payment_settings');
//		$row = $ilDB->fetchAssoc($res);
//
//		return $row['mail_billing_text'];
//	}
//
//	public static function useShopSpecials()
//	{
//		global $ilDB;
//
//		$res = $ilDB->query('SELECT use_shop_specials FROM payment_settings');
//		$row = $ilDB->fetchAssoc($res);
//
//		return $row['use_shop_specials'];
//	}

}
?>