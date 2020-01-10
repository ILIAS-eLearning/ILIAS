<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* soap server
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: server.php 14977 2007-10-12 11:58:35Z rkuester $
*
* @package ilias
*/

if (!defined('ILIAS_MODULE') || (defined('ILIAS_MODULE') && ILIAS_MODULE != "webservice/soap")) {
    //direct call to this endpoint
    chdir("../..");
    define("ILIAS_MODULE", "webservice/soap");
    define("IL_SOAPMODE_NUSOAP", 0);
    define("IL_SOAPMODE_INTERNAL", 1);
    define("IL_SOAPMODE", IL_SOAPMODE_NUSOAP);
}

include_once './webservice/soap/classes/class.ilNusoapUserAdministrationAdapter.php';
$server = new ilNusoapUserAdministrationAdapter(true);
$server->start();
