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
* @ingroup ServicesWebServicesECS
*/

class ilECSResult
{
	const RESULT_TYPE_JSON = 1;
	
	protected $result_string = '';
	protected $result;
	protected $result_type;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string result_string
	 * @param int result type 
	 * 
	 */
	public function __construct($a_res,$a_type = self::RESULT_TYPE_JSON)
	{
	 	$this->result_string = $a_res;
	 	$this->result_type = $a_type;
	
		$this->init(); 	
	}
	
	/**
	 * get unformated result string
	 *
	 * @access public
	 * 
	 */
	public function getPlainResultString()
	{
	 	return $this->result_string;
	}

	/**
	 * get result
	 *
	 * @access public
	 * @return mixed JSON object, array of objects or false in case of errors.
	 * 
	 */
	public function getResult()
	{
		return $this->result;	 	
	}
	
	/**
	 * init result (json_decode) 
	 * @access private
	 * 
	 */
	private function init()
	{
		if(!$this->result_string)
		{
			$this->result = array();
			return true;
		}

	 	switch($this->result_type)
	 	{
	 		case self::RESULT_TYPE_JSON:
				$this->result = json_decode($this->result_string);
				break;
	 	}
	 	return true;
	}
}

?>