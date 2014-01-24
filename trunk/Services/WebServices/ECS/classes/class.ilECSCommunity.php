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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/

class ilECSCommunity
{
	protected $json_obj = null;
	protected $title = '';
	protected $description = '';
	protected $id = 0;
	
	protected $participants = array();
	protected $position = 0;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object json object
	 * 
	 */
	public function __construct($json_obj)
	{
		$this->json_obj = $json_obj;
		$this->read();		 	
	}
	
	/**
	 * get title
	 *
	 * @access public
	 * 
	 */
	public function getTitle()
	{
	 	return $this->title;
	}
	
	/**
	 * getDescription
	 *
	 * @access public
	 * 
	 */
	public function getDescription()
	{
	 	return $this->description;
	}
	
	/**
	 * get participants
	 *
	 * @access public
	 * 
	 */
	public function getParticipants()
	{
	 	return $this->participants ? $this->participants : array();
	}

	/**
	 * Get array of mids of all participants
	 */
	public function getMids()
	{
		$mids = array();
		foreach($this->getParticipants() as $part)
		{
			$mids[] = $part->getMID();
		}
		return $mids;
	}

	/**
	 * Get own mid of community
	 */
	public function getOwnId()
	{
		foreach($this->getParticipants() as $part)
		{
			if($part->isSelf())
			{
				return $part->getMID();
			}
		}
		return 0;
	}

	
	/**
	 * get id
	 *
	 * @access public
	 * 
	 */
	public function getId()
	{
	 	return $this->id;
	}
	
	
	/**
	 * Read community entries and participants
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	$this->title = $this->json_obj->community->name;
	 	$this->description = $this->json_obj->community->description;
	 	$this->id = $this->json_obj->community->cid;
	 	
		foreach($this->json_obj->participants as $participant)
		{
			include_once('./Services/WebServices/ECS/classes/class.ilECSParticipant.php');
			$this->participants[] = new ilECSParticipant($participant,$this->getId());
		}
	}
}


?>