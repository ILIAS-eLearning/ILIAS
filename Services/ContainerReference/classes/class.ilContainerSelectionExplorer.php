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

include_once('./classes/class.ilExplorer.php');

/** 
* 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup 
*/
class ilContainerSelectionExplorer extends ilExplorer
{
	protected $target_type;
	
	/**
	 * Constructor 
	 */
	public function __construct($a_target)
	{
		 parent::__construct($a_target);
	}
	
	/**
	 * set target type 
	 * @param
	 * @return
	 */
	public function setTargetType($a_type)
	{
		$this->target_type = $a_type;
	}
	
	/**
	 * get target type 
	 * @param
	 * @return
	 */
	public function getTargetType()
	{
		return $this->target_type;
	}
	
	/**
	 * check if item is clickable
	 * @param
	 * @return
	 */
	public function isClickable($a_type,$a_id)
	{
		if($this->getTargetType() == $a_type)
		{
			// TODO: check permission
			return true;
		}
		return false;
	}
}
?>
