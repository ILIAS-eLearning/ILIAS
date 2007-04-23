<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 *  
 * Some config values since we are not connected to ILIAS ini values yet
 * Including classes and modules common to all processes  
 * Initing database, since there is no operation without database
 */ 
 


	// zip exec will later be taken from ILIAS utils
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
{
	define('IL_OP_ZIP_EXE', realpath('infozip/zip.exe')); 
	define('IL_OP_UNZIP_EXE', realpath('infozip/unzip.exe'));
} 
else 
{
	define('IL_OP_ZIP_EXE', realpath('/usr/bin/zip'));
  define('IL_OP_UNZIP_EXE', realpath('/usr/bin/unzip'));
}

define('IL_OP_PACKAGES_FOLDER', dirname(__FILE__) . '/packages');
define('IL_OP_SAMPLES_FOLDER', dirname(__FILE__) . '/samples');

//define('IL_OP_PACKAGE_BASE', 'sco.php/packages/{packageId}/');
define('IL_OP_PACKAGE_BASE', 'packages/{packageId}/');
//define('IL_OP_PACKAGE_BASE', 'player.php/packages/{packageId}/');

//define('IL_OP_DB_TYPE', 'mysql');
//define('IL_OP_DB_DSN', 'mysql:host=localhost;dbname=ilscorm13');
define('IL_OP_DB_TYPE', 'sqlite');
define('IL_OP_DB_DSN', 'sqlite2:data/sqlite2.db');
define('IL_OP_USER_NAME', '');
define('IL_OP_USER_PASSWORD', '');
define('IL_OP_COOKIE_NAME', 'ilSCORM13');

/**
 * We will include some global functions extending poor PHP (this time a module 
 * and not a class). 
 * We also load a special database module running for sqlite. Will later be
 * mapped to ilDB (even if it is more rdbs independent and injection secure the
 * ILIAS default database code). 
 */
require_once('classes/phpext.php');
require_once('classes/ilSCORM13DB.php');


/**
 * Special database module is normally used in static mode. 
 * So there ist only one database and is accessable from everywhere. 
 * You could also use instances of ilSCORM13DB for binding to additional databases.  
 */
ilSCORM13DB::init(IL_OP_DB_DSN, IL_OP_DB_TYPE);


// login (pseudo)
// for test purposes only
// writes temp cookie in current path

$userId = $_GET['userId'] ? $_GET['userId'] : ($_COOKIE['userId'] ? $_COOKIE['userId'] : 50);
if ($userId != $_COOKIE['userId']) 
{
	setcookie('userId', $userId, 0, dirname($_SERVER['SCRIPT_NAME']));
} 

$USER = ilSCORM13DB::query(
	'SELECT usr_id FROM usr_data WHERE usr_id=?', 
	array($userId)
);
if (!$USER) die('not logged in');
else $USER = $USER[0];


?>
