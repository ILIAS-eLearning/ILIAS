<?php
// vim: set et ts=4 sw=4 fdm=marker:

/**
 * DB_Table_Generator - Generates DB_Table subclass skeleton code
 *
 * PHP version 4 and 5
 *
 * @category Database
 * @package  DB_Table
 * @author   David C. Morse <morse@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  $Id: Generator.php,v 1.10 2007/04/03 03:27:22 morse Exp $
 */

// {{{ Includes

/**
 * The PEAR class (used for errors)
 */
require_once 'PEAR.php';

/**
 * DB_Table table abstraction class
 */
require_once 'DB/Table.php';

/**
 * DB_Table_Manager class (used to reverse engineer indices)
 */
require_once 'DB/Table/Manager.php';

// }}}
// {{{ Error code constants

/**
 * Parameter is not a DB/MDB2 object
 */
define('DB_TABLE_GENERATOR_ERR_DB_OBJECT', -301);

/**
 * Parameter is not a DB/MDB2 object
 */
define('DB_TABLE_GENERATOR_ERR_INDEX_COL', -302);

// }}}
// {{{ Error messages
/** 
 * US-English default error messages. 
 */
$GLOBALS['_DB_TABLE_GENERATOR']['default_error'] = array(
        DB_TABLE_GENERATOR_ERR_DB_OBJECT =>
        'Invalid DB/MDB2 object parameter. Function',
        DB_TABLE_GENERATOR_ERR_INDEX_COL =>
        'Index column is not a valid column name. Index column'
    );

// merge default and user-defined error messages
if (!isset($GLOBALS['_DB_TABLE_GENERATOR']['error'])) {
    $GLOBALS['_DB_TABLE_GENERATOR']['error'] = array();
}
foreach ($GLOBALS['_DB_TABLE_GENERATOR']['default_error'] as $code => $message) {
    if (!array_key_exists($code, $GLOBALS['_DB_TABLE_GENERATOR']['error'])) {
        $GLOBALS['_DB_TABLE_GENERATOR']['error'][$code] = $message;
    }
}

// }}}
// {{{ class DB_Table_Generator

/**
 * class DB_Table_Generator - Generates DB_Table subclass skeleton code
 *
 * This class generates the php code necessary to use the DB_Table
 * package to interact with an existing database. This requires the
 * generation of a skeleton subclass definition be generated for each 
 * table in the database, in which the $col, $idx, and $auto_inc_col
 * properties are constructed using a table schema that is obtained
 * by querying the database. 
 *
 * The class can also generate a file, named 'Database.php' by default,
 * that includes (require_once) each of the table subclass definitions,
 * instantiates one object of each DB_Table subclass (i.e., one object 
 * for each table), instantiates a parent DB_Table_Database object, 
 * adds all the tables to that parent, attempts to guess foreign key 
 * relationships between tables based on the column names, and adds
 * the inferred references to the parent object.
 *
 * All of the code is written to a directory whose path is given by
 * the property $class_write_path. By default, this is the current
 * directory.  By default, the name of the class constructed for a 
 * table named 'thing' is "Thing_Table". That is, the class name is 
 * the table name, with the first letter upper case, with a suffix 
 * '_Table'.  This suffix can be changed by setting the $class_suffix 
 * property. The file containing a subclass definition is the 
 * subclass name with a php extension, e.g., 'Thing_Table.php'. The 
 * object instantiated from that subclass is the same as the table 
 * name, with no suffix, e.g., 'thing'.
 * 
 * To generate the code for all of the tables in a database named 
 * $database, instantiate a MDB2 or DB object named $db that connects 
 * to the database of interest, and execute the following code:
 * <code>
 *     $generator = DB_Table_Generator($db, $database);
 *     $generator->class_write_path = $class_write_path;
 *     $generator->generateTableClassFiles();
 *     $generator->generateDatabaseFile();
 * </code>
 * Here $class_write_path should be the path (without a trailing 
 * separator) to a directory in which all of the code should be 
 * written. If this directory does not exist, it will be created. 
 * If the directory does already exist, existing files will not 
 * be overwritten. If $class_write_path is not set (i.e., if this
 * line is omitted) all the code will be written to the current 
 * directory.  If ->generateDatabaseFile() is called, it must be 
 * called after ->generateTableClassFiles(). 
 *
 * By default, ->generateTableClassFiles() and ->generateDatabaseFiles()
 * generate code for all of the tables in the current database. To 
 * generate code for a specified list of tables, set the value of the 
 * public $tables property to a sequential list of table names before 
 * calling either of these methods. Code can be generated for three 
 * tables named 'table1', 'table2', and 'table3' as follows:
 * <code>
 *     $generator = DB_Table_Generator($db, $database);
 *     $generator->class_write_path = $class_write_path;
 *     $generator->tables = array('table1', 'table2', 'table3');
 *     $generator->generateTableClassFiles();
 *     $generator->generateDatabaseFile();
 * </code>
 * If the $tables property is not set to a non-null value prior 
 * to calling ->generateTableClassFiles() then, by default, the 
 * database is queried for a list of all table names, by calling the
 * ->getTableNames() method from within ->generateTableClassFiles().
 * 
 * PHP version 4 and 5
 *
 * @category Database
 * @package  DB_Table
 * @author   David C. Morse <morse@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  $Id: Generator.php,v 1.10 2007/04/03 03:27:22 morse Exp $
 */
