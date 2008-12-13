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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesWebServicesECS 
*/

class ilECSParticipant
{
	protected $json_obj;
	protected $cid;
	protected $mid;
	protected $email;
	protected $certid;
	protected $dns;
	protected $description;
	protected $participantname;
	protected $abr;
	protected $is_self;
	
	protected $settings;
	protected $part_settings;


	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($json_obj,$a_cid)
	{
		include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php');
		
		$this->settings = ilECSSettings::_getInstance();
		$this->part_settings = ilECSParticipantSettings::_getInstance();
		$this->json_obj = $json_obj;
		$this->cid = $a_cid;
		$this->read();		 	
	}
	
	/**
	 * get community id
	 *
	 * @access public
	 * 
	 */
	public function getCommunityId()
	{
	 	return $this->cid;
	}
	
	/**
	 * get mid
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getMID()
	{
	 	return $this->mid; 
	}
	
	/**
	 * get email
	 *
	 * @access public
	 * 
	 */
	public function getEmail()
	{
	 	return $this->email;
	}

	/**
	 * get cert id
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getCertId()
	{
	 	return $this->certid;
	}
	
	/**
	 * get dns
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getDNS()
	{
	 	return $this->dns;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 * 
	 */
	public function getDescription()
	{
	 	return $this->description;
	}

	/**
	 * get participant name
	 *
	 * @access public
	 * 
	 */
	public function getParticipantName()
	{
	 	return $this->participantname;
	}
	
	/**
	 * get abbreviation of participant
	 *
	 * @access public
	 * 
	 */
	public function getAbbreviation()
	{
	 	return $this->abr;
	}
	
	/**
	 * is publishable (enabled and mid with own cert id)
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isPublishable()
	{
	 	return $this->isSelf();
	}
	
	/**
	 * is self
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isSelf()
	{
	 	return (int) $this->getCertId() == (int) $this->settings->getCertSerialNumber();
	}
	
	
	/**
	 * is Enabled
	 *
	 * @access public
	 * 
	 */
	public function isEnabled()
	{
	 	return (bool) $this->part_settings->isEnabled($this->getMID());
	}
	/**
	 * Read
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	$this->mid = $this->json_obj->mid;
	 	$this->email = $this->json_obj->email;
	 	$this->certid = strtolower($this->json_obj->certid);
	 	$this->dns = $this->json_obj->dns;
	 	$this->description = $this->json_obj->description;
	 	$this->participantname = $this->json_obj->participantname;
	 	$this->abr = $this->json_obj->abr;
		return true;
	}
}


?>