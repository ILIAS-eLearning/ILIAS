<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesLDAP
*/

class ilLDAPResult
{
    private $ldap_handle = null;
    private $result = null;
    private $entries = null;
    
    private $num_results = null;
    private $data = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param resource ldap connection
     * @param resource ldap result
     *
     */
    public function __construct($a_ldap_connection, $a_result)
    {
        $this->ldap_handle = $a_ldap_connection;
        $this->result = $a_result;
        
        $this->toSimpleArray();
    }
    
    /**
     * Get search result as array
     *
     * @access public
     * @param
     *
     */
    public function get()
    {
        return $this->data ? $this->data : array();
    }
    
    /**
     * Get all result rows
     *
     * @access public
     *
     */
    public function getRows()
    {
        return $this->all_rows ? $this->all_rows : array();
    }
    
    /**
     * get number of rows
     *
     * @access public
     * @param
     *
     */
    public function numRows()
    {
        return $this->num_results;
    }
    
    /**
     * Transform ldap result in simple array
     *
     * @access private
     * @param
     *
     */
    private function toSimpleArray()
    {
        $this->data = array();
        $this->num_results = 0;
        
        if (!$this->entries = $this->getEntries()) {
            return false;
        }
        
        $this->num_results = $this->entries['count'];
        if ($this->entries['count'] == 0) {
            return true;
        }
        
        for ($row_counter = 0; $row_counter < $this->entries['count'];$row_counter++) {
            $data = array();
            foreach ($this->entries[$row_counter] as $key => $value) {
                $key = strtolower($key);
                
                if (is_int($key)) {
                    continue;
                }
                if ($key == 'dn') {
                    $data['dn'] = $value;
                    continue;
                }
                if (is_array($value)) {
                    if ($value['count'] > 1) {
                        for ($i = 0; $i < $value['count']; $i++) {
                            $data[$key][] = $value[$i];
                        }
                    } elseif ($value['count'] == 1) {
                        $data[$key] = $value[0];
                    }
                } else {
                    $data[$key] = $value;
                }
            }
            $this->all_rows[] = $data;
            if ($row_counter == 0) {
                $this->data = $data;
            }
        }
        return true;
    }
    
    public function __destruct()
    {
        @ldap_free_result($this->result);
    }
    
    /**
     * Wrapper for ldap_get_entries
     *
     * @access private
     *
     */
    private function getEntries()
    {
        return $this->entries = @ldap_get_entries($this->ldap_handle, $this->result);

        // this way ldap_get_entries is binary safe

        $i = 0;
        $tmp_entries = array();
        $entry = ldap_first_entry($this->ldap_handle, $this->result);
        do {
            $attributes = @ldap_get_attributes($this->ldap_handle, $entry);
            for ($j = 0; $j < $attributes['count']; $j++) {
                $values = ldap_get_values_len($this->ldap_handle, $entry, $attributes[$j]);
                $tmp_entries[$i][strtolower($attributes[$j])] = $values;
            }
            $i++;
        } while ($entry = @ldap_next_entry($this->ldap_handle, $entry));

        if ($i) {
            $tmp_entries['count'] = $i;
        }
        $this->entries = $tmp_entries;
        return $this->entries;
    }
}
