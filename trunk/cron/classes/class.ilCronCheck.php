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
		/* MOVED TO: ilCronManager::runActiveJobs();
		global $ilSetting;
		
		$ilSetting->set('last_cronjob_start_ts', time());
		*/
		
		if( $_SERVER['argc'] > 4 )
		{
			 for($i = 4; $i < $_SERVER['argc']; $i++)
			 {
				$arg = $_SERVER['argv'][$i];
				
				if( !isset($this->possible_tasks[$arg]) )
					throw new ilException('cron-task "'.$arg.'" is not defined');
				
				$task = $this->possible_tasks[$arg];
				
				$this->runTask($task);
			 }
		}
		else foreach($this->default_tasks as $task)
		{
			$task = $this->possible_tasks[$task];
			
			$this->runTask($task);
		}
	}
	
	private function runTask($task)
	{
		global $ilLog;
		
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
			$ilLog->write("CRON - starting task: ".$classname."::".$method);
			
			$task = new $classname;
			$task->$method();
			
			$ilLog->write("CRON - finished task: ".$classname."::".$method);
		}
		else
		{
			$ilLog->write("CRON - task condition failed: ".$classname."::".$method);
		}
	}
	
	private function initTasks()
	{
		global $ilias;

		require_once('Services/Payment/classes/class.ilUserDefinedInvoiceNumber.php');

		$this->default_tasks = array(				
				'ilCronValidator::check'
		);

		$this->possible_tasks = array(

				// Start System Check
				'ilCronValidator::check' => array(
					'classname'		=> 'ilCronValidator',
					'method'		=> 'check',
					'location'		=> 'cron',
					'condition'		=> ($ilias->getSetting('systemcheck_cron') == 1)
				)	
		);
	}	
}
?>