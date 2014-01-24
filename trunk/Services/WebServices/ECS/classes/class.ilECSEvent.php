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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/

class ilECSEvent
{
	const CREATED = 'created';
	const UPDATED = 'updated';
	const DESTROYED = 'destroyed';
	const NEW_EXPORT = 'new_export';

	protected $json_obj = null;
	public $status = '';
	public $ressource = '';
	public $ressource_id = 0;
	public $ressource_type = '';
	
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
	public function getStatus()
	{
	 	return $this->status;
	}
	
	/**
	 * getDescription
	 *
	 * @access public
	 * 
	 */
	public function getRessource()
	{
	 	return $this->ressource;
	}

	/**
	 * Get ressource id
	 */
	public function getRessourceId()
	{
		return $this->ressource_id;
	}


	/**
	 * Get ressource type
	 * @return string
	 */
	public function getRessourceType()
	{
		return $this->ressource_type;
	}

	
	/**
	 * Read community entries and participants
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	$this->status = $this->json_obj->status;
	 	$this->ressource = $this->json_obj->ressource;

		$res_arr = (array) explode('/',$this->getRessource());

		$this->ressource_id = array_pop($res_arr);
		$this->ressource_type = array_pop($res_arr);
	}
}
?>