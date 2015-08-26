<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Jens Conze <jc@databay.de>
* @author Michael Jansen <mjansen@databay.de>
* @version $Id: class.ilCronForumNotification.php 23116 2010-03-05 10:43:59Z nkrzywon $
*
* @package ilias
*/
class ilCronEvento
{
	public function start()
	{
		global $ilias, $rbacsystem, $ilAccess, $ilDB, $lng;
                require_once './cron/classesEvento/class.iliasImportEventoWS.php';
                require_once './cron/classesEvento/nusoap/lib/class.nusoap_base.php';
                require_once './cron/classesEvento/nusoap/lib/class.soapclient.php';
                require_once './cron/classesEvento/nusoap/lib/class.wsdl.php';
                require_once './cron/classesEvento/nusoap/lib/class.soap_transport_http.php';
                require_once './cron/classesEvento/nusoap/lib/class.xmlschema.php';
                require_once './cron/classesEvento/nusoap/lib/class.soap_parser.php';
                require_once "./include/Unicode/UtfNormal.php";
                
                $iliasImportEventoWS = new iliasImportEventoWS();

                print $iliasImportEventoWS->import();

	}

}
?>
