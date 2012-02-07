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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/

class ilCronCheck
{
	private $possible_tasks = array();
	private $default_tasks = array();
	
	public function ilCronCheck()
	{
		global $ilLog;

		$this->log = $ilLog;

		$this->initTasks();
	}
	
	public function start()
	{
		global $ilSetting;
		
		$ilSetting->set('last_cronjob_start_ts', time());
		
		if( $_SERVER['argc'] > 4 ) for($i = 4; $i < $_SERVER['argc']; $i++)
		{
				$arg = $_SERVER['argv'][$i];
				
				if( !isset($this->possible_tasks[$arg]) )
					throw new ilException('cron-task "'.$arg.'" is not defined');
				
				$task = $this->possible_tasks[$arg];
				
				$this->runTask($task);
		}
		else foreach($this->default_tasks as $task)
		{
			$task = $this->possible_tasks[$task];
			
			$this->runTask($task);
		}
	}
	
	private function runTask($task)
	{
		/**
		 * prepare task information
		 */

		$classlocation = $task['location'].'/classes';
		if( isset($task['sub_location']) && strlen($task['sub_location']) )
		{
			$classlocation .= '/'.$task['sub_location'];
		}
		$classfile .= $classlocation.'/class.'.$task['classname'].'.php';

		$classname = $task['classname'];
		$method = $task['method'];

		$condition = $task['condition'];

		/**
		 * check if task is runable
		 */

		if( !file_exists($classfile) )
			throw new ilException('class file "'.$classfile.'" does not exist');
		
		require_once($classfile);
		
		if( !class_exists($classname) )
			throw new ilException('class "'.$classname.'" does not exist');
		
		if( !method_exists($classname, $method) )
			throw new ilException('method "'.$classname.'::'.$method.'()" does not exist');

		/**
		 * run task
		 */


		if($condition)
		{
			$task = new $classname;
			$task->$method();
		}
	}
	
