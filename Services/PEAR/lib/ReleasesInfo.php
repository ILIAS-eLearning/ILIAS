<?php /*

Versions:
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
HTML_Template_IT	1.2.1

HTTP_Request		1.4.4
	Depends:
	Net_URL			>= 1.0.12
	Net_Socket		>= 1.0.7		 

Patches:
MDB2/Driver/oci8.php, line 1398:
Patched interpretation of "http://" as string instead of file handle.
due to ILIAS bug #4636

*/
?>