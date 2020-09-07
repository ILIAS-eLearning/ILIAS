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
 * @classDescription Factory for ILIAS rpc client
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilRpcClientFactory
{
    
    /**
     * Creates an ilRpcClient instance to our ilServer
     *
     * @param string $a_package	 Package name
     * @param int $a_timeout The maximum number of seconds to allow ilRpcClient to connect.
     * @return ilRpcClient
     */
    public static function factory($a_package, $a_timeout = 0)
    {
        include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
        
        include_once './Services/WebServices/RPC/classes/class.ilRpcClient.php';
        $client = new ilRpcClient(ilRPCServerSettings::getInstance()->getServerUrl(), $a_package . '.', $a_timeout, 'UTF-8');
        return $client;
    }
}
