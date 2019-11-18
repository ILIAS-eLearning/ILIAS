<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiStatementsReport
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReport
{
	/**
	 * @var array
	 */
	protected $response;
	
	/**
	 * @var array
	 */
	protected $statements;
	
	/**
	 * @var int
	 */
	protected $maxCount;
	
	/**
	 * @var ilCmiXapiUser[]
	 */
	protected $cmixUsersByIdent;
	
	public function __construct(string $responseBody, $objId)
	{
		$responseBody = json_decode($responseBody, true);
		
		if( count($responseBody) )
		{
			$this->response = current($responseBody);
			$this->statements = $this->response['statements'];
			$this->maxCount = $this->response['maxcount'];
		}
		else
		{
			$this->response = '';
			$this->statements = array();
			$this->maxCount = 0;
		}
		
		foreach(ilCmiXapiUser::getUsersForObject($objId) as $cmixUser)
		{
			$this->cmixUsersByIdent[$cmixUser->getUsrIdent()] = $cmixUser;
		}
	}
	
	public function getMaxCount()
	{
		return $this->maxCount;
	}
	
	public function getStatements()
	{
		return $this->statements;
	}
	
	public function hasStatements()
	{
		return (bool)count($this->statements);
	}
	
	public function getTableData()
	{
		$data = [];
		
		foreach($this->statements as $index => $statement)
		{
			$data[] = [
				'date' => $this->fetchDate($statement),
				'actor' => $this->fetchActor($statement),
				'verb_id' => $this->fetchVerbId($statement),
				'verb_display' => $this->fetchVerbDisplay($statement),
				'object' => $this->fetchObjectName($statement),
				'object_info' => $this->fetchObjectInfo($statement),
				'statement' => json_encode($statement, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
			];
		}
		
		return $data;
	}
	
	protected function fetchDate($statement)
	{
		return $statement['timestamp'];
	}
	
	protected function fetchActor($statement)
	{
		$ident = str_replace('mailto:', '', $statement['actor']['mbox']);
		return $this->cmixUsersByIdent[$ident];
	}
	
	protected function fetchVerbId($statement)
	{
		return $statement['verb']['id'];
	}
	
	protected function fetchVerbDisplay($statement)
	{
		return $statement['verb']['display']['en-US'];
	}
	
	protected function fetchObjectName($statement)
	{
		return $statement['object']['definition']['name']['en-US'];
	}
	
	protected function fetchObjectInfo($statement)
	{
		return $statement['object']['definition']['description']['en-US'];
	}
}
