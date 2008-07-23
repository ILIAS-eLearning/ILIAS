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
		$query = "SELECT * FROM payment_settings";
		$result = $this->db->getrow($query);
		
		$this->setSettingsId($result->settings_id);
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
		$query = "UPDATE payment_settings ";
		$query .= "SET currency_unit = '', ";
		$query .= "currency_subunit = '', ";
		$query .= "address = '', ";
		$query .= "bank_data = '', ";
		$query .= "add_info = '', ";
		$query .= "vat_rate = '', ";
		$query .= "pdf_path = '', ";
		$query .= "topics_sorting_type = 1, ";
		$query .= "topics_sorting_direction = 'asc', ";
		$query .= "topics_allow_custom_sorting = 0, ";
		$query .= "max_hits = 20, ";
		$query .= "shop_enabled = 0 ";
		$query .= "WHERE settings_id = '" . $this->getSettingsId() . "'";
		
		$this->db->query($query);

		return true;
	}
		
	function setAll($a_values)
	{		
		global $ilDB;

		if ($this->getSettingsId())
		{		
			$query = "UPDATE payment_settings ";
			$query .= "SET currency_unit = " . $ilDB->quote($a_values["currency_unit"]) . ", ";
			$query .= "currency_subunit = " . $ilDB->quote($a_values["currency_subunit"]) . ", ";
			$query .= "address = " . $ilDB->quote($a_values["address"]) . ", ";
			$query .= "bank_data = " . $ilDB->quote($a_values["bank_data"]) . ", ";
			$query .= "add_info = " . $ilDB->quote($a_values["add_info"]) . ", ";
			$query .= "vat_rate = " . $ilDB->quote($a_values["vat_rate"]) . ", ";
			$query .= "pdf_path = " . $ilDB->quote($a_values["pdf_path"]) . ", ";
			$query .= "topics_allow_custom_sorting = " . $ilDB->quote($a_values["topics_allow_custom_sorting"]) . ", ";
			$query .= "topics_sorting_type = " . $ilDB->quote($a_values["topics_sorting_type"]) . ", ";
			$query .= "topics_sorting_direction = " . $ilDB->quote($a_values["topics_sorting_direction"]) . ", ";
			$query .= "max_hits = " . $ilDB->quote($a_values["max_hits"]) . ", ";
			$query .= "shop_enabled = " . $ilDB->quote($a_values["shop_enabled"]) . " ";
			$query .= "WHERE settings_id = '" . $this->getSettingsId() . "'";
			
			$this->db->query($query);
		}
		else
		{
			$query = "INSERT INTO payment_settings
					  SET 
					  currency_unit = ".$ilDB->quote($a_values["currency_unit"]).",
					  currency_subunit = ".$ilDB->quote($a_values["currency_subunit"]).",
					  address = ".$ilDB->quote($a_values["address"]).",
					  bank_data = ".$ilDB->quote($a_values["bank_data"]).",
					  add_info = ".$ilDB->quote($a_values["add_info"]).",
					  vat_rate = ".$ilDB->quote($a_values["vat_rate"]).",
					  pdf_path = ".$ilDB->quote($a_values["pdf_path"]).",
					  topics_allow_custom_sorting = ".$ilDB->quote($a_values["topics_allow_custom_sorting"]).",
					  topics_sorting_type = ".$ilDB->quote($a_values["topics_sorting_type"]).",
					  topics_sorting_direction = ".$ilDB->quote($a_values["topics_sorting_direction"]).",
					  shop_enabled = ".$ilDB->quote($a_values["shop_enabled"]).",				
					  max_hits = ".$ilDB->quote($a_values["topics_sorting_direction"]);
			$this->db->query($query);					
			
			$this->setSettingsId($this->db->getLastInsertId());
		}		

		$this->__getSettings();

		return true;
	}

	function __getSettings()
	{
		$this->fetchSettingsId();
		
		$query = "SELECT * FROM payment_settings";
		$result = $this->db->getrow($query);

		$data = array();
		if (is_object($result))
		{
			$data["currency_unit"] = $result->currency_unit;
			$data["currency_subunit"] = $result->currency_subunit;
			$data["address"] = $result->address;
			$data["bank_data"] = $result->bank_data;
			$data["add_info"] = $result->add_info;
			$data["vat_rate"] = $result->vat_rate;
			$data["pdf_path"] = $result->pdf_path;
			$data["topics_allow_custom_sorting"] = $result->topics_allow_custom_sorting;
			$data["topics_sorting_type"] = $result->topics_sorting_type;
			$data["topics_sorting_direction"] = $result->topics_sorting_direction;
			$data["max_hits"] = $result->max_hits;
			$data["shop_enabled"] = $result->shop_enabled;				
		}

		$this->settings = $data;
	}

}
?>