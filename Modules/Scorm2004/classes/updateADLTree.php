<?

	require_once("adlparser/SeqTreeBuilder.php");
	require_once("../../../classes/class.ilIniFile.php");
	
	//find all folders containing data
	//initIliasIniFile()
	
	$ini=new ilIniFile("../../../ilias.ini.php");
	$ini->read();
	
	//define constants
    define("ILIAS_WEB_DIR",$ini->readVariable("clients","path"));
    define("ILIAS_ABSOLUTE_PATH",$ini->readVariable('server','absolute_path'));
	define("ILIAS_CLIENT_ID",$ini->readVariable('clients','default'));
	define("ILIAS_CLIENT_INI",$ini->readVariable('clients','inifile'));
	
	//build datapath for this ilias installation
	$search_path=ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".ILIAS_CLIENT_ID."/lm_data";
	
	//client ini
	$client_ini=ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".ILIAS_CLIENT_ID."/".ILIAS_CLIENT_INI;

	//get db connection data from client_ini
	$cini=new ilIniFile($client_ini);
	$cini->read();
	define("ILIAS_DB_NAME",$cini->readVariable("db","name"));
	define("ILIAS_DB_HOST",$cini->readVariable("db","host"));	
	define("ILIAS_DB_USER",$cini->readVariable("db","user"));
	define("ILIAS_DB_PASS",$cini->readVariable("db","pass"));
	define("ILIAS_DB_TYPE",$cini->readVariable("db","type"));
	
	//init db-connection

	$dbh = mysql_connect(ILIAS_DB_HOST, ILIAS_DB_USER, ILIAS_DB_PASS)  or die("Unable to connect to MySQL");
	$sel_db = mysql_select_db(ILIAS_DB_NAME,$dbh);
	
	//start directory processing - one level only
	
	$dirs=array();
	$d = dir($search_path);
    while (false !== ($entry = $d->read())) {
        if($entry != '.' && $entry != '..' && is_dir($search_path."/".$entry)) {
			array_push($dirs, $entry);
		}
    }
    $d->close();
	
	//iterate over array
	
	for ($i=0;$i<count($dirs);$i++) {
		//check for imsmanifest
		$toparse=$search_path."/".$dirs[$i]."/imsmanifest.xml";
		if (is_file($toparse)) {
			//check for DB entry
			//get id
			$id = $webdir=str_replace("lm_","",$dirs[$i]);	
			$result = mysql_query("SELECT * FROM cp_package WHERE(obj_id=$id)");
			$row = mysql_fetch_array($result,MYSQL_ASSOC);
			if (count($row)>0 && strlen($row['jsdata'])>10) {
				//create new parser
				$builder=new SeqTreeBuilder();
				$ret=$builder->buildNodeSeqTree($toparse);
				$global=$ret['global'];
				$adltree=mysql_escape_string(json_encode($ret['tree']));
				$result_update = mysql_query("UPDATE cp_package SET activitytree='$adltree',global_to_system='$global' WHERE(obj_id=$id)") or die(mysql_error());
				echo "Updated activitytree for: ".$dirs[$i]." Global:".$global." \n";
			}
		}
	}

	mysql_close($dbh);

?>