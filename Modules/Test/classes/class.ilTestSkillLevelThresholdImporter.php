<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdImporter
{
	/**
	 * @var integer
	 */
	protected $importInstallationId = null;
	
	/**
	 * @var ilImportMapping
	 */
	protected $importMappingRegistry = null;

	/**
	 * @var integer
	 */
	protected $targetTestId = null;
	
	/**
	 * @var ilTestSkillLevelThresholdImportList
	 */
	protected $thresholdList = null;
	
	/**
	 * @return int
	 */
	public function getImportInstallationId()
	{
		return $this->importInstallationId;
	}
	
	/**
	 * @param int $importInstallationId
	 */
	public function setImportInstallationId($importInstallationId)
	{
		$this->importInstallationId = $importInstallationId;
	}
	
	/**
	 * @return ilImportMapping
	 */
	public function getImportMappingRegistry()
	{
		return $this->importMappingRegistry;
	}
	
	/**
	 * @param ilImportMapping $importMappingRegistry
	 */
	public function setImportMappingRegistry($importMappingRegistry)
	{
		$this->importMappingRegistry = $importMappingRegistry;
	}
	
	/**
	 * @return int
	 */
	public function getTargetTestId()
	{
		return $this->targetTestId;
	}
	
	/**
	 * @param int $targetTestId
	 */
	public function setTargetTestId($targetTestId)
	{
		$this->targetTestId = $targetTestId;
	}
	
	/**
	 * @return ilTestSkillLevelThresholdImportList
	 */
	public function getThresholdList()
	{
		return $this->thresholdList;
	}
	
	/**
	 * @param ilTestSkillLevelThresholdImportList $thresholdList
	 */
	public function setThresholdList($thresholdList)
	{
		$this->thresholdList = $thresholdList;
	}
	

	/*
	include_once("./Services/Skill/classes/class.ilBasicSkill.php");
	$r = ilBasicSkill::getLevelIdForImportId($a_source_inst_id,
	$a_level_import_id);
	
	$results[] = array("level_id" => $rec["id"], "creation_date" =>
	$rec["creation_date"]);
	*/
	
	/**
	 * @return bool
	 */
	public function	import()
	{
		return true;
	}
}