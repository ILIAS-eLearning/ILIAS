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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
chdir('../..');

define ("ILIAS_MODULE", "webservice/soap");

global $HTTP_RAW_POST_DATA;

#if(substr(phpversion(),0,1) == '5' and $HTTP_RAW_POST_DATA)
#{
#	include_once './webservice/soap/classes/class.ilSoapUserAdministrationAdapter.php';
#
#	$server =& new ilSoapUserAdministrationAdapter();
#	$server->start();
#}
#else
{
	include_once './webservice/soap/classes/class.ilNusoapUserAdministrationAdapter.php';
	
	$server =& new ilNusoapUserAdministrationAdapter();
	$server->start();
	break;
}
?>