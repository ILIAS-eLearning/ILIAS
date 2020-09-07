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
* adapter class for nusoap server
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/

class ilSoapUserAdministrationAdapter
{
    /*
     * @var object Nusoap-Server
     */
    public $server = null;

    
    public function __construct($a_use_wsdl = true)
    {
        $this->server = new SoapServer();

        $this->__registerMethods();
    }

    public function start()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->server->handle();
        }
    }

    // PRIVATE
    public function __registerMethods()
    {
        include_once './webservice/soap/include/inc.soap_functions.php';

        $this->server->addFunction(SOAP_FUNCTIONS_ALL);
    }
}
