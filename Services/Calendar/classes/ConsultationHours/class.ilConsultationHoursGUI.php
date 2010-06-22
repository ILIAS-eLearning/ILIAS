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
 * Consultation hours editor
 * 
 * @ilCtrl_Calls: ilConsultationHoursGUI:
 */
class ilConsultationHoursGUI
{
	const MODE_CREATE = 1;
	const MODE_UPDATE = 2;
	
	protected $user_id;
	
	
	/**
	 * Constructor
	 */
	public function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	
	/**
	 * Execute command
	 * @return 
	 */
	public function executeCommand()
	{
		switch($this->ctrl->getNextClass())
		{
			default:
				
				$cmd = $this->ctrl->getCmd('settings');
				$this->$cmd();
		}
	}
	
	/**
	 * Show settings of consultation hours
	 * @todo add list/filter of consultation hours if user is responsible for more than one other consultation hour series.
	 * @return 
	 */
	protected function settings()
	{
		$this->initFormSettings();
	}
}
?>