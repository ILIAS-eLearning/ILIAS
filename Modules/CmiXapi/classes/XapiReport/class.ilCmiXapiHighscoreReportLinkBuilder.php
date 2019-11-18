<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiHighscoreReportLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiHighscoreReportLinkBuilder extends ilCmiXapiAbstractReportLinkBuilder
{
	/**
	 * @return array
	 */
	protected function buildPipeline() : array
	{
		$pipeline = [];
		
		$pipeline[] = $this->buildFilterStage();
		$pipeline[] = $this->buildOrderStage();
		
		$pipeline[] = ['$group' => [
			'_id' => '$statement.actor.mbox',
			'mbox' => [ '$last' => '$statement.actor.mbox' ],
			'username' => [ '$last' => '$statement.actor.name' ],
			'timestamp' => [ '$last' => '$statement.timestamp' ],
			'duration' => [ '$push' => '$statement.result.duration' ],
			'score' => [ '$last' => '$statement.result.score' ]
		]];
		
		return $pipeline;
	}
	
	protected function buildFilterStage()
	{
		$stage = array();
		
		$stage['statement.object.objectType'] = 'Activity';
		$stage['statement.object.id'] = $this->filter->getActivityId();
		
		$stage['statement.result.score.scaled'] = [
			'$exists' => 1
		];
		
		$stage['statement.actor.objectType'] = 'Agent';
		
		$stage['$or'] = $this->getUsersStack();
		
		return [
			'$match' => $stage
		];
	}
	
	protected function buildOrderStage()
	{
		return [ '$sort' => [
			'statement.timestamp' => 1
		]];
	}
	
	protected function getUsersStack()
	{
		$users = [];
		
		foreach(ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser)
		{
			$users[] = [
				'statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"
			];
		}
		
		return $users;
	}
	
	public function getPipelineDebug()
	{
		return '<pre>'.json_encode($this->buildPipeline(), JSON_PRETTY_PRINT).'</pre>';
	}
}
