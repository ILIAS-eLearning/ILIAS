<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2007 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
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

// $Id: oci8.php,v 1.192 2007/03/04 22:27:11 quipo Exp $

/**
 * MDB2 OCI8 driver
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_oci8 extends MDB2_Driver_Common
{
    // {{{ properties
    var $string_quoting = array('start' => "'", 'end' => "'", 'escape' => "'", 'escape_pattern' => '@');

    var $identifier_quoting = array('start' => '"', 'end' => '"', 'escape' => '"');

    var $uncommitedqueries = 0;
    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->phptype = 'oci8';
        $this->dbsyntax = 'oci8';

        $this->supported['sequences'] = true;
        $this->supported['indexes'] = true;
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['current_id'] = true;
        $this->supported['affected_rows'] = true;
        $this->supported['transactions'] = true;
        $this->supported['savepoints'] = true;
        $this->supported['limit_queries'] = true;
        $this->supported['LOBs'] = true;
        $this->supported['replace'] = 'emulated';
        $this->supported['sub_selects'] = true;
        $this->supported['auto_increment'] = false; // implementation is broken
        $this->supported['primary_key'] = true;
        $this->supported['result_introspection'] = true;
        $this->supported['prepared_statements'] = true;
        $this->supported['identifier_quoting'] = true;
        $this->supported['pattern_escaping'] = true;
        $this->supported['new_link'] = true;

        $this->options['DBA_username'] = false;
        $this->options['DBA_password'] = false;
        $this->options['database_name_prefix'] = false;
        $this->options['emulate_database'] = true;
        $this->options['default_tablespace'] = false;
        $this->options['default_text_field_length'] = 2000;
        $this->options['result_prefetching'] = false;
    }

    // }}}
    // {{{ errorInfo()

    /**
     * This method is used to collect information about an error
     *
     * @param integer $error
     * @return array
     * @access public
     */
    function errorInfo($error = null)
    {
        if (is_resource($error)) {
            $error_data = @OCIError($error);
            $error = null;
        } elseif ($this->connection) {
            $error_data = @OCIError($this->connection);
        } else {
            $error_data = @OCIError();
        }
        $native_code = $error_data['code'];
        $native_msg  = $error_data['message'];
        if (is_null($error)) {
            static $ecode_map;
            if (empty($ecode_map)) {
                $ecode_map = array(
                    1    => MDB2_ERROR_CONSTRAINT,
                    900  => MDB2_ERROR_SYNTAX,
                    904  => MDB2_ERROR_NOSUCHFIELD,
                    911  => MDB2_ERROR_SYNTAX, //invalid character
                    913  => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                    921  => MDB2_ERROR_SYNTAX,
                    923  => MDB2_ERROR_SYNTAX,
                    942  => MDB2_ERROR_NOSUCHTABLE,
                    955  => MDB2_ERROR_ALREADY_EXISTS,
                    1400 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                    1401 => MDB2_ERROR_INVALID,
                    1407 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                    1418 => MDB2_ERROR_NOT_FOUND,
                    1476 => MDB2_ERROR_DIVZERO,
                    1722 => MDB2_ERROR_INVALID_NUMBER,
                    2289 => MDB2_ERROR_NOSUCHTABLE,
                    2291 => MDB2_ERROR_CONSTRAINT,
                    2292 => MDB2_ERROR_CONSTRAINT,
                    2449 => MDB2_ERROR_CONSTRAINT,
                    24344 => MDB2_ERROR_SYNTAX, //success with compilation error
                );
            }
            if (isset($ecode_map[$native_code])) {
                $error = $ecode_map[$native_code];
            }
        }
        return array($error, $native_code, $native_msg);
    }

    // }}}
    // {{{ beginTransaction()

    /**
     * Start a transaction or set a savepoint.
     *
     * @param   string  name of a savepoint to set
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function beginTransaction($savepoint = null)
    {
        $this->debug('Starting transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!is_null($savepoint)) {
            if (!$this->in_transaction) {
                return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                    'savepoint cannot be released when changes are auto committed', __FUNCTION__);
            }
            $query = 'SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        } elseif ($this->in_transaction) {
            return MDB2_OK;  //nothing to do
        }
        if (!$this->destructor_registered && $this->opened_persistent) {
            $this->destructor_registered = true;
            register_shutdown_function('MDB2_closeOpenTransactions');
        }
        $this->in_transaction = true;
        ++$this->uncommitedqueries;
        return MDB2_OK;
    }

    // }}}
    // {{{ commit()

    /**
     * Commit the database changes done during a transaction that is in
     * progress or release a savepoint. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after committing the pending changes.
     *
     * @param   string  name of a savepoint to release
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function commit($savepoint = null)
    {
        $this->debug('Committing transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'commit/release savepoint cannot be done changes are auto committed', __FUNCTION__);
        }
        if (!is_null($savepoint)) {
            return MDB2_OK;
        }

        if ($this->uncommitedqueries) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
            if (!@OCICommit($connection)) {
                return $this->raiseError(null, null, null,
                'Unable to commit transaction', __FUNCTION__);
            }
            $this->uncommitedqueries = 0;
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }

    // }}}
    // {{{ rollback()

    /**
     * Cancel any database changes done during a transaction or since a specific
     * savepoint that is in progress. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after canceling the pending changes.
     *
     * @param   string  name of a savepoint to rollback to
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function rollback($savepoint = null)
    {
        $this->debug('Rolling back transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'rollback cannot be done changes are auto committed', __FUNCTION__);
        }
        if (!is_null($savepoint)) {
            $query = 'ROLLBACK TO SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        }

        if ($this->uncommitedqueries) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
            if (!@OCIRollback($connection)) {
                return $this->raiseError(null, null, null,
                'Unable to rollback transaction', __FUNCTION__);
            }
            $this->uncommitedqueries = 0;
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }

    // }}}
    // {{{ function setTransactionIsolation()

    /**
     * Set the transacton isolation level.
     *
     * @param   string  standard isolation level
     *                  READ UNCOMMITTED (allows dirty reads)
     *                  READ COMMITTED (prevents dirty reads)
     *                  REPEATABLE READ (prevents nonrepeatable reads)
     *                  SERIALIZABLE (prevents phantom reads)
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     * @since   2.1.1
     */
    function setTransactionIsolation($isolation)
    {
        $this->debug('Setting transaction isolation level', __FUNCTION__, array('is_manip' => true));
        switch ($isolation) {
        case 'READ UNCOMMITTED':
            $isolation = 'READ COMMITTED';
        case 'READ COMMITTED':
        case 'REPEATABLE READ':
            $isolation = 'SERIALIZABLE';
        case 'SERIALIZABLE':
            break;
        default:
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'isolation level is not supported: '.$isolation, __FUNCTION__);
        }

        $query = "ALTER SESSION ISOLATION LEVEL $isolation";
        return $this->_doQuery($query, true);
    }

    // }}}
    // {{{ _doConnect()

    /**
     * do the grunt work of the connect
     *
     * @return connection on success or MDB2 Error Object on failure
     * @access protected
     */
    function _doConnect($username, $password, $persistent = false)
    {
        if (!PEAR::loadExtension($this->phptype)) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'extension '.$this->phptype.' is not compiled into PHP', __FUNCTION__);
        }

        $sid = '';

        if (!empty($this->dsn['service']) && $this->dsn['hostspec']) {
            //oci8://username:password@foo.example.com[:port]/?service=service
            // service name is given, it is assumed that hostspec is really a
            // hostname, we try to construct an oracle connection string from this
            $port = $this->dsn['port'] ? $this->dsn['port'] : 1521;
            $sid = sprintf("(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)
                            (HOST=%s) (PORT=%s)))
                            (CONNECT_DATA=(SERVICE_NAME=%s)))",
                $this->dsn['hostspec'],
                $port,
                $this->dsn['service']
            );
        } elseif ($this->dsn['hostspec']) {
            // we are given something like 'oci8://username:password@foo/'
            // we have hostspec but not a service name, now we assume that
            // hostspec is a tnsname defined in tnsnames.ora
            $sid = $this->dsn['hostspec'];
        } else {
            // oci://username:password@
            // if everything fails, we have to rely on environment variables
            // not before a check to 'emulate_database'
            if (!$this->options['emulate_database'] && $this->database_name) {
                $sid = $this->database_name;
            } elseif (getenv('ORACLE_SID')) {
                $sid = getenv('ORACLE_SID');
            } elseif ($sid = getenv('TWO_TASK')) {
                $sid = getenv('TWO_TASK');
            } else {
                return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'not a valid connection string or environment variable [ORACLE_SID|TWO_TASK] not set',
                    __FUNCTION__);
            }
        }

        if (function_exists('oci_connect')) {
            if (isset($this->dsn['new_link'])
                && ($this->dsn['new_link'] == 'true' || $this->dsn['new_link'] === true)
            ) {
                $connect_function = 'oci_new_connect';
            } else {
                $connect_function = $persistent ? 'oci_pconnect' : 'oci_connect';
            }

            $charset = empty($this->dsn['charset']) ? null : $this->dsn['charset'];
            $connection = @$connect_function($username, $password, $sid, $charset);
            $error = @OCIError();
            if (isset($error['code']) && $error['code'] == 12541) {
                // Couldn't find TNS listener.  Try direct connection.
                $connection = @$connect_function($username, $password, null, $charset);
            }
        } else {
            $connect_function = $persistent ? 'OCIPLogon' : 'OCILogon';
            $connection = @$connect_function($username, $password, $sid);

            if (!empty($this->dsn['charset'])) {
                $result = $this->setCharset($this->dsn['charset'], $connection);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }

        if (!$connection) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED, null, null,
                'unable to establish a connection', __FUNCTION__);
        }

       if (empty($this->dsn['disable_iso_date'])) {
            $query = "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'";
            $err =& $this->_doQuery($query, true, $connection);
            if (PEAR::isError($err)) {
                $this->disconnect(false);
                return $err;
            }
       }

        $query = "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='. '";
        $err =& $this->_doQuery($query, true, $connection);
        if (PEAR::isError($err)) {
            $this->disconnect(false);
            return $err;
        }

        return $connection;
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database
     *
     * @return MDB2_OK on success, MDB2 Error Object on failure
     * @access public
     */
    function connect()
    {
        if ($this->database_name && $this->options['emulate_database']) {
             $this->dsn['username'] = $this->options['database_name_prefix'].$this->database_name;
        }
        if (is_resource($this->connection)) {
            if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
                && $this->connected_database_name == $this->database_name
                && $this->opened_persistent == $this->options['persistent']
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }

        $connection = $this->_doConnect(
            $this->dsn['username'],
            $this->dsn['password'],
            $this->options['persistent']
        );
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $this->connection = $connection;
        $this->connected_dsn = $this->dsn;
        $this->connected_database_name = $this->database_name;
        $this->opened_persistent = $this->options['persistent'];
        $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;

        $this->as_keyword = ' ';
        $server_info = $this->getServerVersion();
        if (is_array($server_info)) {
            if ($server_info['major'] >= '10') {
                $this->as_keyword = ' AS ';
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @param  boolean $force if the disconnect should be forced even if the
     *                        connection is opened persistently
     * @return mixed true on success, false if not connected and error
     *                object on error
     * @access public
     */
    function disconnect($force = true)
    {
        if (is_resource($this->connection)) {
            if ($this->in_transaction) {
                $dsn = $this->dsn;
                $database_name = $this->database_name;
                $persistent = $this->options['persistent'];
                $this->dsn = $this->connected_dsn;
                $this->database_name = $this->connected_database_name;
                $this->options['persistent'] = $this->opened_persistent;
                $this->rollback();
                $this->dsn = $dsn;
                $this->database_name = $database_name;
                $this->options['persistent'] = $persistent;
            }

            if (!$this->opened_persistent || $force) {
                if (function_exists('oci_close')) {
                    @oci_close($this->connection);
                } else {
                    @OCILogOff($this->connection);
                }
            }
            $this->uncommitedqueries = 0;
        }
        return parent::disconnect($force);
    }

    // }}}
    // {{{ standaloneExec()

   /**
     * execute a query as database administrator
     *
     * @param string $query the SQL query
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &standaloneExec($query)
    {
        $connection = $this->_doConnect(
            $this->options['DBA_username'],
            $this->options['DBA_password'],
            $this->options['persistent']
        );
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, false, $limit, $offset);

        $result =& $this->_doQuery($query, false, $connection, false);
        if (PEAR::isError($result)) {
            @OCILogOff($connection);
            return $result;
        }

        $ret = $this->_affectedRows($connection, $result);
        @OCILogOff($connection);
        return $ret;
    }

    // }}}
    // {{{ standaloneQuery()

   /**
     * execute a query as DBA
     *
     * @param string $query the SQL query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param boolean $is_manip  if the query is a manipulation query
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &standaloneQuery($query, $types = null, $is_manip = false)
    {
        $connection = $this->_doConnect(
            $this->options['DBA_username'],
            $this->options['DBA_password'],
            $this->options['persistent']
        );
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, $is_manip, $limit, $offset);

        $result =& $this->_doQuery($query, $is_manip, $connection, false);
        @OCILogOff($connection);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($is_manip) {
            $affected_rows =  $this->_affectedRows($connection, $result);
            return $affected_rows;
        }
        $return =& $this->_wrapResult($result, $types, true, false, $limit, $offset);
        return $return;
    }

    // }}}
    // {{{ _modifyQuery()

    /**
     * Changes a query string for various DBMS specific reasons
     *
     * @param string $query  query to modify
     * @param boolean $is_manip  if it is a DML query
     * @param integer $limit  limit the number of rows
     * @param integer $offset  start reading from given offset
     * @return string modified query
     * @access protected
     */
    function _modifyQuery($query, $is_manip, $limit, $offset)
    {
        if (preg_match('/^\s*SELECT/i', $query)) {
            if (!preg_match('/\sFROM\s/i', $query)) {
                $query.= " FROM dual";
            }
            if ($limit > 0) {
                // taken from http://svn.ez.no/svn/ezcomponents/packages/Database
                $max = $offset + $limit;
                if ($offset > 0) {
                    $min = $offset + 1;
                    $query = "SELECT * FROM (SELECT a.*, ROWNUM mdb2rn FROM ($query) a WHERE ROWNUM <= $max) WHERE mdb2rn >= $min";
                } else {
                    $query = "SELECT a.* FROM ($query) a WHERE ROWNUM <= $max";
                }
            }
        }
        return $query;
    }

    // }}}
    // {{{ _doQuery()

    /**
     * Execute a query
     * @param string $query  query
     * @param boolean $is_manip  if the query is a manipulation query
     * @param resource $connection
     * @param string $database_name
     * @return result or error object
     * @access protected
     */
    function &_doQuery($query, $is_manip = false, $connection = null, $database_name = null)
    {
        $this->last_query = $query;
        $result = $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (PEAR::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        if ($this->getOption('disable_query')) {
            if ($is_manip) {
                return 0;
            }
            return null;
        }

        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }

        $result = @OCIParse($connection, $query);
        if (!$result) {
            $err = $this->raiseError(null, null, null,
                'Could not create statement', __FUNCTION__);
            return $err;
        }

        $mode = $this->in_transaction ? OCI_DEFAULT : OCI_COMMIT_ON_SUCCESS;
        if (!@OCIExecute($result, $mode)) {
            $err =& $this->raiseError($result, null, null,
                'Could not execute statement', __FUNCTION__);
            return $err;
        }

        if (is_numeric($this->options['result_prefetching'])) {
            @ocisetprefetch($result, $this->options['result_prefetching']);
        }

        $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }

    // }}}
    // {{{ _affectedRows()

    /**
     * Returns the number of rows affected
     *
     * @param resource $result
     * @param resource $connection
     * @return mixed MDB2 Error Object or the number of rows affected
     * @access private
     */
    function _affectedRows($connection, $result = null)
    {
        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }
        return @OCIRowCount($result);
    }

    // }}}
    // {{{ getServerVersion()

    /**
     * return version information about the server
     *
     * @param bool   $native  determines if the raw version string should be returned
     * @return mixed array/string with version information or MDB2 error object
     * @access public
     */
    function getServerVersion($native = false)
    {
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        if ($this->connected_server_info) {
            $server_info = $this->connected_server_info;
        } else {
            $server_info = @ociserverversion($connection);
        }
        if (!$server_info) {
            return $this->raiseError(null, null, null,
                'Could not get server information', __FUNCTION__);
        }
        // cache server_info
        $this->connected_server_info = $server_info;
        if (!$native) {
            if (!preg_match('/ (\d+)\.(\d+)\.(\d+)\.([\d\.]+) /', $server_info, $tmp)) {
                return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                    'Could not parse version information:'.$server_info, __FUNCTION__);
            }
            $server_info = array(
                'major' => $tmp[1],
                'minor' => $tmp[2],
                'patch' => $tmp[3],
                'extra' => $tmp[4],
                'native' => $server_info,
            );
        }
        return $server_info;
    }

    // }}}
    // {{{ prepare()

    /**
     * Prepares a query for multiple execution with execute().
     * With some database backends, this is emulated.
     * prepare() requires a generic query as string like
     * 'INSERT INTO numbers VALUES(?,?)' or
     * 'INSERT INTO numbers VALUES(:foo,:bar)'.
     * The ? and :[a-zA-Z] and  are placeholders which can be set using
     * bindParam() and the query can be send off using the execute() method.
     *
     * @param string $query the query to prepare
     * @param mixed   $types  array that contains the types of the placeholders
     * @param mixed   $result_types  array that contains the types of the columns in
     *                        the result set or MDB2_PREPARE_RESULT, if set to
     *                        MDB2_PREPARE_MANIP the query is handled as a manipulation query
     * @param mixed   $lobs   key (field) value (parameter) pair for all lob placeholders
     * @return mixed resource handle for the prepared query on success, a MDB2
     *        error on failure
     * @access public
     * @see bindParam, execute
     */
    function &prepare($query, $types = null, $result_types = null, $lobs = array())
    {
        if ($this->options['emulate_prepared']) {
            $obj =& parent::prepare($query, $types, $result_types, $lobs);
            return $obj;
        }
        $is_manip = ($result_types === MDB2_PREPARE_MANIP);
        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $result = $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (PEAR::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        $query = $this->_modifyQuery($query, $is_manip, $limit, $offset);
        $placeholder_type_guess = $placeholder_type = null;
        $question = '?';
        $colon = ':';
        $positions = array();
        $position = 0;
        $parameter = -1;
        while ($position < strlen($query)) {
            $q_position = strpos($query, $question, $position);
            $c_position = strpos($query, $colon, $position);
            if ($q_position && $c_position) {
                $p_position = min($q_position, $c_position);
            } elseif ($q_position) {
                $p_position = $q_position;
            } elseif ($c_position) {
                $p_position = $c_position;
            } else {
                break;
            }
            if (is_null($placeholder_type)) {
                $placeholder_type_guess = $query[$p_position];
            }
            
            $new_pos = $this->_skipDelimitedStrings($query, $position, $p_position);
            if (PEAR::isError($new_pos)) {
                return $new_pos;
            }
            if ($new_pos != $position) {
                $position = $new_pos;
                continue; //evaluate again starting from the new position
            }

            if ($query[$position] == $placeholder_type_guess) {
                if (is_null($placeholder_type)) {
                    $placeholder_type = $query[$p_position];
                    $question = $colon = $placeholder_type;
                    if (!empty($types) && is_array($types)) {
                        if ($placeholder_type == ':') {
                            if (is_int(key($types))) {
                                $types_tmp = $types;
                                $types = array();
                                $count = -1;
                            }
                        } else {
                            $types = array_values($types);
                        }
                    }
                }
                if ($placeholder_type == ':') {
                    $parameter = preg_replace('/^.{'.($position+1).'}([a-z0-9_]+).*$/si', '\\1', $query);
                    if ($parameter === '') {
                        $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                            'named parameter with an empty name', __FUNCTION__);
                        return $err;
                    }
                    // use parameter name in type array
                    if (isset($count) && isset($types_tmp[++$count])) {
                        $types[$parameter] = $types_tmp[$count];
                    }
                    $length = strlen($parameter) + 1;
                } else {
                    ++$parameter;
                    $length = strlen($parameter);
                }
                if (!in_array($parameter, $positions)) {
                    $positions[] = $parameter;
                }
                if (isset($types[$parameter])
                    && ($types[$parameter] == 'clob' || $types[$parameter] == 'blob')
                ) {
                    if (!isset($lobs[$parameter])) {
                        $lobs[$parameter] = $parameter;
                    }
                    $value = $this->quote(true, $types[$parameter]);
                    $query = substr_replace($query, $value, $p_position, $length);
                    $position = $p_position + strlen($value) - 1;
                } elseif ($placeholder_type == '?') {
                    $query = substr_replace($query, ':'.$parameter, $p_position, 1);
                    $position = $p_position + $length;
                } else {
                    $position = $p_position + 1;
                }
            } else {
                $position = $p_position;
            }
        }
        if (is_array($lobs)) {
            $columns = $variables = '';
            foreach ($lobs as $parameter => $field) {
                $columns.= ($columns ? ', ' : ' RETURNING ').$field;
                $variables.= ($variables ? ', ' : ' INTO ').':'.$parameter;
            }
            $query.= $columns.$variables;
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $statement = @OCIParse($connection, $query);
        if (!$statement) {
            $err =& $this->raiseError(null, null, null,
                'Could not create statement', __FUNCTION__);
            return $err;
        }

        $class_name = 'MDB2_Statement_'.$this->phptype;
        $obj = new $class_name($this, $statement, $positions, $query, $types, $result_types, $is_manip, $limit, $offset);
        $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'post', 'result' => $obj));
        return $obj;
    }

    // }}}
    // {{{ nextID()

    /**
     * Returns the next free id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @param boolean $ondemand when true the sequence is
     *                           automatic created, if it
     *                           not exists
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($seq_name), true);
        $query = "SELECT $sequence_name.nextval FROM DUAL";
        $this->expectError(MDB2_ERROR_NOSUCHTABLE);
        $result = $this->queryOne($query, 'integer');
        $this->popExpect();
        if (PEAR::isError($result)) {
            if ($ondemand && $result->getCode() == MDB2_ERROR_NOSUCHTABLE) {
                $this->loadModule('Manager', null, true);
                $result = $this->manager->createSequence($seq_name);
                if (PEAR::isError($result)) {
                    return $result;
                }
                return $this->nextId($seq_name, false);
            }
        }
        return $result;
    }

    // }}}
    // {{{ lastInsertID()

    /**
     * Returns the autoincrement ID if supported or $id or fetches the current
     * ID in a sequence called: $table.(empty($field) ? '' : '_'.$field)
     *
     * @param string $table name of the table into which a new row was inserted
     * @param string $field name of the field into which a new row was inserted
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function lastInsertID($table = null, $field = null)
    {
        $seq = $table.(empty($field) ? '' : '_'.$field);
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($seq), true);
        return $this->queryOne("SELECT $sequence_name.currval", 'integer');
    }

    // }}}
    // {{{ currId()

    /**
     * Returns the current id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @return mixed MDB2_Error or id
     * @access public
     */
    function currId($seq_name)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        $query = 'SELECT (last_number-1) FROM user_sequences';
        $query.= ' WHERE sequence_name='.$this->quote($sequence_name, 'text');
        $query.= ' OR sequence_name='.$this->quote(strtoupper($sequence_name), 'text');
        return $this->queryOne($query, 'integer');
    }
}

