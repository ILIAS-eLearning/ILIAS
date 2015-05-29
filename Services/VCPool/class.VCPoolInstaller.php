<?php

class VCPoolInstaller {
	public static function step_1($ilDB) {
		if ( $ilDB->tableExists(ilVCPool::URL_POOL_TABLE) ) {
			return;
		}
	
		$ilDB->createTable(ilVCPool::URL_POOL_TABLE, array(
			"id" => array(
				"type"		=> "integer",
				"length"	=> 4,
				"notnull"	=> true
			),
			"url" => array(
				"type"		=> "text",
				"length"	=> "100",
				"notnull"	=> true
			),
			"vc_type" => array(
				"type"		=> "text",
				"length"	=> "20",
				"notnull"	=> true
			)
		));
		
		$ilDB->addPrimaryKey(ilVCPool::URL_POOL_TABLE, array("id"));
	}
	
	public static function step_2($ilDB) {
		if ( $ilDB->tableExists(ilVCPool::ASSIGNMENT_TABLE) ) {
			return;
		}
		
		$ilDB->createTable(ilVCPool::ASSIGNMENT_TABLE, array(
			"id" => array(
				"type"		=> "integer",
				"length"	=> 4,
				"notnull"	=> true
			),
			"vc_id" => array(
				"type"		=> "integer",
				"length"	=> 4,
				"notnull"	=> true
			),
			"obj_id" => array(
				"type"		=> "integer",
				"length"	=> 4,
				"notnull"	=> true
			),
			"ts_start" => array(
				"type"		=> "timestamp",
				"notnull"	=> true
			),
			"ts_end" => array(
				"type"		=> "timestamp",
				"notnull"	=> true
			)
		));
		
		$ilDB->addPrimaryKey(ilVCPool::ASSIGNMENT_TABLE, array("id"));
	}
	
	public static function step_3($ilDB) {
		$ilDB->createSequence(ilVCPool::ASSIGNMENT_TABLE);
	}
	
	public static function allSteps($ilDB) {
		require_once("Services/VCPool/classes/class.ilVCPool.php");
		
		self::step_1($ilDB);
		self::step_2($ilDB);
		self::step_3($ilDB);
	}
}

?>