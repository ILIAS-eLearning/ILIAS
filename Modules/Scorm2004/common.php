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

	// where to save zip's and to save unzipped files in a folder per package
define('IL_OP_PACKAGES_FOLDER', dirname(__FILE__) . '/packages');

	// DEBUG only: there is only one fixed sahs in this mode
	// creating new sahs is job of ILIAS environment 
define('IL_OP_SAHS_ID', '100');

	// DEBUG only: where to save zip's on the server as a kind of remote repository
	// so you don't have to upload everytime
	// later to be loaded from ILIAS upload folder 
define('IL_OP_SAMPLES_FOLDER', dirname(__FILE__) . '/samples');

	// href template for loading package data 
define('IL_OP_PACKAGE_BASE', 'sco.php/packages/{packageId}/');

	// DEBUG only: database connection data
	// later to be replaced by ILIAS ilDB object 
//define('IL_OP_DB_TYPE', 'mysql');
//define('IL_OP_DB_DSN', 'mysql:host=localhost;dbname=ilscorm13');
define('IL_OP_DB_TYPE', 'sqlite');
define('IL_OP_DB_DSN', 'sqlite2:data/sqlite2.db');
define('IL_OP_USER_NAME', '');
define('IL_OP_USER_PASSWORD', '');

/**
 * We will include some global functions extending poor PHP.
 * Also adding some DEBUG initialitation later to be replace by ILIAS core functions  
 * This is a module and not a class.  
 */
require_once('classes/phpext.php');

/**
 * We also load a special database module running for sqlite. Will later be
 * mapped to ilDB (even if it is more rdbs independent and injection secure the
 * ILIAS default database code). 
 */
require_once('classes/ilSCORM13DB.php');

/**
 * We load some utility function in a static class emulating some ILIAS
 * core function, like startup, zipping etc..
 */
require_once('classes/ilSCORM13Utils.php');

/**
 * We need a simple template engine roughly emulating PEAR template
 */
require_once('classes/ilSCORM13Template.php');


	// start to whole thing (database, login, etc.)
ilSCORM13Utils::init();

?>