/**
 * MDB2 OCI8 result driver
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Result_oci8 extends MDB2_Result_Common
{
    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            @OCIFetchInto($this->result, $row, OCI_ASSOC+OCI_RETURN_NULLS);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
            @OCIFetchInto($this->result, $row, OCI_RETURN_NULLS);
        }
        if (!$row) {
            if ($this->result === false) {
                $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
                return $err;
            }
            $null = null;
            return $null;
        }
        // remove additional column at the end
        if ($this->offset > 0) {
            array_pop($row);
        }
        $mode = 0;
        $rtrim = false;
        if ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM) {
            if (empty($this->types)) {
                $mode += MDB2_PORTABILITY_RTRIM;
            } else {
                $rtrim = true;
            }
        }
        if ($mode) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row, $rtrim);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }

    // }}}
    // {{{ _getColumnNames()

    /**
     * Retrieve the names of columns returned by the DBMS in a query result.
     *
     * @return  mixed   Array variable that holds the names of columns as keys
     *                  or an MDB2 error on failure.
     *                  Some DBMS may not return any columns when the result set
     *                  does not contain any rows.
     * @access private
     */
    function _getColumnNames()
    {
        $columns = array();
        $numcols = $this->numCols();
        if (PEAR::isError($numcols)) {
            return $numcols;
        }
        for ($column = 0; $column < $numcols; $column++) {
            $column_name = @OCIColumnName($this->result, $column + 1);
            $columns[$column_name] = $column;
        }
        if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $columns = array_change_key_case($columns, $this->db->options['field_case']);
        }
        return $columns;
    }

    // }}}
    // {{{ numCols()

    /**
     * Count the number of columns returned by the DBMS in a query result.
     *
     * @return mixed integer value with the number of columns, a MDB2 error
     *      on failure
     * @access public
     */
    function numCols()
    {
        $cols = @OCINumCols($this->result);
        if (is_null($cols)) {
            if ($this->result === false) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            } elseif (is_null($this->result)) {
                return count($this->types);
            }
            return $this->db->raiseError(null, null, null,
                'Could not get column count', __FUNCTION__);
        }
        if ($this->offset > 0) {
            --$cols;
        }
        return $cols;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with $result.
     *
     * @return boolean true on success, false if $result is invalid
     * @access public
     */
    function free()
    {
        if (is_resource($this->result) && $this->db->connection) {
            $free = @OCIFreeCursor($this->result);
            if ($free === false) {
                return $this->db->raiseError(null, null, null,
                    'Could not free result', __FUNCTION__);
            }
        }
        $this->result = false;
        return MDB2_OK;

    }
}

