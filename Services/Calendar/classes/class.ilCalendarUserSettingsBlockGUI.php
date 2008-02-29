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
* @ilCtrl_IsCalledBy ilCalendarUserSettingsBlockGUI: ilColumnGUI
* @ingroup ServicesCalendar 
*/

class ilCalendarUserSettingsBlockGUI extends ilBlockGUI
{
	public static $block_type = 'cal';

	protected $tpl;
	protected $lng;
	

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
	 	global $ilUser,$tpl,$lng;

		$this->tpl = $tpl;
		$this->lng = $lng;
		
		parent::__construct();
	}
	
	/**
	 * get block type
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function getBlockType()
	{
		return self::$block_type;
	}
	
	/**
	 * is repository object
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		return $this->$cmd();
	}
	
	/**
	 * fill data section
	 *
	 * @access public
	 * 
	 */
	public function fillDataSection()
	{
	 	$this->setDataSection('');
	}
	
	/**
	 * get HTML
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getHTML()
	{
	 	return '';
	}
}

?>