class DB_Table_Generator
{

    // {{{ properties

    /**
     * Name of the database
     *
     * @var    string
     * @access public
     */
    var $name   = null;

    /**
     * The PEAR DB/MDB2 object that connects to the database.
     *
     * @var    object
     * @access private
     */
    var $db = null;

    /**
     * The backend type. May have values 'db' or 'mdb2'
     *
     * @var    string
     * @access private
     */
    var $backend = null;

    /**
    * If there is an error on instantiation, this captures that error.
    *
    * This property is used only for errors encountered in the constructor
    * at instantiation time.  To check if there was an instantiation error...
    *
    * <code>
    * $obj =& new DB_Table_Generator();
    * if ($obj->error) {
    *     // ... error handling code here ...
    * }
    * </code>
    *
    * @var    object PEAR_Error
    * @access public
    */
    var $error = null;

    /**
     * Numerical array of table name strings
     *
     * @var    array
     * @access public
     */
    var $tables = array();

    /**
     * Class being extended (DB_Table or generic subclass)
     *
     * @var    string
     * @access public
     */
    var $extends = 'DB_Table';

    /**
     * Path to definition of the class $this->extends 
     *
     * @var    string
     * @access public
     */
    var $extends_file = 'DB/Table.php';

    /**
     * Suffix to add to table names to obtain corresponding class names
     *
     * @var    string
     * @access public
     */
    var $class_suffix = "_Table";

    /**
     * Path to directory in which subclass definitions should be written
     *
     * Value should not include a trailing "/".
     *
     * @var    string
     * @access public
     */
    var $class_write_path = '';

    /**
     * Include path to subclass definition files from database file
     *
     * Used to create require_once statements in the Database.php file,
     * which is in the same directory as the class definition files. Leave
     * as empty string if your PHP include_path contains ".". The value 
     * should not include a trailing "/", which is added automatically 
     * to values other than the empty string.
     *
     * @var    string
     * @access public
     */
    var $class_include_path = '';

    /**
     * Array of column definitions
     *
     * Array $this->col[table_name][column_name] = column definition.
     * Column definition is an array with the same format as the $col 
     * property of a DB_Table object
     *
     * @var    array
     * @access public
     */
    var $col          = array();

    /**
     * Array of index/constraint definitions.
     *
     * Array $this->idx[table_table][index_name] = Index definition. 
     * The index definition is an array with the same format as the
     * DB_Table $idx property property array.
     *
     * @var    array
     * @access public
     */
     var $idx = array();

    /**
     * Array of auto_increment column names
     *
     * Array $this->auto_inc_col[table_name] = auto-increment column
     *
     * @var    array
     * @access public
     */
     var $auto_inc_col = array();

    /**
     * Array of primary keys
     *
     * @var    array
     * @access public
     */
     var $primary_key = array();

