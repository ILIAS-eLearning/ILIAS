<?php

namespace GetId3\Extension\Cache;

use GetId3\GetId3Core;
use GetId3\Exception\DefaultException;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// extension.cache.dbm.php - part of GetId3()                  //
// Please see readme.txt for more information                  //
//                                                            ///
/////////////////////////////////////////////////////////////////
//                                                             //
// This extension written by Allan Hansen <ahØartemis*dk>      //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
* This is a caching extension for GetId3(). It works the exact same
* way as the GetId3 class, but return cached information very fast
*
* Example:
*
*    Normal GetId3 usage (example):
*
*       require_once 'getid3/getid3.php';
*       $getID3 = new GetId3;
*       $getID3->encoding = 'UTF-8';
*       $info1 = $getID3->analyze('file1.flac');
*       $info2 = $getID3->analyze('file2.wv');
*
*    GetId3_cached usage:
*
*       require_once 'getid3/getid3.php';
*       require_once 'getid3/getid3/extension.cache.dbm.php';
*       $getID3 = new GetId3_cached('db3', '/tmp/getid3_cache.dbm',
*                                          '/tmp/getid3_cache.lock');
*       $getID3->encoding = 'UTF-8';
*       $info1 = $getID3->analyze('file1.flac');
*       $info2 = $getID3->analyze('file2.wv');
*
*
* Supported Cache Types
*
*   SQL Databases:          (use extension.cache.mysql)
*
*   cache_type          cache_options
*   -------------------------------------------------------------------
*   mysql               host, database, username, password
*
*
*   DBM-Style Databases:    (this extension)
*
*   cache_type          cache_options
*   -------------------------------------------------------------------
*   gdbm                dbm_filename, lock_filename
*   ndbm                dbm_filename, lock_filename
*   db2                 dbm_filename, lock_filename
*   db3                 dbm_filename, lock_filename
*   db4                 dbm_filename, lock_filename  (PHP5 required)
*
*   PHP must have write access to both dbm_filename and lock_filename.
*
*
* Recommended Cache Types
*
*   Infrequent updates, many reads      any DBM
*   Frequent updates                    mysql
*/

/**
 *
 * @author James Heinrich <info@getid3.org>
 * @author Allan Hansen <ahØartemis*dk>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Dbm extends GetId3
{

    /**
     * public: constructor - see top of this file for cache type and cache_options
     *
     * @param  type      $cache_type
     * @param  type      $dbm_filename
     * @param  type      $lock_filename
     * @throws Exception
     */
    public function __construct($cache_type, $dbm_filename, $lock_filename)
    {
        // Check for dba extension
        if (!extension_loaded('dba')) {
            throw new DefaultException('PHP is not compiled with dba support, required to use DBM style cache.');
        }

        // Check for specific dba driver
        if (!function_exists('dba_handlers') || !in_array($cache_type, dba_handlers())) {
            throw new DefaultException('PHP is not compiled --with '.$cache_type.' support, required to use DBM style cache.');
        }

        // Create lock file if needed
        if (!file_exists($lock_filename)) {
            if (!touch($lock_filename)) {
                throw new DefaultException('failed to create lock file: '.$lock_filename);
            }
        }

        // Open lock file for writing
        if (!is_writeable($lock_filename)) {
            throw new DefaultException('lock file: '.$lock_filename.' is not writable');
        }
        $this->lock = fopen($lock_filename, 'w');

        // Acquire exclusive write lock to lock file
        flock($this->lock, LOCK_EX);

        // Create dbm-file if needed
        if (!file_exists($dbm_filename)) {
            if (!touch($dbm_filename)) {
                throw new DefaultException('failed to create dbm file: '.$dbm_filename);
            }
        }

        // Try to open dbm file for writing
        $this->dba = dba_open($dbm_filename, 'w', $cache_type);
        if (!$this->dba) {

            // Failed - create new dbm file
            $this->dba = dba_open($dbm_filename, 'n', $cache_type);

            if (!$this->dba) {
                throw new DefaultException('failed to create dbm file: '.$dbm_filename);
            }

            // Insert GetId3 version number
            dba_insert(GetId3Core::VERSION, GetId3Core::VERSION, $this->dba);
        }

        // Init misc values
        $this->cache_type   = $cache_type;
        $this->dbm_filename = $dbm_filename;

        // Register destructor
        register_shutdown_function(array($this, '__destruct'));

        // Check version number and clear cache if changed
        if (dba_fetch(GetId3Core::VERSION, $this->dba) != GetId3Core::VERSION) {
            $this->clear_cache();
        }

        parent::__construct();
    }

    /**
     *
     */
    public function __destruct()
    {
        // Close dbm file
        dba_close($this->dba);

        // Release exclusive lock
        flock($this->lock, LOCK_UN);

        // Close lock file
        fclose($this->lock);
    }

    /**
     * clear cache
     *
     * @throws Exception
     */
    public function clear_cache()
    {
        // Close dbm file
        dba_close($this->dba);

        // Create new dbm file
        $this->dba = dba_open($this->dbm_filename, 'n', $this->cache_type);

        if (!$this->dba) {
            throw new DefaultException('failed to clear cache/recreate dbm file: '.$this->dbm_filename);
        }

        // Insert GetId3 version number
        dba_insert(GetId3Core::VERSION, GetId3Core::VERSION, $this->dba);

        // Re-register shutdown function
        register_shutdown_function(array($this, '__destruct'));
    }

    /**
     * analyze file
     *
     * @param  type $filename
     * @return type
     */
    public function analyze($filename)
    {
        if (file_exists($filename)) {

            // Calc key     filename::mod_time::size    - should be unique
            $key = $filename.'::'.filemtime($filename).'::'.filesize($filename);

            // Loopup key
            $result = dba_fetch($key, $this->dba);

            // Hit
            if ($result !== false) {
                return unserialize($result);
            }
        }

        // Miss
        $result = parent::analyze($filename);

        // Save result
        if (file_exists($filename)) {
            dba_insert($key, serialize($result), $this->dba);
        }

        return $result;
    }
}
