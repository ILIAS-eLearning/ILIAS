<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

define("MATERIAL_TYPE_INTERNALLINK", 0);
define("MATERIAL_TYPE_URL", 1);
define("MATERIAL_TYPE_FILE", 2);

/**
* Survey material class
*
* @author		Helmut Schottmüller <ilias@aurealis.de>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class ilSurveyMaterial 
{
	protected $data;

	/**
	* ilSurveyMaterial constructor
	*/
	function __construct() 
	{
		$this->data = array(
			'type' => MATERIAL_TYPE_INTERNALLINK,
			'internal_link' => '',
			'title' => '',
			'url' => '',
			'filename' => ''
		);
	}

	public function __set($name, $value) 
	{
		$this->data[$name] = $value;
	}

	public function __get($name) 
	{
		if (array_key_exists($name, $this->data)) 
		{
			switch ($name)
			{
				case 'internal_link':
				case 'import_id':
				case 'material_title':
				case 'text_material':
				case 'file_material':
				case 'external_link':
					return (strlen($this->data[$name])) ? $this->data[$name] : NULL;
					break;
				default:
					return $this->data[$name];
			}
		}
		return null;
	}
}

?>