    /**
     * MDB2 'idxname_format' option, format of index names 
     *
     * For use in printf() formatting. Use '%s' to use index names as
     * returned by getTableConstraints/Indexes, and '%s_idx' to add an
     * '_idx' suffix. For MySQL, use the default value '%'. 
     */
    var $idxname_format = '%s';

// }}}

// {{{    function DB_Table_Generator(&$db, $name)

    /**
     * Constructor
     *
     * If an error is encountered during instantiation, the error
     * message is stored in the $this->error property of the resulting
     * object. See $error property docblock for a discussion of error
     * handling. 
     * 
     * @param  object $db   DB/MDB2 database connection object
     * @param  string $name database name string
     * @return object DB_Table_Generator
     * @access public
     */
    function DB_Table_Generator(&$db, $name)
    {
        // Is $db an DB/MDB2 object or null?
        if (is_a($db, 'db_common')) {
            $this->backend = 'db';
        } elseif (is_a($db, 'mdb2_driver_common')) {
            $this->backend = 'mdb2';
        } else {
            $this->error =& DB_Table_Generator::throwError(
                            DB_TABLE_GENERATOR_ERR_DB_OBJECT,
                            "DB_Table_Generator");
            return;
        }
        $this->db  =& $db;
        $this->name = $name;

    }

// }}}
// {{{    function &throwError($code, $extra = null)

    /**
     * Specialized version of throwError() modeled on PEAR_Error.
     * 
     * Throws a PEAR_Error with a DB_Table_Generator error message based 
     * on a DB_Table_Generator constant error code.
     * 
     * @param string $code  A DB_Table_Generator error code constant.
     * @param string $extra Extra text for the error (in addition to the 
     *                      regular error message).
     * @return object PEAR_Error
     * @access public
     * @static
     */
    function &throwError($code, $extra = null)
    {
        // get the error message text based on the error code
        $text = 'DB_TABLE_GENERATOR ERROR - ' . "\n" .
                $GLOBALS['_DB_TABLE_GENERATOR']['error'][$code];
        
        // add any additional error text
        if ($extra) {
            $text .= ' ' . $extra;
        }
        
        // done!
        $error = PEAR::throwError($text, $code);
        return $error;
    }
   
// }}}
// {{{    function setErrorMessage($code, $message = null) 

    /**
     * Overwrites one or more error messages, e.g., to internationalize them.
     * 
     * @param mixed $code If string, the error message with code $code will be
     *                    overwritten by $message. If array, each key is a code
     *                    and each value is a new message. 
     * 
     * @param string $message Only used if $key is not an array.
     * @return void
     * @access public
     */
    function setErrorMessage($code, $message = null) 
    {
        if (is_array($code)) {
            foreach ($code as $single_code => $single_message) {
                $GLOBALS['_DB_TABLE_GENERATOR']['error'][$single_code] 
                    = $single_message;
            }
        } else {
            $GLOBALS['_DB_TABLE_GENERATOR']['error'][$code] = $message;
        }
    }

// }}}
// {{{    function getTableNames()

    /**
     * Gets a list of tables from the database
     * 
     * Upon successful completion, names are stored in the $this->tables 
     * array. If an error is encountered, a PEAR Error is returned, and 
     * $this->tables is reset to null. 
     *
     * @access  public
     * @return  mixed  true on success, PEAR Error on failure
     */
    function getTableNames()
    {

        if ($this->backend == 'db') {
            // try getting a list of schema tables first. (postgres)
            $this->db->expectError(DB_ERROR_UNSUPPORTED);
            $this->tables = $this->db->getListOf('schema.tables');
            $this->db->popExpect();
            if (PEAR::isError($this->tables)) {
                // try a list of tables, not qualified by 'schema'
                $this->db->expectError(DB_ERROR_UNSUPPORTED);
                $this->tables = $this->db->getListOf('tables');
                $this->db->popExpect();
            }
        } else {
            $this->db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);
            $this->db->loadModule('Manager');
            $this->db->loadModule('Reverse');

            // Get list of tables
            $this->tables = $this->db->manager->listTables();

        }
        if (PEAR::isError($this->tables)) {
            $error = $this->tables; 
            $this->tables = null;
            return $error; 
        } else {
            return true;
        }
    }

