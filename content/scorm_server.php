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
* scorm learning module presentation script
*
* @author Ralph Barthel <ralph.barthel@21ll.com> , 21 LearnLine AG
* @version $Id: scorm_server.php,v 1.0 2003/08/12 
*
* @package content
*/
chdir("..");
require_once "./include/inc.header.php";
require_once "./content/classes/SCORM/class.ilObjSCORMTracking.php";
require_once "./content/classes/SCORM/class.ilObjDebug.php";
$scorm_communication=new ilObjSCORMTracking($_GET["user_id"],$_GET["item_id"]);
$debug = new ilObjDebug("/opt/ilias/www/htdocs/ilias3/debug/debug.scorm_server");

if (isset($_GET["value"])) //setValue Call
{
  $temp='$scorm_communication->'.$_GET["function"].'("'.$_GET["var"].','.$_GET["value"].'");';
	$debug->debug("Method: ".$temp);
   $retval=eval("$temp");
	$debug->debug("ReturnValue: ".$retval);
   return $retval;
   
}
else
{
  if ($_GET["var"]=="null") {
  	$temp='$scorm_communication->'.$_GET["function"].'("");';
  }	else {
	  $temp='$scorm_communication->'.$_GET["function"].'("'.$_GET["var"].'");';
	}
	$debug->debug("Method: ".$temp);
  $retval=eval("$temp");
	$debug->debug("ReturnValue: ".$retval);
    return $retval;
}

?>
