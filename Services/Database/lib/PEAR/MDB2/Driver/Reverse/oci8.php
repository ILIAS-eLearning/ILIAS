<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2006 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Frank M. Kromann                       |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id: oci8.php,v 1.62 2007/03/29 18:18:06 quipo Exp $
//

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 Oracle driver for the schema reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@dybnet.de>
 */
class MDB2_Driver_Reverse_oci8 extends MDB2_Driver_Reverse_Common
{
    // {{{ getTableFieldDefinition()

    /**
     * Get the structure of a field into an array
     *
     * @param string    $table       name of table that should be used in method
     * @param string    $field_name  name of field that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableFieldDefinition($table, $field_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }

        $query = 'SELECT column_name name, data_type "type", nullable, data_default "default"';
        $query.= ', COALESCE(data_precision, data_length) "length", data_scale "scale"';
        $query.= ' FROM user_tab_columns';
        $query.= ' WHERE (table_name='.$db->quote($table, 'text').' OR table_name='.$db->quote(strtoupper($table), 'text').')';
        $query.= ' AND (column_name='.$db->quote($field_name, 'text').' OR column_name='.$db->quote(strtoupper($field_name), 'text').')';
        $query.= ' ORDER BY column_id';
        $column = $db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($column)) {
            return $column;
        }

        if (empty($column)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table column', __FUNCTION__);
        }

        $column = array_change_key_case($column, CASE_LOWER);
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $column['name'] = strtolower($column['name']);
            } else {
                $column['name'] = strtoupper($column['name']);
            }
        }
        $mapped_datatype = $db->datatype->mapNativeDatatype($column);
        if (PEAR::IsError($mapped_datatype)) {
            return $mapped_datatype;
        }
        list($types, $length, $unsigned, $fixed) = $mapped_datatype;
        $notnull = false;
        if (!empty($column['nullable']) && $column['nullable'] == 'N') {
            $notnull = true;
        }
        $default = false;
        if (array_key_exists('default', $column)) {
            $default = $column['default'];
            if ($default === 'NULL') {
                $default = null;
            }
            if (is_null($default) && $notnull) {
                $default = '';
            }
        }

        $definition[0] = array('notnull' => $notnull, 'nativetype' => $column['type']);
        if (!is_null($length)) {
            $definition[0]['length'] = $length;
        }
        if (!is_null($unsigned)) {
            $definition[0]['unsigned'] = $unsigned;
        }
        if (!is_null($fixed)) {
            $definition[0]['fixed'] = $fixed;
        }
        if ($default !== false) {
            $definition[0]['default'] = $default;
        }
        foreach ($types as $key => $type) {
            $definition[$key] = $definition[0];
            if ($type == 'clob' || $type == 'blob') {
                unset($definition[$key]['default']);
            }
            $definition[$key]['type'] = $type;
            $definition[$key]['mdb2type'] = $type;
        }
        if ($type == 'integer') {
            $query = "SELECT DISTINCT name
                        FROM all_source
                       WHERE type='TRIGGER'
                         AND UPPER(text) like '%ON ". strtoupper($db->escape($table, 'text')) ."%'";
            $result = $db->query($query);
            if (!PEAR::isError($result)) {
                while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $row = array_change_key_case($row, CASE_LOWER);
                    $trquery = 'SELECT text
                                  FROM all_source
                                 WHERE name=' . $db->quote($row['name'],'text')
                          . ' ORDER BY line';
                    $triggersth = $db->query($trquery);
                    $triggerstr = '';
                    while ($triggerline = $triggersth->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                        $triggerline = array_change_key_case($triggerline,CASE_LOWER);
                        $triggerstr .= $triggerline['text']. ' ';
                    }
                    $matches = array();
                    if (preg_match('/.*\W(.+)\.nextval into :NEW\.'.$field_name.' FROM dual/i', $triggerstr, $matches)) {
                        // we reckon it's an autoincrementing trigger on field_name
                        // there will be other pcre patterns needed here for other ways of mimicking auto_increment in ora.
                        $definition[0]['autoincrement'] = $matches[1];
                    }
                }
            }
        }
        return $definition;
    }

    // }}}

    // {{{ getTableIndexDefinition()

    /**
     * Get the structure of an index into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $index_name name of index that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableIndexDefinition($table, $index_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        
        $query = "SELECT column_name,
                         column_position,
                         descend
                    FROM user_ind_columns
                   WHERE (table_name=".$db->quote($table, 'text').' OR table_name='.$db->quote(strtoupper($table), 'text').')
                     AND (index_name=%s OR index_name=%s)
                     AND index_name NOT IN (
                           SELECT constraint_name
                             FROM dba_constraints
                            WHERE (table_name = '.$db->quote($table, 'text').' OR table_name='.$db->quote(strtoupper($table), 'text').")
                              AND constraint_type in ('P','U')
                         )
                ORDER BY column_position";
        $index_name_mdb2 = $db->getIndexName($index_name);
        $sql = sprintf($query,
            $db->quote($index_name_mdb2, 'text'),
            $db->quote(strtoupper($index_name_mdb2), 'text')
        );
        $result = $db->queryRow($sql);
        if (!PEAR::isError($result) && !is_null($result)) {
            // apply 'idxname_format' only if the query succeeded, otherwise
            // fallback to the given $index_name, without transformation
            $index_name = $index_name_mdb2;
        }
        $sql = sprintf($query,
            $db->quote($index_name, 'text'),
            $db->quote(strtoupper($index_name), 'text')
        );
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            return $result;
        }

        $definition = array();
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $row = array_change_key_case($row, CASE_LOWER);
            $column_name = $row['column_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $column_name = strtolower($column_name);
                } else {
                    $column_name = strtoupper($column_name);
                }
            }
            $definition['fields'][$column_name] = array(
                'position' => (int)$row['column_position'],
            );
            if (!empty($row['descend'])) {
                $definition['fields'][$column_name]['sorting'] =
                    ($row['descend'] == 'ASC' ? 'ascending' : 'descending');
            }
        }
        $result->free();
        if (empty($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table index', __FUNCTION__);
        }
        return $definition;
    }

    // }}}
    // {{{ getTableConstraintDefinition()

    /**
     * Get the structure of a constraint into an array
     *
     * @param string    $table           name of table that should be used in method
     * @param string    $constraint_name name of constraint that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableConstraintDefinition($table, $constraint_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        
        $query = 'SELECT alc.constraint_name, 
                         alc.constraint_type,
                         alc.search_condition,
                         alc.r_constraint_name,
                         alc.search_condition,
                         cols.column_name,
                         cols.position
                    FROM all_constraints alc,
                         all_cons_columns cols
                   WHERE (alc.constraint_name=%s OR alc.constraint_name=%s)
                     AND alc.constraint_name = cols.constraint_name
                     AND alc.owner = '.$db->quote(strtoupper($db->dsn['username']), 'text');
        if (!empty($table)) {
             $query.= ' AND (alc.table_name='.$db->quote($table, 'text').' OR alc.table_name='.$db->quote(strtoupper($table), 'text').')';
        }
        if (strtolower($constraint_name) != 'primary') {
            $constraint_name_mdb2 = $db->getIndexName($constraint_name);
            $sql = sprintf($query,
                $db->quote($constraint_name_mdb2, 'text'),
                $db->quote(strtoupper($constraint_name_mdb2), 'text')
            );
            $result = $db->queryRow($sql);
            if (!PEAR::isError($result) && !is_null($result)) {
                // apply 'idxname_format' only if the query succeeded, otherwise
                // fallback to the given $index_name, without transformation
                $constraint_name = $constraint_name_mdb2;
            }
        }
        $sql = sprintf($query,
            $db->quote($constraint_name, 'text'),
            $db->quote(strtoupper($constraint_name), 'text')
        );
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            return $result;
        }
        $definition = array();
        while (is_array($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $column_name = $row['column_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $column_name = strtolower($column_name);
                } else {
                    $column_name = strtoupper($column_name);
                }
            }
            $definition['fields'][$column_name] = array(
                'position' => (int)$row['position']
            );
            $lastrow = $row;
            // otherwise $row is no longer usable on exit from loop
        }
        $result->free();
        if (empty($definition)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                $constraint_name . ' is not an existing table constraint', __FUNCTION__);
        }
        if ($lastrow['constraint_type'] === 'P') {
            $definition['primary'] = true;
        } elseif ($lastrow['constraint_type'] === 'U') {
            $definition['unique'] = true;
        } elseif ($lastrow['constraint_type'] === 'R') {
            $definition['foreign'] = $lastrow['r_constraint_name'];
        } elseif ($lastrow['constraint_type'] === 'C') {
            $definition['check'] = true;
            // pattern match constraint for check constraint values into enum-style output:
			$enumregex = '/'.$lastrow['column_name'].' in \((.+?)\)/i';
			if (preg_match($enumregex, $lastrow['search_condition'], $rangestr)) {
				$definition['fields'][$column_name] = array();
				$allowed = explode(',', $rangestr[1]);
				foreach ($allowed as $val) {
					$val = trim($val);
					$val = preg_replace('/^\'/', '', $val);
					$val = preg_replace('/\'$/', '', $val);
					array_push($definition['fields'][$column_name], $val);
				}
			}
		}
        return $definition;
    }

    // }}}
    // {{{ getSequenceDefinition()

    /**
     * Get the structure of a sequence into an array
     *
     * @param string    $sequence   name of sequence that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getSequenceDefinition($sequence)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($sequence);
        $query = 'SELECT last_number FROM user_sequences';
        $query.= ' WHERE sequence_name='.$db->quote($sequence_name, 'text');
        $query.= ' OR sequence_name='.$db->quote(strtoupper($sequence_name), 'text');
        $start = $db->queryOne($query, 'integer');
        if (PEAR::isError($start)) {
            return $start;
        }
        $definition = array();
        if ($start != 1) {
            $definition = array('start' => $start);
        }
        return $definition;
    }

    // }}}
    // {{{ getTriggerDefinition()

    /**
     * Get the structure of a trigger into an array
     *
     * EXPERIMENTAL
     *
     * WARNING: this function is experimental and may change the returned value
     * at any time until labelled as non-experimental
     *
     * @param string    $trigger    name of trigger that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTriggerDefinition($trigger)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT trigger_name,
                         table_name,
                         trigger_body,
                         trigger_type,
                         triggering_event trigger_event,
                         description trigger_comment,
                         1 trigger_enabled,
                         when_clause
                    FROM user_triggers
                   WHERE trigger_name = \''. strtoupper($trigger).'\'';
        $types = array(
            'trigger_name'    => 'text',
            'table_name'      => 'text',
            'trigger_body'    => 'text',
            'trigger_type'    => 'text',
            'trigger_event'   => 'text',
            'trigger_comment' => 'text',
            'trigger_enabled' => 'boolean',
            'when_clause'     => 'text',
        );
        $result = $db->queryRow($query, $types, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!empty($result['trigger_type'])) {
            //$result['trigger_type'] = array_shift(explode(' ', $result['trigger_type']));
            $result['trigger_type'] = preg_replace('/(\S+).*/', '\\1', $result['trigger_type']);
        }
        return $result;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * NOTE: only supports 'table' and 'flags' if <var>$result</var>
     * is a table name.
     *
     * NOTE: flags won't contain index information.
     *
     * @param object|string  $result  MDB2_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A MDB2_Error object on failure.
     *
     * @see MDB2_Driver_Common::tableInfo()
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
           return parent::tableInfo($result, $mode);
        }

        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $resource = MDB2::isResultCommon($result) ? $result->getResource() : $result;
        if (!is_resource($resource)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'Could not generate result resource', __FUNCTION__);
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $case_func = 'strtolower';
            } else {
                $case_func = 'strtoupper';
            }
        } else {
            $case_func = 'strval';
        }

        $count = @OCINumCols($resource);
        $res = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        $db->loadModule('Datatype', null, true);
        for ($i = 0; $i < $count; $i++) {
            $column = array(
                'table'  => '',
                'name'   => $case_func(@OCIColumnName($resource, $i+1)),
                'type'   => @OCIColumnType($resource, $i+1),
                'length' => @OCIColumnSize($resource, $i+1),
                'flags'  => '',
            );
            $res[$i] = $column;
            $res[$i]['mdb2type'] = $db->datatype->mapNativeDatatype($res[$i]);
            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }
        return $res;
    }
}
?>