// }}}
// {{{    function getTableDefinition($table) 

    /**
     * Gets column and index definitions by querying database
     * 
     * Upon return, column definitions are stored in $this->col[$table], 
     * and index definitions in $this->idx[$table].
     *
     * Calls DB/MDB2::tableInfo() for column definitions, and uses 
     * the DB_Table_Manager class to obtain index definitions.
     *
     * @param   $table string name of table
     * @return  void
     * @access  public
     */
    function getTableDefinition($table) 
    {
        #// postgres strip the schema bit from the
        #if (!empty($options['generator_strip_schema'])) {
        #    $bits = explode('.', $table,2);
        #    $table = $bits[0];
        #    if (count($bits) > 1) {
        #        $table = $bits[1];
        #    }
        #}
        $db =& $this->db;
        if ($this->backend == 'db') {

            $defs =  $db->tableInfo($table);
            if (PEAR::isError($defs)) {
                return $defs;
            }   
            $this->columns[$table] = $defs;

        } else {

            // Columns
            $defs =  $db->reverse->tableInfo($table);
            if (PEAR::isError($defs)) {
                return $defs;
            }   

            // rename the 'length' key, so it matches db's return.
            foreach ($defs as $k => $v) {
                if (isset($defs[$k]['length'])) {
                    $defs[$k]['len'] = $defs[$k]['length'];
                }
            }

            $this->columns[$table] = $defs;

            // Temporarily set 'idxname_format' MDB2 option to $this->idx_format
            $idxname_format = $db->getOption('idxname_format');
            $db->setOption('idxname_format', $this->idxname_format);
        }

        // Default - no auto increment column 
        $this->auto_inc_col[$table] = null;

        // Loop over columns to create $this->col[$table]
        $this->col[$table] = array();
        foreach($defs as $t) {

            $name = $t['name'];
            $col  = array();
             
            switch (strtoupper($t['type'])) {
                case 'INT2':     // postgres
                case 'TINYINT':
                case 'TINY':     //mysql
                case 'SMALLINT':
                    $col['type'] = 'smallint';
                    break;
                case 'INT4':     // postgres
                case 'SERIAL4':  // postgres
                case 'INT':
                case 'SHORT':    // mysql
                case 'INTEGER':
                case 'MEDIUMINT':
                case 'YEAR':
                    $col['type'] = 'integer';
                    break;
                case 'BIGINT':
                case 'LONG':     // mysql
                case 'INT8':     // postgres
                case 'SERIAL8':  // postgres
                    $col['type'] = 'bigint';
                    break;
                case 'REAL':
                case 'NUMERIC':
                case 'NUMBER': // oci8 
                case 'FLOAT':  // mysql
                case 'FLOAT4': // real (postgres)
                    $col['type'] = 'single';
                    break;
                case 'DOUBLE':
                case 'DOUBLE PRECISION': // double precision (firebird)
                case 'FLOAT8': // double precision (postgres)
                    $col['type'] = 'double';
                    break;
                case 'DECIMAL':
                case 'MONEY':  // mssql and maybe others
                    $col['type'] = 'decimal';
                    break;
                case 'BIT':
                case 'BOOL':   
                case 'BOOLEAN':   
                    $col['type'] = 'boolean';
                    break;
                case 'STRING':
                case 'CHAR':
                    $col['type'] = 'char';
                    break;
                case 'VARCHAR':
                case 'VARCHAR2':
                case 'TINYTEXT':
                    $col['type'] = 'varchar';
                    break;
                case 'TEXT':
                case 'MEDIUMTEXT':
                case 'LONGTEXT':
                    $col['type'] = 'clob';
                    break;
                case 'DATE':    
                    $col['type'] = 'date';
                    break;
                case 'TIME':    
                    $col['type'] = 'time';
                    break;
                case 'DATETIME':   // mysql
                case 'TIMESTAMP':
                    $col['type'] = 'timestamp';
                    break;
                case 'ENUM':
                case 'SET':         // not really but oh well
                case 'TIMESTAMPTZ': // postgres
                case 'BPCHAR':      // postgres
                case 'INTERVAL':    // postgres (eg. '12 days')
                case 'CIDR':        // postgres IP net spec
                case 'INET':        // postgres IP
                case 'MACADDR':     // postgress network Mac address.
                case 'INTEGER[]':   // postgres type
                case 'BOOLEAN[]':   // postgres type
                    $col['type'] = 'varchar';
                    break;
                default:     
                    $col['type'] = $t['type'] . ' (Unknown type)';
                    break;
            }
        
            // Set length and scope if required 
            if (in_array($col['type'], array('char','varchar','decimal'))) { 
                if (isset($t['len'])) {
                    $col['size'] = (int) $t['len'];
                } elseif ($col['type'] == 'varchar') { 
                    $col['size'] = 255; // default length
                } elseif ($col['type'] == 'char') { 
                    $col['size'] = 128; // default length
                } elseif ($col['type'] == 'decimal') { 
                    $col['size'] =  15; // default length
                }
                if ($col['type'] == 'decimal') { 
                    $col['scope'] =  2;
                }
            }
            if (isset($t['notnull'])) {
                if ($t['notnull']) {
                   $col['required'] = true;
                }
            }
            if (isset($t['autoincrement'])) {
                $this->auto_inc_col[$table] = $name;
            }
            if (isset($t['flags'])){ 
                $flags = $t['flags'];
                if (preg_match('/not[ _]null/i',$flags)) {
                    $col['required'] = true;
                }
                if (preg_match("/(auto_increment|nextval\()/i", $flags)) {
                    $this->auto_inc_col[$table] = $name;
                } 
            }
            $required = isset($col['required']) ? $col['required'] : false;
            if ($required) {
                if (isset($t['default'])) {
                    $default = $t['default'];
                    $type    = $col['type'];
                    if (in_array($type, 
                                 array('smallint', 'integer', 'bigint'))) {
                        $default = (int) $default;
                    } elseif (in_array($type, array('single', 'double'))) {
                        $default = (float) $default;
                    } elseif ($type == 'boolean') {
                        $default = (int) $default ? 1 : 0;
                    }
                    $col['default'] = $default;
                }
            }
            $this->col[$table][$name] = $col;

        }

        // Make array with lower case column array names as keys
        $col_lc = array();
        foreach ($this->col[$table] as $name => $def) {
            $name_lc = strtolower($name);
            $col_lc[$name_lc] = $name;
        }

        // Constraints/Indexes
        $DB_indexes = DB_Table_Manager::getIndexes($db, $table);
        if (PEAR::isError($DB_indexes)) {
            return $DB_indexes;
        }   

        // Check that index columns correspond to valid column names.
        // Try to correct problems with capitalization, if necessary.
        foreach ($DB_indexes as $type => $indexes) {
            foreach ($indexes as $name => $fields) {
                foreach ($fields as $key => $field) {

                    // If index column is not a valid column name
                    if (!array_key_exists($field, $this->col[$table])) {

                        // Try a case-insensitive match
                        $field_lc = strtolower($field);
                        if (isset($col_lc[$field_lc])) {
                            $correct = $col_lc[$field_lc];
                            $DB_indexes[$type][$name][$key] 
                                 = $correct;
                        } else {
                            $return =& DB_Table_Generator::throwError(
                                          DB_TABLE_GENERATOR_ERR_INDEX_COL,
                                          "$field");
                        }

                    }
                }
            }
        }

        // Generate index definitions, if any, as php code
        $n_idx = 0;
        $u = array();
        $this->idx[$table] = array(); 
        $this->primary_key[$table] = null; 
        foreach ($DB_indexes as $type => $indexes) {
            if (count($indexes) > 0) {
                foreach ($indexes as $name => $fields) {
                    $this->idx[$table][$name] = array();
                    $this->idx[$table][$name]['type'] = $type;
                    if (count($fields) == 1) {
                        $key = $fields[0];
                    } else {
                        $key = array();
                        foreach ($fields as $value) {
                            $key[] = $value;
                        }
                    }
                    $this->idx[$table][$name]['cols'] = $key;
                    if ($type == 'primary') {
                        $this->primary_key[$table] = $key;
                    }
                }
            }
        }

        if ($this->backend == 'mdb2') {
            // Restore original MDB2 idxname_format
            $db->setOption('idxname_format', $idxname_format);
        }
    }

