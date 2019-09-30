<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-20019 ILIAS open source, University of Cologne            |
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
 * Class ilLDAPPagedResult
 *
 * @author Fabian Wolf
 */
class ilLDAPResult
{
	/**
	 * @var resource
	 */
	private $handle;

	/**
	 * @var resource
	 */
	private $result;

	/**
	 * @var array
	 */
	private $rows;

	/**
	 * @var array
	 */
	private $last_row;

	/**
	 * ilLDAPPagedResult constructor.
	 * @param resource $a_ldap_handle from ldap_connect()
	 * @param resource $a_result from ldap_search()
	 */
	public function __construct($a_ldap_handle, $a_result = null)
	{
		$this->handle = $a_ldap_handle;

		if($a_result != null)
		{
			$this->result = $a_result;
		}
	}

	/**
	 * Total count of resulted rows
	 * @return int
	 */
	public function numRows()
	{
		return is_array($this->rows) ? count($this->rows) : 0;
	}

	/**
	 * Resource from ldap_search()
	 * @return resource
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Resource from ldap_search()
	 * @param resource $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * Returns last result
	 * @return array
	 */
	public function get()
	{
		return is_array($this->last_row) ? $this->last_row : array();
	}

	/**
	 * Returns complete results
	 * @return array
	 */
	public function getRows()
	{
		return is_array($this->rows) ? $this->rows : array();
	}

	/**
	 * Starts ldap_get_entries() and transforms results
	 * @return self $this
	 */
	public function run()
	{
		$entries = @ldap_get_entries($this->handle, $this->result);
		$this->addEntriesToRows($entries);

		return $this;
	}

	/**
	 * Adds Results from ldap_get_entries() to rows
	 * @param array $entries
	 */
	private function addEntriesToRows($entries)
	{
		if(!$entries)
		{
			return;
		}

		$num = $entries['count'];

		if($num == 0)
		{
			return;
		}

		for($row_counter = 0; $row_counter < $num;$row_counter++)
		{
			$data = $this->toSimpleArray($entries[$row_counter]);
			$this->rows[] = $data;
			$this->last_row = $data;
		}
	}

	/**
	 * Transforms results from ldap_get_entries() to a simple format
	 * @param array $entry
	 * @return array
	 */
	private function toSimpleArray($entry)
	{
		$data = array();
		foreach($entry as $key => $value)
		{
			$key = strtolower($key);

			if(is_int($key))
			{
				continue;
			}
			if($key == 'dn')
			{
				$data['dn'] = $value;
				continue;
			}
			if(is_array($value))
			{
				if($value['count'] > 1)
				{
					for($i = 0; $i < $value['count']; $i++)
					{
						$data[$key][] = $value[$i];
					}
				}
				elseif($value['count'] == 1)
				{
					$data[$key] = $value[0];
				}
			}
			else
			{
				$data[$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		@ldap_free_result($this->result);
	}
}