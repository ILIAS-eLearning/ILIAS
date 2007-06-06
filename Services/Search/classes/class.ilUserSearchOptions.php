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

/**
* Class ilLPObjSettings
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
*
*/
define('FIELD_TYPE_UDF_SELECT',1);
define('FIELD_TYPE_UDF_TEXT',2);
define('FIELD_TYPE_SELECT',3);
define('FIELD_TYPE_TEXT',4);

class ilUserSearchOptions
{
	var $db = null;

	function ilUserSearchOptions()
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->__read();
	}

	function _getSearchableFieldsInfo($a_admin = false)
	{
		global $lng;


		// Search for login is always enabled
		#$fields[0]['values'] = array();
		#$fields[0]['type'] = FIELD_TYPE_TEXT;
		#$fields[0]['lang'] = $this->lng->txt('login');
		#$fields[0]['db'] = 'login';
		

		$counter = 1;
		foreach(ilUserSearchOptions::_getPossibleFields($a_admin) as $field)
		{
			// TODO: check enabled
			// DONE
			if($a_admin == false and !ilUserSearchOptions::_isEnabled($field))
			{
				continue;
			}
			$fields[$counter]['values'] = array();
			$fields[$counter]['type'] = FIELD_TYPE_TEXT;
			$fields[$counter]['lang'] = $this->lng->txt($field);
			$fields[$counter]['db'] = $field;


			if($field == 'gender')
			{
				$fields[$counter]['values'] = array(0 => $lng->txt('please_choose'),
												  'f' => $lng->txt('gender_f'),
												  'm' => $lng->txt('gender_m'));
				$fields[$counter]['type'] = FIELD_TYPE_SELECT;
			}
			if($field == 'title')
			{
				$fields[$counter]['lang'] = $lng->txt('person_title');
			}
			/*if($field == 'active')
			{
				$fields[$counter]['values'] = array(-1 => $lng->txt('please_choose'),
												  '1' => $lng->txt('active'),
												  '0' => $lng->txt('inactive'));
				$fields[$counter]['type'] = FIELD_TYPE_SELECT;
			}*/
			++$counter;
		}
		// TODO: add udf fields
		// DONE
		 $fields = ilUserSearchOptions::__appendUserDefinedFields($fields,$counter);

		return $fields ? $fields : array();
	}

	function _getPossibleFields($a_admin = false)
	{
		if ($a_admin === true)
		{
			return array(
			// 'active',
			 'gender',
			 'login',
			 'lastname',
			 'firstname',
			 'title',
			 'institution',
			 'street',
			 'zipcode',
			 'city',
			 'country',
			 'email',
			 'matriculation');
		}
		
		return array('gender',
					 'login',
					 'lastname',
					 'firstname',
					 'title',
					 'institution',
					 'department',
					 'street',
					 'zipcode',
					 'city',
					 'country',
					 'email',
					 'hobby',
					 'matriculation');
	}

	function _isSearchable($a_key)
	{
		return in_array($a_key,ilUserSearchOptions::_getPossibleFields());
	}

	function _isEnabled($a_key)
	{
		global $ilias;

		// login is always enabled
		if($a_key == 'login')
		{
			return true;
		}

		return (bool) $ilias->getSetting('search_enabled_'.$a_key);
	}

	function _saveStatus($a_key,$a_enabled)
	{
		global $ilias;

		$ilias->setSetting('search_enabled_'.$a_key,(int) $a_enabled);
		return true;
	}

	function __appendUserDefinedFields($fields,$counter)
	{
		include_once './classes/class.ilUserDefinedFields.php';

		$user_defined_fields =& ilUserDefinedFields::_getInstance();
		
		foreach($user_defined_fields->getSearchableDefinitions() as $definition)
		{
			$fields[$counter]['values'] = ilUserSearchOptions::__prepareValues($definition['field_values']);
			$fields[$counter]['lang'] = $definition['field_name'];
			$fields[$counter]['db'] = $definition['field_id'];

			switch($definition['field_type'])
			{
				case UDF_TYPE_TEXT:
					$fields[$counter]['type'] = FIELD_TYPE_UDF_TEXT;
					break;

				case UDF_TYPE_SELECT:
					$fields[$counter]['type'] = FIELD_TYPE_UDF_SELECT;
					break;
			}
			++$counter;
		}
		return $fields ? $fields : array();
	}

	function __prepareValues($a_values)
	{
		$new_values = array(0 => $this->lng->txt('please_choose'));
		foreach($a_values as $value)
		{
			$new_values[$value] = $value;
		}
		return $new_values ? $new_values : array();
	}
}
?>