// }}}
// {{{ function buildTableClass($table, $indent = '')

    /**
     * Returns one skeleton DB_Table subclass definition, as php code
     *
     * The returned subclass definition string contains values for the 
     * $col (column), $idx (index) and $auto_inc_col properties, with
     * no method definitions.
     *
     * @param  $table   string  name of table
     * @param  $indent  string  string of whitespace for base indentation
     * @return string skeleton DB_Table subclass definition
     * @access public
     */
    function buildTableClass($table, $indent = '')
    {
        $s   = array();
        $idx = array();
        $s[] = $indent . 'class ' . $this->className($table) . 
               ' extends ' . $this->extends . " {\n";
        $indent = $indent . '    ';
        $s[] = $indent . 'var $col = array(' . "\n";
        $u   = array(); 
        $indent = $indent . '    ';
       
        // Begin loop over columns
        foreach($this->col[$table] as $name => $col) {

            // Generate DB_Table column definitions as php code
            $v = $indent . "'" . $name . "' => array(\n";
            $indent = $indent . '    ';
            $t = array();
            foreach ($col as $key => $value) {
                if (is_string($value)) {
                    $value = "'" . $value . "'";
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } else {
                    $value = (string) $value;
                }
                $t[] = $indent . "'" . $key . "'" . ' => ' . $value ;
            }
            $v = $v . implode($t,",\n") . "\n";
            $indent = substr($indent, 0, -4);
            $v = $v . $indent . ")";
            $u[] = $v;

        } //end loop over columns
        $s[] = implode($u,",\n\n") . "\n";
        $indent = substr($indent, 0, -4);
        $s[] = $indent . ");\n";

        // Generate index definitions, if any, as php code
        if (count($this->idx[$table]) > 0) {
            $u = array(); 
            $s[] = $indent . 'var $idx = array(' . "\n";
            $indent = $indent . '    ';
            foreach ($this->idx[$table] as $name => $def) {
                $type = $def['type'];
                $cols = $def['cols'];
                $v = $indent . "'" . $name . "' => array(\n";
                $indent = $indent . '    ';
                $v = $v . $indent . "'type' => '$type',\n";
                if (is_array($cols)) {
                    $v = $v . $indent . "'cols' => array(\n";
                    $indent = $indent . '    ';
                    $t = array();
                    foreach ($cols as $value) {
                        $t[] = $indent . "'{$value}'";
                    }
                    $v = $v . implode($t,",\n") . "\n";
                    $indent = substr($indent, 0, -4);
                    $v = $v . $indent . ")\n";
                } else {
                    $v = $v . $indent . "'cols' => '$cols'\n";
                }
                $indent = substr($indent, 0, -4);
                $v = $v . $indent . ")";
                $u[] = $v;
            }
            $s[] = implode($u,",\n\n") . "\n";
            $indent = substr($indent, 0, -4);
            $s[] = $indent . ");\n";
        }

        // Write auto_inc_col
        if (isset($this->auto_inc_col[$table])) {
           $s[] = $indent . 'var $auto_inc_col = ' 
                          . "'{$this->auto_inc_col[$table]}';\n";
        }
        $indent = substr($indent, 0, -4);
        $s[] = $indent . '}';

        // Implode and return lines of class definition
        return implode($s,"\n") . "\n";
        
    }

