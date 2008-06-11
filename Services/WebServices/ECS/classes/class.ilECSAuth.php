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
class ilECSAuth
{
	protected $log;
	protected $mids = array();
	
	
	public $hash;
	public $mid;
	public $eid;

	/**
	 * constuctor
	 *
	 * @access public
	 * @param 
	 * 
	 */
	public function __construct($a_eid,$a_mid)
	{
		global $ilLog;
		
		$this->log = $ilLog;
		
		$this->eid = (int) $a_eid;
		$this->mid = (int) $a_mid;
		$this->init();
	}
	
	/**
	 * get hash
	 *
	 * @access public
	 * 
	 */
	public function getHash()
	{
	 	return $this->hash;
	}
	
	/**
	 * set SOV
	 *
	 * @access public
	 * @param int start of verification
	 * 
	 */
	public function setSOV($a_sov)
	{
	 	include_once('Date.php');
	 	
	 	$date = new Date();
	 	$date->setDate($a_sov,DATE_FORMAT_UNIXTIME);
	 	$this->sov = $date->getDate().'+01:00';
	}

	/**
	 * set EOV
	 *
	 * @access public
	 * @param int eov of verification
	 * 
	 */
	public function setEOV($a_eov)
	{
	 	include_once('Date.php');
	 	
	 	$date = new Date();
	 	$date->setDate($a_eov,DATE_FORMAT_UNIXTIME);
	 	$this->eov = $date->getDate().'+01:00';
	}
	
	/**
	 * set abbreviation
	 *
	 * @access public
	 * 
	 */
	public function setAbbreviation($a_abr)
	{
	 	$this->abr = (string) $a_abr;
	}

	/**
	 * init
	 *
	 * @access protected
	 */
	protected function init()
	{
		$this->hash = md5(time());
		
	 	try
	 	{
	 		$this->mids = array();
	 		
	 		include_once('./Services/WebServices/ECS/classes/class.ilECSCommunityReader.php');
	 		$reader = ilECSCommunityReader::_getInstance();
	 		foreach($reader->getCommunities() as $com)
	 		{
	 			foreach($com->getParticipants() as $part)
	 			{
	 				if($part->isSelf())
	 				{
	 					$this->mids[] = $part->getMID();
	 					$this->log->write('Fetch MID: '.$part->getMID());
	 				}
	 			}
	 		}
	 	}
	 	catch(ilException $exc)
	 	{
	 		throw $exc;
	 	}
	}
}
?>