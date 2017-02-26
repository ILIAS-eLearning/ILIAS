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
 * @classDescription class for ILIAS ViewFull
 * 
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de
 * @version $id$
 *  
 * @ingroup ServicesView
 */
 
include_once 'Services/View/classes/class.ilBaseViewGUI.php'; 

class ilFullViewGUI extends ilBaseViewGUI
{
	private static $instance = null;
	
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->checkActivation();
	}
	
	/**
	 * Get instance
	 * @return object ilMemberViewSettings
	 */
	public static function getInstance()
	{
		if(self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new ilFullViewGUI();
	}
	/** 
	 * always enabled
	 * @return bool
	 */ 
	public function isEnabled() {
		return true;
	}
	
	public function checkActivation() {
		if (!$this->isEnabled()) {
			$this->active = false;
			return false;
		}
		// no check for the first default ilViewFull, always active on start
		$this->active = true;
		return true;
	}
	
	public function getMainMenu() {
		return array(
				"spacer_class" => "ilFixedTopSpacer",
				"main_menu_list_entries" => self::KEEP,
				"search" => self::KEEP,
				"statusbox" => self::KEEP,
				"main_header" => self::KEEP,
				"user_logged_in" => self::KEEP
			);
	}
}
?>
