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
	var $db;

	var $settings;

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
		$query .= "pdf_path = '' ";
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
			$query .= "pdf_path = " . $ilDB->quote($a_values["pdf_path"]) . " ";
			$query .= "WHERE settings_id = '" . $this->getSettingsId() . "'";
			
			$this->db->query($query);
		}
		else
		{
			$query = "INSERT INTO payment_settings (currency_unit, currency_subunit, address, bank_data, add_info, vat_rate, pdf_path) VALUES (";
			$query .= $ilDB->quote($a_values["currency_unit"]) . ", ";
			$query .= $ilDB->quote($a_values["currency_subunit"]) . ", ";
			$query .= $ilDB->quote($a_values["address"]) . ", ";
			$query .= $ilDB->quote($a_values["bank_data"]) . ", ";
			$query .= $ilDB->quote($a_values["add_info"]) . ", ";
			$query .= $ilDB->quote($a_values["vat_rate"]) . ", ";
			$query .= $ilDB->quote($a_values["pdf_path"]) . ")";
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
		}

		$this->settings = $data;
	}

}
?>