	private function initTasks()
	{
		global $ilias;

		require_once('Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php');
		require_once('Services/Payment/classes/class.ilUserDefinedInvoiceNumber.php');

		$this->default_tasks = array(
				'ilLDAPCronSynchronization::start',
				'ilCronCheckUserAccounts::check',
				'ilLuceneIndexer::index',
				'ilCronLinkCheck::check',
				'ilCronWebResourceCheck::check',
				'ilCronForumNotification::sendNotifications',
				'ilCronMailNotification::sendNotifications',
				'ilCronValidator::check',
				'ilCronDiskQuotaCheck::updateDiskUsageStatistics',
				'ilCronDiskQuotaCheck::sendReminderMails',
				// This entry refers to a task that is not completely implemented
				#'ilPaymentShoppingCart::__deleteExpiredSessionsPSC',
				'ilCronDeleteInactiveUserAccounts::run',
				'ilCronPaymentNotification::sendNotifications',
				'ilCronCourseGroupNotification::check',
				'ilCronPaymentUDInvoiceNumberReset::check',
				'ilCronObjectStatisticsCheck::check'
		);

		$this->possible_tasks = array(

				'ilLDAPCronSynchronization::start' => array(
					'classname'		=> 'ilLDAPCronSynchronization',
					'method'		=> 'start',
					'location'		=> 'Services/LDAP',
					'condition'		=> true
				),
				
				// Check user accounts if enabled in settings
				'ilCronCheckUserAccounts::check' => array(
					'classname'		=> 'ilCronCheckUserAccounts',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> $ilias->getSetting('cron_user_check')
				),
				
				// Start lucene indexer
				'ilLuceneIndexer::index' => array(
					'classname'		=> 'ilLuceneIndexer',
					'method'		=> 'index',
					'location'		=> 'Services/Search',
					'sub_location'	=> 'Lucene',
					'condition'		=> $ilias->getSetting("cron_lucene_index")
				),
				
				// Start Link check
				'ilCronLinkCheck::check' => array(
					'classname'		=> 'ilCronLinkCheck',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> $ilias->getSetting("cron_link_check")
				),
				
				// Start web resource check
				'ilCronWebResourceCheck::check' => array(
					'classname'		=> 'ilCronWebResourceCheck',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> $ilias->getSetting("cron_web_resource_check")
				),
				
				// Start sending forum notifications
				'ilCronForumNotification::sendNotifications' => array(
					'classname'		=> 'ilCronForumNotification',
					'method'		=> 'sendNotifications',
					'location'		=> 'cron',
					'condition'		=> ($ilias->getSetting('forum_notification') == 2)
				),
				
				// Start sending mail notifications
				'ilCronMailNotification::sendNotifications' => array(
					'classname'		=> 'ilCronMailNotification',
					'method'		=> 'sendNotifications',
					'location'		=> 'cron',
					'condition'		=> ($ilias->getSetting('mail_notification') == 1)
				),
				
				// Start System Check
				'ilCronValidator::check' => array(
					'classname'		=> 'ilCronValidator',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> ($ilias->getSetting('systemcheck_cron') == 1)
				),
				
				// Start Disk Quota Usage Statistics
				'ilCronDiskQuotaCheck::updateDiskUsageStatistics' => array(
					'classname'		=> 'ilCronDiskQuotaCheck',
					'method'		=> 'updateDiskUsageStatistics',
					'location'		=> 'cron',
					'condition'		=> ilDiskQuotaActivationChecker::_isActive()
				),
				
				// Send Disk Quota Reminder Mails
				'ilCronDiskQuotaCheck::sendReminderMails' => array(
					'classname'		=> 'ilCronDiskQuotaCheck',
					'method'		=> 'sendReminderMails',
					'location'		=> 'cron',
					'condition'		=> ilDiskQuotaActivationChecker::_isReminderMailActive()
				),
				
				// Send Disk Quota Summary Mails
				'ilCronDiskQuotaCheck::sendSummaryMails' => array(
					'classname'		=> 'ilCronDiskQuotaCheck',
					'method'		=> 'sendSummaryMails',
					'location'		=> 'cron',
					'condition'		=> ilDiskQuotaActivationChecker::_isSummaryMailActive()
				),
				
				/**
				 * This task entry refers to a method that does not exist!
				 * When the method will be implemented it has to be non static!
				 */
				#// Start Shopping Cart Check
				#'ilPaymentShoppingCart::__deleteExpiredSessionsPSC' => array(
				#	'classname'		=> 'ilPaymentShoppingCart',
				#	'method'		=> '__deleteExpiredSessionsPSC',
				#	'location'		=> 'Services/Payment',
				#	'condition'		=> true
				#),

				// Delete Inactive User Accounts
				'ilCronDeleteInactiveUserAccounts::run' => array(
					'classname'		=> 'ilCronDeleteInactiveUserAccounts',
					'method'		=> 'run',
					'location'		=> 'Services/User',
					'condition'		=> $ilias->getSetting('cron_inactive_user_delete', 0)
				),

				// Start sending Payment "Buy Extension" Reminder
				'ilCronPaymentNotification::sendNotifications' => array(
					'classname'		=> 'ilCronPaymentNotification',
					'method'		=> 'sendNotifications',
					'location'		=> 'cron',
					'condition'		=> ($ilias->getSetting('payment_notifications') == 1)
				),

				// Start course group notification check
				'ilCronCourseGroupNotification::check' => array(
					'classname'		=> 'ilCronCourseGroupNotification',
					'method'		=> 'sendNotifications',
					'location'		=> 'cron',
					'condition'		=> $ilias->getSetting("crsgrp_ntf")
				),

				// Reset payment incremental invoice number
				'ilCronPaymentUDInvoiceNumberReset::check' => array(
					'classname'		=> 'ilCronPaymentUDInvoiceNumberReset',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> ilUserDefinedInvoiceNumber::_isUDInvoiceNumberActive()
				),
			
				// (object) statistics
				'ilCronObjectStatisticsCheck::check' => array(
					'classname'		=> 'ilCronObjectStatisticsCheck',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> true
				)
		);
	}	
}
?>