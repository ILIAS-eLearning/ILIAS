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
 

define('IL_OP_ZIP_EXE', 'C:/PortableApps/XamppPortable/Data/docs/ilias3/bin/InfoZip/unzip/unzip.exe');
define('IL_OP_UNZIP_EXE', 'C:/PortableApps/XamppPortable/Data/docs/ilias3/bin/InfoZip/unzip/unzip.exe');
define('IL_OP_PACKAGES_FOLDER', dirname(__FILE__) . '/packages');
define('IL_OP_USER_ID', 50);
define('IL_OP_PACKAGE_ID', 100);
define('IL_OP_PACKAGE_BASE', 'sco.php/packages/{packageId}/');
define('IL_OP_DB_TYPE', 'sqlite');
define('IL_OP_DB_DSN', 'sqlite2:data/sqlite2.db');
define('IL_OP_USER_NAME', 'Anonymous');

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

?>
