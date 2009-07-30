<?php

// copy this file to "cfg.loadtest.php", enter your values,
// change to scripts dir and call
// > ./run_loadtest ../config/cfg.loadtest.php
// from command line

// complete path to jmeter directory (no trailing /)
$jmeter_base = "/opt/jmeter_2_3_4";

// web server host name
$web_host = "welles.local";

// complete path of ilias web directory (no trailing /)
$iliaswebdir = "/htdocs/ilias3";

// relative path of ilias web directory in web server web space
// no preceding or trailing /
$iliasrelwebdir = "ilias3";

// ilias client name
$client = "loadtest";

// ilias db connection credentials
$db_host = "localhost";
$db_user = "root";
$db_pw = "";
$db_name = "loadtest";



// test programme
$tests = array(
		array(
			"title" => "First Test",
			"testplan" => "/htdocs/ilias3/Services/LoadTest/jmx/test1.jmx",
			"jtlfile" => "/htdocs/ilias3/Services/LoadTest/results/test1.jtl",
			"threads" => "1",
			"loops" => "1",
			"ramp_up" => "1",
			"parameter" => array(
				"usercsv" => "/htdocs/ilias3/Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/user.csv",
				"catcsv" => "/htdocs/ilias3/Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/cat.csv",
				"crscsv" => "/htdocs/ilias3/Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/crs.csv"
				)
			)
	);

?>