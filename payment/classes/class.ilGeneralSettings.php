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
		$query = "DELETE FROM payment_settings";
		$this->db->query($query);

		return true;
	}
		
	function setAll($a_values)
	{
		$query = "INSERT INTO payment_settings VALUES(";
		$query .= "'', ";
		$query .= "'" . $a_values["currency_unit"] . "', ";
		$query .= "'" . $a_values["currency_subunit"] . "', ";
		$query .= "'" . $a_values["address"] . "', ";
		$query .= "'" . $a_values["bank_data"] . "', ";
		$query .= "'" . $a_values["add_info"] . "', ";
		$query .= "'" . $a_values["vat_rate"] . "', ";
		$query .= "'" . $a_values["pdf_path"] . "')";
		$this->db->query($query);

		$this->__getSettings();

		return true;
	}

	function __getSettings()
	{
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