// }}}
// {{{    function buildTableClasses() 

    /**
     * Returns a string containing all table class definitions in one file
     *
     * The returned string contains the contents of a single php file with
     * definitions of DB_Table subclasses associated with all of the tables
     * in $this->tables. If $this->tables is initially null, method
     * $this->getTableNames() is called internally to generate a list of 
     * table names. 
     *
     * The returned string includes the opening and closing <?php and ?> 
     * script elements, and the require_once line needed to include the 
     * $this->extend_class (i.e., DB_Table or a subclass) that is being
     * extended. To use, write this string to a new php file. 
     *
     * Usage:
     * <code>
     *     $generator = DB_Table_Generator($db, $database);
     *     print $generator->buildTablesClasses();
     * </code>
     * 
     */
    function buildTableClasses() 
    {
        // If $this->tables is null, call getTableNames()
        if (!$this->tables) {
            $return = $this->getTableNames();
            if (PEAR::isError($return)) {
                return $return;
            }
        }

        $s = array();
        $s[] = "<?php";
        $s[] = "require_once '{$this->extends_file}';\n";
        foreach($this->tables as $table) {
            $this->getTableDefinition($table);
            $s[] = $this->buildTableClass($table) . "\n";
        }
        $s[] = '?>';
        return implode($s,"\n");
    }

