<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

class ilGeneralSettings
{
	private static $_instance;
	
	var $db;
	var $settings;
	
	public static function _getInstance()
	{
		if(!isset(self::$_instance))
		{
			self::$_instance = new ilGeneralSettings();
		}
		
		return self::$_instance;
	}

	function ilGeneralSettings()
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->__getSettings();
	}	
	
	/** 
	 * Fetches and sets the primary key of the payment settings
	 *
	 * @access	private
	 */
	private function fetchSettingsId()
	{
		$result = $this->db->query('SELECT settings_id FROM payment_settings');		
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))	
		{	
			$this->setSettingsId($row->settings_id);
		}
	}
	
	public function setSettingsId($a_settings_id = 0)
	{
		$this->settings_id = $a_settings_id;

	}
	public function getSettingsId()
	{
		return $this->settings_id;
	}
	
	function get($a_type)
	{
		return $this->settings[$a_type];
	}

	function getAll()
	{
		return $this->settings;
	}

	function clearAll()
	{
		$statement = $this->db->manipulateF('
			UPDATE payment_settings
			SET	currency_unit = %s,
				currency_subunit = %s,
				address = %s,
				bank_data = %s,
				add_info = %s,
				pdf_path = %s,
				topics_sorting_type = %s,
				topics_sorting_direction = %s,
				topics_allow_custom_sorting = %s,
				max_hits = %s,
				shop_enabled = %s,
				save_customer_address_enabled = %s,
hide_advanced_search = %s,
hide_filtering = %s,
hide_coupons = %s,
hide_news = %s
			WHERE settings_id = %s', 
			array( 	'text', 
					'text', 
					'text',
					'text', 
					'text',
					'text', 
					'integer', 
					'integer', 
					'text', 
					'integer', 
					'integer', 
					'integer',
					'integer',
				'integer',
				'integer', 
				'integer',
				'integer'),
			array(	NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					'1',
					'asc',
					'0',
					'20',
					'0',
					'0',
				'0',
				'0',
				'0',
				'0',
					$this->getSettingsId())
			);

		return true;
	}
		
	function setAll($a_values)
	{	
		global $ilDB;

		if ($this->getSettingsId())
		{	
			/**/
			if(!$a_values['currency_unit']) 	$a_values['currency_unit'] = NULL;
			if(!$a_values['currency_subunit']) 	$a_values['currency_subunit'] = NULL;
			if(!$a_values['address']) 			$a_values['address'] = NULL;
			if(!$a_values['bank_data']) 		$a_values['bank_data'] = NULL;
			if(!$a_values['add_info']) 			$a_values['add_info'] = NULL;
			if(!$a_values['pdf_path']) 			$a_values['pdf_path'] = NULL;
			
			if(!$a_values['topics_allow_custom_sorting']) $a_values['topics_allow_custom_sorting'] = 0;
			if(!$a_values['topics_sorting_type']) 		$a_values['topics_sorting_type'] = 0;
			if(!$a_values['topics_sorting_direction']) 	$a_values['topics_sorting_direction'] = NULL;
			if(!$a_values['shop_enabled']) 				$a_values['shop_enabled'] = 0;
			if(!$a_values['max_hits']) 					$a_values['max_hits'] = 0;
			if(!$a_values['save_customer_address_enabled']) $a_values['save_customer_address_enabled'] = 0;	
			if(!$a_values['hide_advanced_search']) $a_values['hide_advanced_search'] = 0;
			if(!$a_values['hide_filtering']) $a_values['hide_filtering'] = 0;
			if(!$a_values['hide_coupons']) $a_values['hide_coupons'] = 0;
			if(!$a_values['hide_news']) $a_values['hide_news'] = 0;
			/**/				
			
			$statement = $this->db->manipulateF('
				UPDATE payment_settings
				SET	currency_unit = %s,
					currency_subunit = %s,
					address = %s,
					bank_data = %s,
					add_info = %s,
					pdf_path = %s,
					topics_sorting_type = %s,
					topics_sorting_direction = %s,
					topics_allow_custom_sorting = %s,
					max_hits = %s,
					shop_enabled = %s,
					save_customer_address_enabled = %s,
hide_advanced_search = %s,
hide_filtering = %s,
hide_coupons = %s,
hide_news = %s
				WHERE settings_id = %s', 
				array( 'text', 
						'text', 
						'text', 
						'text',
						'text', 
						'text', 
						'integer', 
						'text',
						'integer', 
						'integer', 
						'integer', 
						'integer',
						'integer',
'integer',
'integer',
'integer',
'integer'),
				array(
					$a_values['currency_unit'],
					$a_values['currency_subunit'],
					$a_values['address'],
					$a_values['bank_data'],
					$a_values['add_info'],
					$a_values['pdf_path'],
					$a_values['topics_sorting_type'],
					$a_values['topics_sorting_direction'],					
					$a_values['topics_allow_custom_sorting'],					
					$a_values['max_hits'],
					$a_values['shop_enabled'],
					$a_values['save_customer_address_enabled'],
					$a_values['hide_advanced_search'],
					$a_values['hide_filtering'],
					$a_values['hide_coupons'],
					$a_values['hide_news'],
					$this->getSettingsId())
			);
		}
		else
		{ 
			
			/**/
			if(!$a_values['currency_unit']) 	$a_values['currency_unit'] = NULL;
			if(!$a_values['currency_subunit']) 	$a_values['currency_subunit'] = NULL;
			if(!$a_values['address']) 			$a_values['address'] = NULL;
			if(!$a_values['bank_data']) 		$a_values['bank_data'] = NULL;
			if(!$a_values['add_info']) 			$a_values['add_info'] = NULL;

			if(!$a_values['pdf_path']) 			$a_values['pdf_path'] = NULL;
			
			if(!$a_values['topics_allow_custom_sorting']) $a_values['topics_allow_custom_sorting'] = 0;
			if(!$a_values['topics_sorting_type']) 		$a_values['topics_sorting_type'] = 0;
			if(!$a_values['topics_sorting_direction']) 	$a_values['topics_sorting_direction'] = NULL;
			if(!$a_values['shop_enabled']) 				$a_values['shop_enabled'] = 0;
			if(!$a_values['max_hits']) 					$a_values['max_hits'] = 0;
			if(!$a_values['save_customer_address_enabled']) $a_values['save_customer_address_enabled'] = 0;	
			if(!$a_values['hide_advanced_search']) $a_values['hide_advanced_search'] = 0;
			if(!$a_values['hide_filtering']) $a_values['hide_filtering'] = 0;
			if(!$a_values['hide_coupons']) $a_values['hide_coupons'] = 0;
			if(!$a_values['hide_news']) $a_values['hide_news'] = 0;

			
			$next_id = $ilDB->nextId('payment_settings');
			$statement = $this->db->manipulateF('
				INSERT INTO payment_settings
				( 	settings_id,
					currency_unit,
					currency_subunit,
					address,
					bank_data,
					add_info,
					pdf_path,
					topics_allow_custom_sorting,
					topics_sorting_type,
					topics_sorting_direction,
					shop_enabled,
					max_hits,
					save_customer_address_enabled,
hide_advanced_search,
hide_filtering,
hide_coupons,
hide_news
				)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
				array( 'integer',
						'text', 
						'text', 
						'text', 
						'text', 
						'text', 
						'text', 
						'integer', 
						'integer', 
						'text', 
						'integer', 
						'integer', 
						'integer',
				       'integer',
				       'integer',
				       'integer',
				       'integer'),
				array(
					$next_id,
					$a_values['currency_unit'],
					$a_values['currency_subunit'],
					$a_values['address'],
					$a_values['bank_data'],
					$a_values['add_info'],
					$a_values['pdf_path'],
					$a_values['topics_allow_custom_sorting'],
					$a_values['topics_sorting_type'],
					$a_values['topics_sorting_direction'],
					$a_values['shop_enabled'],
					$a_values['max_hits'],
					$a_values['save_customer_address_enabled'],
$a_values['hide_advanced_search'],
$a_values['hide_filtering'],
					$a_values['hide_coupons'],
					$a_values['hide_news']
					)
			);
		}		

		$this->__getSettings();

		return true;
	}

	function __getSettings()
	{
		$this->fetchSettingsId();

		$result = $this->db->query('SELECT * FROM payment_settings');
		 
		$data = array();	
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))	
		{	
			$data['currency_unit'] = $row->currency_unit;
			$data['currency_subunit'] = $row->currency_subunit;
			$data['address'] = $row->address;
			$data['bank_data'] = $row->bank_data;
			$data['add_info'] = $row->add_info;
			$data['pdf_path'] = $row->pdf_path;
			$data['topics_allow_custom_sorting'] = $row->topics_allow_custom_sorting;
			$data['topics_sorting_type'] = $row->topics_sorting_type;
			$data['topics_sorting_direction'] = $row->topics_sorting_direction;
			$data['max_hits'] = $row->max_hits;
			$data['shop_enabled'] = $row->shop_enabled;
			$data['save_customer_address_enabled'] = $row->save_customer_address_enabled;
				$data['hide_advanced_search'] = $row->hide_advanced_search;
				$data['hide_filtering'] = $row->hide_filtering;
				$data['hide_coupons'] = $row->hide_coupons;
				$data['hide_news'] = $row->hide_news;
		}
		$this->settings = $data;
	}
}
?>