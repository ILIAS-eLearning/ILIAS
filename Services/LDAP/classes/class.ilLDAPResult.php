<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    private ?array $rows;
    private ?array $last_row;

    /**
     * ilLDAPPagedResult constructor.
     * @param resource $a_ldap_handle from ldap_connect()
     * @param resource $a_result from ldap_search()
     */
    public function __construct($a_ldap_handle, $a_result = null)
    {
        $this->handle = $a_ldap_handle;

        if ($a_result !== null) {
            $this->result = $a_result;
        }
    }

    /**
     * Total count of resulted rows
     * @return int
     */
    public function numRows() : int
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
    public function setResult($result) : void
    {
        $this->result = $result;
    }

    /**
     * Returns last result
     * @return array
     */
    public function get() : array
    {
        return is_array($this->last_row) ? $this->last_row : [];
    }

    /**
     * Returns complete results
     */
    public function getRows() : array
    {
        return is_array($this->rows) ? $this->rows : [];
    }

    /**
     * Starts ldap_get_entries() and transforms results
     * @return self $this
     */
    public function run() : self
    {
        $entries = ldap_get_entries($this->handle, $this->result);
        $this->addEntriesToRows($entries);

        return $this;
    }

    /**
     * Adds Results from ldap_get_entries() to rows
     */
    private function addEntriesToRows(array $entries) : void
    {
        $num = $entries['count'];
        $this->rows = [];
        if ($num === 0) {
            return;
        }


        for ($row_counter = 0; $row_counter < $num;$row_counter++) {
            $data = $this->toSimpleArray($entries[$row_counter]);
            $this->rows[] = $data;
            $this->last_row = $data;
        }
    }

    /**
     * Transforms results from ldap_get_entries() to a simple format
     */

    private function toSimpleArray(array $entry) : array
    {
        $data = array();
        foreach ($entry as $key => $value) {
            if (is_int($key)) {
                continue;
            }

            $key = strtolower($key);
            if ($key === 'dn') {
                $data['dn'] = $value;
                continue;
            }
            if (is_array($value)) {
                if ($value['count'] > 1) {
                    for ($i = 0; $i < $value['count']; $i++) {
                        $data[$key][] = $value[$i];
                    }
                } elseif ($value['count'] === 1) {
                    $data[$key] = $value[0];
                }
            } else {
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
        if ($this->result) {
            ldap_free_result($this->result);
        }
    }
}
