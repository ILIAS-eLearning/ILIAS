<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesWebServicesECS 
*/

class ilECSUtils
{
	/**
	 * fetch new econtent id from location header
	 *
	 * @access public
	 * @static
	 *
	 * @param array header array
	 */
	public static function _fetchEContentIdFromHeader($a_header)
	{
		global $ilLog;
		
		if(!isset($a_header['Location']))
		{
			return false;
		}
		$end_path = strrpos($a_header['Location'],"/");
		
		if($end_path === false)
		{
			$ilLog->write(__METHOD__.': Cannot find path seperator.');
			return false;
		}
		$econtent_id = substr($a_header['Location'],$end_path + 1);
		$ilLog->write(__METHOD__.': Received EContentId '.$econtent_id);
		return (int) $econtent_id;
	}
	
	/**
	 * get optional econtent fields
	 * These fields might be mapped against AdvancedMetaData field definitions
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getOptionalEContentFields()
	{
		return array(
			'study_courses',
			'lecturer',
			'courseType',
			'courseID',
			'term',
			'credits',
			'semester_hours',
			'begin',
			'end',
			'room',
			'cycle');
	}
	
	/**
	 * Lookup participant name 
	 * @param int	$a_owner	Mid of participant
	 * @return
	 */
	public static function lookupParticipantName($a_owner)
	{
		global $ilLog;
		
		try {
			include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
			$reader = ilECSCommunityReader::_getInstance();
			if($part = $reader->getParticipantByMID($a_owner))
			{
				return $part->getParticipantName();
			}
			return '';
		}
		catch(ilECSConnectorException $e)
		{
			$ilLog->write(__METHOD__.': Error reading participants.');
			return '';	
		}
	}
}

?>