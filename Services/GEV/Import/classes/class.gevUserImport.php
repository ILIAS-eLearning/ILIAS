<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/



//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/Import/classes', '', $basedir);
chdir($basedir);

//SIMPLE SEC !
require "./Customizing/global/skin/genv/Services/GEV/simplePwdSec.php";

//context w/o user
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


//settings and imports
ini_set("memory_limit","512M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);
require_once("Services/GEV/Import/classes/class.gevImportedUser.php");



class gevUserImport {
	
	private $shadowDB = NULL;

	public function __construct() {
		$this->connectShadowDB();
		$this->createDB();
	}


	private function connectShadowDB(){
		global $ilClientIniFile;
		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$this->shadowDB = $mysql;
	}



	private function createDB(){
		//orgunits
		$sql = "CREATE TABLE IF NOT EXISTS interimOrgunits ("
			." orgu_id int(11) NOT NULL,"
			." name varchar(64) COLLATE utf8_unicode_ci NOT NULL,"
			." parent int(11) NOT NULL,"
			." PRIMARY KEY (orgu_id)"
			." ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

		$result = mysql_query($sql, $this->shadowDB);
		
		//usr/org_units
		$sql = "CREATE TABLE IF NOT EXISTS interimUserOrgunits ("
			." usr_id int(11) NOT NULL,"
			." orgu_id int(11) NOT NULL,"
			." PRIMARY KEY (usr_id)"
			." ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

		$result = mysql_query($sql, $this->shadowDB);
		
		//roles
		$sql = "CREATE TABLE IF NOT EXISTS interimRoles ("
			." usr_id int(11) NOT NULL,"
			." role varchar(64) COLLATE utf8_unicode_ci NOT NULL,"
			." PRIMARY KEY (usr_id)"
			." ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

		$result = mysql_query($sql, $this->shadowDB);

		//users
		$fields = gevImportedUser::$USERFIELDS;
		$fstring = implode(' varchar(128) DEFAULT NULL,', $fields);
		$fstring .= ' varchar(128) DEFAULT NULL,';

		$sql = "CREATE TABLE IF NOT EXISTS interimUsers ("
			." id int(11) NOT NULL AUTO_INCREMENT,"
			.$fstring
		  	." PRIMARY KEY (id)"
			." ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";

		$result = mysql_query($sql, $this->shadowDB);
	}


	private function storeUsersToInterimsDB($users) {
		$fields = gevImportedUser::$USERFIELDS;
		foreach ($users as $usr) {
			$sql = "INSERT INTO interimUsers ("
				.implode(', ', $fields)
				.") VALUES ('"
				.implode("', '", $usr->userdata)
				."')";

			$result = mysql_query($sql, $this->shadowDB);
			if(!$result){
				print $sql;
				die("ERROR WHILE DOING QUERY ABOVE");
			}
		}
	}

	public function fetchVFSUsers(){
		require_once("Services/GEV/Import/classes/class.gevFetchVFSUser.php");
		$fetcher = new gevFetchFVSUser();
		$users = $fetcher->fetchUsers();

		$this->storeUsersToInterimsDB($users);
		$fetcher->updateOrgUnitNameForImportedUsers();
	}

	public function fetchGEVUsers(){
		require_once("Services/GEV/Import/classes/class.gevFetchGEVUser.php");
		$fetcher = new gevFetchGEVUser();
		$users = $fetcher->fetchUsers();
		
		$this->storeUsersToInterimsDB($users);
		$fetcher->updateOrgUnitNameForImportedUsers();
	}






}


$imp = new gevUserImport();

print '<pre>';


$imp->fetchGEVUsers();
print 'done GEV-users.<br>';

$imp->fetchVFSUsers();
print 'done VFS-users.<br>';



print '<br><br>ok.';
