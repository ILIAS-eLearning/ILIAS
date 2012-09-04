<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This cron deletes user accounts by INACTIVATION period
 * 
 * @author Bjoern Heyser <bheyser@databay.de>
 * @version $Id$
 *
 * @package Services/User
 */
class ilCronDeleteInactivatedUserAccounts
{
	const INTERVAL_DAILY		= '1';
	const INTERVAL_WEEKLY		= '2';
	const INTERVAL_MONTHLY		= '3';
	const INTERVAL_QUARTERLY	= '4';

	const DEFAULT_INACTIVITY_PERIOD = 365;

	private $interval = null;

	private $include_roles = null;

	private $period = null;

	private $enabled = false;

	public function __construct()
	{
		global $ilSetting;

		$this->interval = $ilSetting->get(
			'cron_inactivated_user_delete_interval',
			self::getDefaultIntervalKey()
		);

		$this->include_roles = $ilSetting->get(
			'cron_inactivated_user_delete_include_roles', null
		);
		if($this->include_roles === null) $this->include_roles = array();
		else $this->include_roles = explode(',', $this->include_roles);

		$this->period = $ilSetting->get(
			'cron_inactivated_user_delete_period',
			self::DEFAULT_INACTIVITY_PERIOD
		);

		$last_run = (int)$ilSetting->get('cron_inactivated_user_delete_last_run', 0);

		if( $ilSetting->get('cron_inactivated_user_delete', false) )
		{
			$this->enabled = false;
		}
		if( !$last_run || (time() - $last_run) > $this->getCurrentIntervalPeriod() )
		{
			$this->enabled = true;

			$ilSetting->set('cron_inactivated_user_delete_last_run', time());
		}
	}

	public function run()
	{
		if( !$this->enabled ) return;

		global $rbacreview;

		$usr_ids = ilObjUser::_getUserIdsByInactivationPeriod($this->period);

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