// }}}
// {{{    function generateTableClassFiles() 

    /**
     * Writes all table class definitions to separate files
     *
     * Usage:
     * <code>
     *     $generator = DB_Table_Generator($db, $database);
     *     $generator->generateTableClassFiles();
     * </code>
     *
     * @return void
     * @access public 
     */
    function generateTableClassFiles() 
    {
        // If $this->tables is null, call getTableNames()
        if (!$this->tables) {
            $return = $this->getTableNames();
            if (PEAR::isError($return)) {
                return $return;
            }
        }

        // Write all table class definitions to separate files
        foreach($this->tables as $table) {
            $classname = $this->className($table);
            $filename  = $this->classFileName($classname);
            $base      = $this->class_write_path;
            if ($base) {
                if (!file_exists($base)) {
                    require_once 'System.php';
                    System::mkdir(array('-p', $base));
                }
                $filename = "$base/$filename";
            }
            if (!file_exists($filename)) {
                $s = array();
                $s[] = "<?php";
                $s[] = "require_once '{$this->extends_file}';\n";
                $this->getTableDefinition($table);
                $s[] = $this->buildTableClass($table) ;
                $s[] = '?>';
                $out = implode($s,"\n");
                $file = fopen( $filename, "w");
                fputs($file, $out);
                fclose($file);
            }
        }

    }

