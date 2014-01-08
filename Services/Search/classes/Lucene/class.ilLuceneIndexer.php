<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
* Class for indexing hmtl ,pdf, txt files and htlm Learning modules.
* This indexer is called by cron.php
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @package ServicesSearch
*/
class ilLuceneIndexer extends ilCronJob
{
	public function getId()
	{
		return "src_lucene_indexer";
	}
	
	public function getTitle()
	{
		global $lng;
		
		return $lng->txt("cron_lucene_index");
	}
	
	public function getDescription()
	{
		global $lng;
		
		return $lng->txt("cron_lucene_index_info");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return true;
	}
	
	public function run()
	{				
		global $ilSetting;
		
		$status = ilCronJobResult::STATUS_NO_ACTION;		
		$error_message = null;
		
		try
		{
			include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
			ilRpcClientFactory::factory('RPCIndexHandler')->index(
				CLIENT_ID.'_'.$ilSetting->get('inst_id',0),
				true
			);
		}
		catch(XML_RPC2_FaultException $e)
		{
			$error_message = $e->getMessage();
		}
		catch(Exception $e)
		{
			$error_message = $e->getMessage();
		}
		
		$result = new ilCronJobResult();
		if($error_message)
		{
			$result->setMessage($error_message);
			$status = ilCronJobResult::STATUS_CRASHED;
		}
		else
		{
			$status = ilCronJobResult::STATUS_OK;
		}			
		$result->setStatus($status);		
		return $result;
	}
	
	
	/**
	 * Update lucene index
	 * @param type $a_obj_ids
	 */
	public static function updateLuceneIndex($a_obj_ids)
	{
		global $ilSetting;
		
		include_once './Services/Search/classes/class.ilSearchSettings.php';
		if(!ilSearchSettings::getInstance()->isLuceneUserSearchEnabled())
		{
			return false;
		}
		
		try
		{
			include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
			ilRpcClientFactory::factory('RPCIndexHandler')->indexObjects(
				CLIENT_ID.'_'.$ilSetting->get('inst_id',0),
				$a_obj_ids
			);
		}
		catch(XML_RPC2_FaultException $e)
		{
			$error_message = $e->getMessage();
			$GLOBALS['ilLog']->write(__METHOD__.': '.$e->getMessage());
		}
		catch(Exception $e)
		{
			$error_message = $e->getMessage();
			$GLOBALS['ilLog']->write(__METHOD__.': '.$e->getMessage());
		}
		
	}
	
}

?>