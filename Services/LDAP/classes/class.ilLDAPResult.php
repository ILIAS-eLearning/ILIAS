<?php

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

declare(strict_types=1);

/**
 * Class ilLDAPPagedResult
 *
 * @author Fabian Wolf
 */
class ilLDAPResult
{
    /**
     * @var resource|\Ldap\Connection
     */
    private $handle;

    /**
     * @var null|resource|list<\Ldap\Result>|\Ldap\Result
     */
    private $result = null;

    private array $rows = [];
    private ?array $last_row;

    /**
     * @param resource|\Ldap\Connection                           $a_ldap_handle from ldap_connect()
     * @param null|false|resource|list<\Ldap\Result>|\Ldap\Result $a_result      from ldap_search(), ldap_list() or ldap_read()
     */
    public function __construct($a_ldap_handle, $a_result = null)
    {
        $this->handle = $a_ldap_handle;

        if ($a_result !== null && $a_result !== false) {
            $this->result = $a_result;
        }
    }

    /**
     * Total count of resulted rows
     */
    public function numRows(): int
    {
        return count($this->rows);
    }

    /**
     * Resource from ldap_search()
     * @param false|resource|list<\Ldap\Result>|\Ldap\Result $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }

    /**
     * Returns last result
     * @return array
     */
    public function get(): array
    {
        return is_array($this->last_row) ? $this->last_row : [];
    }

    /**
     * Returns complete results
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Starts ldap_get_entries() and transforms results
     * @return self $this
     */
    public function run(): self
    {
        if ($this->result) {
            $entries = ldap_get_entries($this->handle, $this->result);
            $this->addEntriesToRows($entries);
        }

        return $this;
    }

    /**
     * Adds Results from ldap_get_entries() to rows
     */
    private function addEntriesToRows(array $entries): void
    {
        $num = $entries['count'];
        if ($num === 0) {
            return;
        }

        for ($row_counter = 0; $row_counter < $num; $row_counter++) {
            $data = $this->toSimpleArray($entries[$row_counter]);
            $this->rows[] = $data;
            $this->last_row = $data;
        }
    }

    /**
     * Transforms results from ldap_get_entries() to a simple format
     */
    private function toSimpleArray(array $entry): array
    {
        $data = [];
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

    public function __destruct()
    {
        if ($this->result) {
            ldap_free_result($this->result);
        }
    }
}