/**
 * MDB2 OCI8 buffered result driver
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_BufferedResult_oci8 extends MDB2_Result_oci8
{
    var $buffer;
    var $buffer_rownum = - 1;

    // {{{ _fillBuffer()

    /**
     * Fill the row buffer
     *
     * @param int $rownum   row number upto which the buffer should be filled
                            if the row number is null all rows are ready into the buffer
     * @return boolean true on success, false on failure
     * @access protected
     */
    function _fillBuffer($rownum = null)
    {
        if (isset($this->buffer) && is_array($this->buffer)) {
            if (is_null($rownum)) {
                if (!end($this->buffer)) {
                    return false;
                }
            } elseif (isset($this->buffer[$rownum])) {
                return (bool)$this->buffer[$rownum];
            }
        }

        $row = true;
        while ((is_null($rownum) || $this->buffer_rownum < $rownum)
            && ($row = @OCIFetchInto($this->result, $buffer, OCI_RETURN_NULLS))
        ) {
            ++$this->buffer_rownum;
            // remove additional column at the end
            if ($this->offset > 0) {
                array_pop($buffer);
            }
            if (empty($this->types)) {
                foreach (array_keys($buffer) as $key) {
                    if (is_a($buffer[$key], 'oci-lob')) {
                        $buffer[$key] = $buffer[$key]->load();
                    }
                }
            }
            $this->buffer[$this->buffer_rownum] = $buffer;
        }

        if (!$row) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = false;
            return false;
        }
        return true;
    }

    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($this->result === false) {
            $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
            return $err;
        } elseif (is_null($this->result)) {
            return null;
        }
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        $target_rownum = $this->rownum + 1;
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if (!$this->_fillBuffer($target_rownum)) {
            $null = null;
            return $null;
        }
        $row = $this->buffer[$target_rownum];
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            $column_names = $this->getColumnNames();
            foreach ($column_names as $name => $i) {
                $column_names[$name] = $row[$i];
            }
            $row = $column_names;
        }
        $mode = 0;
        $rtrim = false;
        if ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM) {
            if (empty($this->types)) {
                $mode += MDB2_PORTABILITY_RTRIM;
            } else {
                $rtrim = true;
            }
        }
        if ($mode) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row, $rtrim);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }

    // }}}
    // {{{ seek()

    /**
     * Seek to a specific row in a result set
     *
     * @param int    $rownum    number of the row where the data can be found
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function seek($rownum = 0)
    {
        if ($this->result === false) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        }
        $this->rownum = $rownum - 1;
        return MDB2_OK;
    }

    // }}}
    // {{{ valid()

    /**
     * Check if the end of the result set has been reached
     *
     * @return mixed true or false on sucess, a MDB2 error on failure
     * @access public
     */
    function valid()
    {
        if ($this->result === false) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        } elseif (is_null($this->result)) {
            return true;
        }
        if ($this->_fillBuffer($this->rownum + 1)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ numRows()

    /**
     * Returns the number of rows in a result object
     *
     * @return mixed MDB2 Error Object or the number of rows
     * @access public
     */
    function numRows()
    {
        if ($this->result === false) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        } elseif (is_null($this->result)) {
            return 0;
        }
        $this->_fillBuffer();
        return $this->buffer_rownum;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with $result.
     *
     * @return boolean true on success, false if $result is invalid
     * @access public
     */
    function free()
    {
        $this->buffer = null;
        $this->buffer_rownum = null;
        return parent::free();
    }
}