// }}}
// {{{    function generateDatabaseFile($object_name = null)

    /**
     * Writes a file to instantiate Table and Database objects
     *
     * After successful completion, a file named 'Database.php' will be
     * have been created in the $this->class_write_path directory. This 
     * file should normally be included in application php scripts. It
     * can be renamed by the user.
     *
     * Usage:
     * <code>
     *     $generator = DB_Table_Generator($db, $database);
     *     $generator->generateTableClassFiles();
     *     $generator->generateDatabaseFile();
     * </code>
     *
     * @param  string variable name for DB_Table_Database object
     * @return void
     * @access public 
     */
    function generateDatabaseFile($object_name = null)
    {
        // Set name for DB_Table_Database object
        if ($object_name) {
            $object_name = '$' . $object_name;
        } else {
            $object_name = '$db'; //default
        }
        $backend = strtoupper($this->backend); // 'DB' or 'MDB2'

        // Create array d[] containing lines of database php file
        $d = array();
        $d[] = "<?php";
        $d[] = "require_once '{$backend}.php';";
        $d[] = "require_once 'DB/Table/Database.php';";

        // Require_once statements for subclass definitions
        foreach ($this->tables as $table) {
            $classname = $this->className($table);
            $class_filename  = $this->classFileName($classname);
            if ($this->class_include_path) {
                $d[] = 'require_once ' .
                       "'{$this->class_include_path}/{$class_filename}';";
            } else {
                $d[] = "require_once '{$class_filename}';";
            }
        }
        $d[] = "";

        $d[] = '// NOTE: User must uncomment & edit code to create $dsn';
        $d[] = '# $phptype  = ' . "'mysqli';";
        $d[] = '# $username = ' . "'root';";
        $d[] = '# $password = ' . "'password';";
        $d[] = '# $hostname = ' . "'localhost';";
        $d[] = '# $dsn = "$phptype://$username:$password@$hostname";';
        $d[] = "";

        $d[] = '// Instantiate DB/MDB2 connection object $conn';
        $d[] = '$conn =& ' . $backend . '::connect($dsn);';
        $d[] = 'if (PEAR::isError($conn)) {';
        $d[] = '    print "Error connecting to database server\n";';
        $d[] = '    print $conn->getMessage();';
        $d[] = '    die;';
        $d[] = '}';
        $d[] = "";

        $d[] = '// Create one instance of each DB_Table subclass';
        foreach ($this->tables as $table) {
            $classname = $this->className($table);
            $d[] = '$' . $table . " = new $classname(" 
                       . '$conn, ' . "'{$table}');";
        }
        $d[] = "";

        $d[] = '// Instantiate a parent DB_Table_Database object';
        $d[] = $object_name . ' = new DB_Table_Database($conn, ' 
             . "'{$this->name}');";
        $d[] = "";

        $d[] = '// Add DB_Table objects to parent DB_Table_Database object';
        foreach ($this->tables as $table) {
            $classname = $this->className($table);
            $d[] = $object_name . '->addTable($' . $table . ');';
        }
        $d[] = "";

        // Add foreign key references: If the name of an integer column 
        // matches "/id$/i" (i.e., the names ends with id, ID, or Id), the
        // remainder of the name matches the name $rtable of another table,
        // and $rtable has an integer primary key, then the column is
        // assumed to be a foreign key that references $rtable.

        $d[] = '// Add auto-guessed foreign references';
        foreach ($this->col as $table => $col) {
            foreach ($col as $col_name => $def) {

                 // Only consider integer columns
                 $ftype = $def['type'];
                 if (!in_array($ftype, array('integer','smallint','bigint'))) {
                     continue;
                 }
                 if (preg_match("/id$/i", $col_name)) {
                     $column_base = preg_replace('/_?id$/i', '', $col_name);
                     foreach ($this->tables as $rtable) {
                         if (!preg_match("/^{$rtable}$/i", $column_base)) {
                             continue;
                         }
                         if (preg_match("/^{$table}$/i", $column_base)) {
                             continue;
                         }
                         if (!isset($this->primary_key[$rtable])) {
                             continue;
                         }
                         $rkey = $this->primary_key[$rtable];
                         if (is_array($rkey)) {
                             continue;
                         }
                         $rtype = $this->col[$rtable][$rkey]['type'];
                         if (!in_array($rtype, 
                                       array('integer','smallint','bigint'))) {
                             continue;
                         }
                         $d[] = $object_name 
                              . "->addRef('$table', '$col_name', '$rtable');";
                     }
                 }
            }
        }
        $d[] = "";
        $d[] = '// Add any additional foreign key references here';
        $d[] = "";
        $d[] = '// Add any linking table declarations here';
        $d[] = '// Uncomment next line to add all possible linking tables;';
        $d[] = '# ' . $object_name . '->addAllLinks();';
        $d[] = "";

        // Closing script element
        $d[] = "?>";

        // Open and write file
        $base = $this->class_write_path;
        if ($base) {
            if (!file_exists($base)) {
                require_once 'System.php';
                System::mkdir(array('-p', $base));
            }
            $filename = $base . "/Database.php";
        } else {
            $filename = "Database.php";
        }
        $file = fopen($filename, "w");
        $out = implode("\n", $d);
        fputs($file, $out);
        fclose($file);
    }

// }}}
// {{{    function className($table)

    /**
     * Convert a table name into a class name 
     *
     * Converts all non-alphanumeric characters to '_', capitalizes 
     * first letter, and adds $this->class_suffix to end. Override 
     * this if you want something else.
     *
     * @param   string $class_name name of table
     * @return  string class name;
     * @access  public
     */
    function className($table)
    {
        $name = preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($table)));
        return  $name . $this->class_suffix;
    }
    
// }}}
// {{{    function classFileName($class_name)
    
    /**
     * Returns the path to a file containing a class definition
     *
     * Appends '.php' to class name.
     *
     * @param   string $class_name name of class
     * @return  string file name   
     * @access  public
     */
    function classFileName($class_name)
    {
        $filename = $class_name . ".php" ;
        return $filename;
        
    }

// }}}

}
// }}}
