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

class ilPaypalSettings
{
	var $db;

	var $settings;
	var $settings_id;

	function ilPaypalSettings()
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
		$query = "UPDATE payment_settings "
				."SET paypal = '' "
				."WHERE settings_id = '" . $this->settings_id . "'";
		$this->db->query($query);

		$this->settings = array();
	}
		
	function setAll($a_values)
	{
		$query = "UPDATE payment_settings "
				."SET paypal = '" . serialize($a_values) . "' "
				."WHERE settings_id = '" . $this->settings_id . "'";
		$this->db->query($query);

		$this->settings = $a_values;
	}

	function __getSettings()
	{
		$this->__getSettingsId();

		$query = "SELECT paypal FROM payment_settings WHERE settings_id = '" . $this->settings_id . "'";
		$result = $this->db->getrow($query);

		$data = array();
		if (is_object($result))
		{
			if ($result->paypal != "") $data = unserialize($result->paypal);
			else $data = array();
		}

		$this->settings = $data;
	}

	function __getSettingsId()
	{
		$query = "SELECT * FROM payment_settings";
		$result = $this->db->getrow($query);

		$this->settings_id = 0;
		if (is_object($result)) $this->settings_id = $result->settings_id;
	}

}
?>