/**
 * MDB2 OCI8 statement driver
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Statement_oci8 extends MDB2_Statement_Common
{
    // }}}
    // {{{ _execute()

    /**
     * Execute a prepared query statement helper method.
     *
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access private
     */
    function &_execute($result_class = true, $result_wrap_class = false)
    {
        if (is_null($this->statement)) {
            $result =& parent::_execute($result_class, $result_wrap_class);
            return $result;
        }
        $this->db->last_query = $this->query;
        $this->db->debug($this->query, 'execute', array('is_manip' => $this->is_manip, 'when' => 'pre', 'parameters' => $this->values));
        if ($this->db->getOption('disable_query')) {
            $result = $this->is_manip ? 0 : null;
            return $result;
        }

        $connection = $this->db->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $result = MDB2_OK;
        $lobs = $quoted_values = array();
        $i = 0;
        foreach ($this->positions as $parameter) {
            if (!array_key_exists($parameter, $this->values)) {
                return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'Unable to bind to missing placeholder: '.$parameter, __FUNCTION__);
            }
            $value = $this->values[$parameter];
            $type = array_key_exists($parameter, $this->types) ? $this->types[$parameter] : null;
            if ($type == 'clob' || $type == 'blob') {
                $lobs[$i]['file'] = false;
                if (is_resource($value)) {
                    $fp = $value;
                    $value = '';
                    while (!feof($fp)) {
                        $value.= fread($fp, 8192);
                    }
                } elseif (preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
// ILIAS patch. We need interpretation as string not as file handle. alex 13.10.2009
// See ILIAS Mantis Bug #4636
//                    $lobs[$i]['file'] = true;
//                    if ($match[1] == 'file://') {
//                        $value = $match[2];
//                    }
                }
                $lobs[$i]['value'] = $value;
                $lobs[$i]['descriptor'] = @OCINewDescriptor($connection, OCI_D_LOB);
                if (!is_object($lobs[$i]['descriptor'])) {
                    $result = $this->db->raiseError(null, null, null,
                        'Unable to create descriptor for LOB in parameter: '.$parameter, __FUNCTION__);
                    break;
                }
                $lob_type = ($type == 'blob' ? OCI_B_BLOB : OCI_B_CLOB);
                if (!@OCIBindByName($this->statement, ':'.$parameter, $lobs[$i]['descriptor'], -1, $lob_type)) {
                    $result = $this->db->raiseError($this->statement, null, null,
                        'could not bind LOB parameter', __FUNCTION__);
                    break;
                }
            } else {
                $quoted_values[$i] = $this->db->quote($value, $type, false);
                if (PEAR::isError($quoted_values[$i])) {
                    return $quoted_values[$i];
                }
                if (!@OCIBindByName($this->statement, ':'.$parameter, $quoted_values[$i])) {
                    $result = $this->db->raiseError($this->statement, null, null,
                        'could not bind non LOB parameter', __FUNCTION__);
                    break;
                }
            }
            ++$i;
        }

        $lob_keys = array_keys($lobs);
        if (!PEAR::isError($result)) {
            $mode = (!empty($lobs) || $this->db->in_transaction) ? OCI_DEFAULT : OCI_COMMIT_ON_SUCCESS;
            if (!@OCIExecute($this->statement, $mode)) {
                $err =& $this->db->raiseError($this->statement, null, null,
                    'could not execute statement', __FUNCTION__);
                return $err;
            }

            if (!empty($lobs)) {
                foreach ($lob_keys as $i) {
                    if (!is_null($lobs[$i]['value']) && $lobs[$i]['value'] !== '') {
                        if ($lobs[$i]['file']) {
                            $result = $lobs[$i]['descriptor']->savefile($lobs[$i]['value']);
                        } else {
                            $result = $lobs[$i]['descriptor']->save($lobs[$i]['value']);
                        }
                        if (!$result) {
                            $result = $this->db->raiseError(null, null, null,
                                'Unable to save descriptor contents', __FUNCTION__);
                            break;
                        }
                    }
                }

                if (!PEAR::isError($result)) {
                    if (!$this->db->in_transaction) {
                        if (!@OCICommit($connection)) {
                            $result = $this->db->raiseError(null, null, null,
                                'Unable to commit transaction', __FUNCTION__);
                        }
                    } else {
                        ++$this->db->uncommitedqueries;
                    }
                }
            }
        }

        $lob_keys = array_keys($lobs);
        foreach ($lob_keys as $i) {
            $lobs[$i]['descriptor']->free();
        }

        if (PEAR::isError($result)) {
            return $result;
        }

        if ($this->is_manip) {
            $affected_rows = $this->db->_affectedRows($connection, $this->statement);
            return $affected_rows;
        }

        $result =& $this->db->_wrapResult($this->statement, $this->result_types,
            $result_class, $result_wrap_class, $this->limit, $this->offset);
        $this->db->debug($this->query, 'execute', array('is_manip' => $this->is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }

    // }}}
    // {{{ free()

    /**
     * Release resources allocated for the specified prepared query.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function free()
    {
        if (is_null($this->positions)) {
            return $this->db->raiseError(MDB2_ERROR, null, null,
                'Prepared statement has already been freed', __FUNCTION__);
        }
        $result = MDB2_OK;

        if (!is_null($this->statement) && !@OCIFreeStatement($this->statement)) {
            $result = $this->db->raiseError(null, null, null,
                'Could not free statement', __FUNCTION__);
        }

        parent::free();
        return $result;
    }
}
?>