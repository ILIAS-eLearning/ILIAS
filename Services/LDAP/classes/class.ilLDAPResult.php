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
	 * @var int
	 */
	private $page_size = 100;

	/**
	 * @var bool
	 */
	private $with_pagination = false;

	/**
	 * @var string
	 */
	public $cookie = '';

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
	 * Gets page size for ldap_control_paged_result(). Only works with pagination
	 * @return int
	 */
	public function getPageSize()
	{
		return $this->page_size;
	}

	/**
	 * Sets page size for ldap_control_paged_result(). Only works with pagination
	 * @param int $page_size
	 */
	public function setPageSize($page_size)
	{
		$this->page_size = $page_size;
	}

	/**
	 * Is pagination enabled
	 * @return bool
	 */
	public function isWithPagination()
	{
		return $this->with_pagination;
	}

	/**
	 * @param bool $with_pagination
	 */
	public function setWithPagination($with_pagination)
	{
		$this->with_pagination = $with_pagination;
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
		if($this->with_pagination)
		{
			do{
				$entries = $this->ldap_get_entries();
				$this->addEntriesToRows($entries);
			}while($this->cookie !== null && $this->cookie != '');
			//reset paged result setting
			ldap_control_paged_result($this->handle, 10000);
		}else{
			$entries = $this->ldap_get_entries();
			$this->addEntriesToRows($entries);
		}
		return $this;
	}


	/**
	 * Retruns ldap_get_entries() result
	 * @return array
	 */
	private function ldap_get_entries(){
		$cookie = $this->cookie;
		if($this->with_pagination)
			ldap_control_paged_result($this->handle, $this->page_size, true, $cookie);
		$entries = @ldap_get_entries($this->handle, $this->result);
		if($this->with_pagination)
			ldap_control_paged_result_response($this->handle, $this->result,  $cookie);
		$this->cookie = $cookie;
		return $entries;
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