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
include_once './webservice/soap/lib/nusoap.php';

// These functions are wrappers for nusoap, since it cannot register methods inside classes

// USER ADMINISTRATION
function login($client,$username,$password)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
	
	$sua =& new ilSoapUserAdministration();
	
	return $sua->login($client,$username,$password);
}

function logout($sid)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->logout($sid);
}
function lookupUser($sid,$user_name)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->lookupUser($sid,$user_name);
}

function getUser($sid,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->getUser($sid,$user_id);
}

function updateUser($sid,$user_data)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->updateUser($sid,$user_data);
}
function addUser($sid,$user_data,$global_role_id)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->addUser($sid,$user_data,$global_role_id);
}
function deleteUser($sid,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->deleteUser($sid,$user_id);
}


// COURSE ADMINSTRATION
function addCourse($sid,$target_id,$crs_xml)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->addCourse($sid,$target_id,$crs_xml);
}
function deleteCourse($sid,$course_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->deleteCourse($sid,$course_id);
}
function assignCourseMember($sid,$course_id,$user_id,$type)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->assignCourseMember($sid,$course_id,$user_id,$type);
}
function excludeCourseMember($sid,$course_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->excludeCourseMember($sid,$course_id,$user_id,$type);
}
function getCourseXML($sid,$course_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->getCourseXML($sid,$course_id);
}
function updateCourse($sid,$course_id,$xml)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->updateCourse($sid,$course_id,$xml);
}	
	
?>
