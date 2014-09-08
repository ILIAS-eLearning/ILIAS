<?php /*

Versions:
PEAR				1.7.1

Log					1.11.5

Auth				1.6.1


Auth_HTTP			2.1.6
	Depends:
	Auth			1.2.0
	
XML_RPC2			1.0.5

MDB2				2.4.1

MDB2_Driver_mysql	1.4.1
	Depends:
	MDB2			2.4.1

MDB2_Driver_oci8	1.4.1
	Depends:
	MDB2			2.4.1

MDB2_Driver_pgsql	1.4.1
	Depends:
	MDB2			2.4.1
 
MDB2_Driver_mysqli	1.4.1
	Depends:
	MDB2			2.4.1
	
HTML_Template_IT	1.2.1

HTTP_Request		1.4.4
	Depends:
	Net_URL			>= 1.0.12
	Net_Socket		>= 1.0.7		 

Mail:				1.2.0

OLE:				1.0 RC1
 
Spreadsheet:		0.9.2

Patches:

PEAR only the files PEAR.php and PEAR/FixPHP5PEARWarnings.php are hold in this package. Check includes 
of further required classes in PEAR.php before updating to a newer version.

MDB2/Driver/oci8.php, line 1398:
Patched interpretation of "http://" as string instead of file handle.
due to ILIAS bug #4636

MDB2/Driver/mysql.php, line 395
Performance fix

MDB2.php, line 927
Suppressed warnings of "is_readable" caused by open_basedir restrictions (leeds to performance issues and large log files)

MDB2/Driver/Datatype/Common.php, line 522:
Explicitely add " NULL" to declarations, otherwise setting oracle fields via
modifyTable from "NOT NULL" to "NULL" was not possible

MDB2/Driver/Datatype/Common.php, line 1262
do not try open clob fields with fopen even if allow_url_fopen is enabled
 
MDB2/Driver/mysqli.php, line 1525
patch for PEAR bug #17024
 
MDB2/Driver/mysqli.php, line 425
patch for hhvm usage

*/
?>