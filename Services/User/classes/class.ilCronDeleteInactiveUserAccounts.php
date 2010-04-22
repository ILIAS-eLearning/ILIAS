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
	* 
	*
	* @author Bjoern Heyser <bheyser@databay.de>
	* @version $Id$
	*
	* @package ilias
	*/

	class ilCronDeleteInactiveUserAccounts
	{
		const INTERVAL_DAILY		= '1';
		const INTERVAL_WEEKLY		= '2';
		const INTERVAL_MONTHLY		= '3';
		const INTERVAL_QUARTERLY	= '4';
	
		const DEFAULT_INACTIVITY_PERIOD = 365;
		
		private $interval = null;
		
		private $include_roles = null;
		
		private $inactivity_period = null;
		
		private $enabled = false;
		
		public function __construct()
		{
			global $ilSetting;
			
			$this->interval = $ilSetting->get(
				'cron_inactive_user_delete_interval',
				self::getDefaultIntervalKey()
			);
			
			$this->include_roles = $ilSetting->get(
				'cron_inactive_user_delete_include_roles', null
			);
			if($this->include_roles === null) $this->include_roles = array();
			else $this->include_roles = explode(',', $this->include_roles);
						
			$this->inactivity_period = $ilSetting->get(
				'cron_inactive_user_delete_period',
				self::DEFAULT_INACTIVITY_PERIOD
			);
			
			$last_run = (int)$ilSetting->get('cron_inactive_user_delete_last_run', 0);
			
			if( !$last_run || (time() - $last_run) > $this->getCurrentIntervalPeriod() )
			{
				$this->enabled = true;
				
				$ilSetting->set('cron_inactive_user_delete_last_run', time());
			}
		}
		
		public function run()
		{
			if( !$this->enabled ) return;

			global $rbacreview;
			
			$usr_ids = ilObjUser::_getUserIdsByInactivityPeriod($this->inactivity_period);
			
			foreach($usr_ids as $usr_id)
			{
				if($usr_id == ANONYMOUS_USER_ID || $usr_id == SYSTEM_USER_ID) continue;
				
				$continue = true;
				foreach($this->include_roles as $role_id)
				{
					if( $rbacreview->isAssigned($usr_id, $role_id) )
					{
						$continue = false;
						break;
					}
				}
				if($continue) continue;
					
				$user = ilObjectFactory::getInstanceByObjId($usr_id);
				
				$user->delete();
			}
		}
		
		private function getCurrentIntervalPeriod()
		{
			$period = 60 * 60;
			
			switch( $this->interval )
			{
				case self::INTERVAL_QUARTERLY:	$period *= 3;
				case self::INTERVAL_MONTHLY:	$period *= 30;
				case self::INTERVAL_WEEKLY:		$period *= 7;
				case self::INTERVAL_DAILY:		$period *= 24;
			}
			
			return $period;
		}
		
		public static function getPossibleIntervalsArray()
		{
			global $lng;
			
			return array(
				self::INTERVAL_DAILY		=> $lng->txt("daily"),
				self::INTERVAL_WEEKLY		=> $lng->txt("weekly"),
				self::INTERVAL_MONTHLY		=> $lng->txt("monthly"),
				self::INTERVAL_QUARTERLY	=> $lng->txt("quarterly")
			);
		}
		
		public static function getDefaultIntervalKey()
		{
			return self::INTERVAL_DAILY;
		}
	}
	


?>
