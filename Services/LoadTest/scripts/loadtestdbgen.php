<?php


// This is an example file how a test db and csv files can be generated
// for a load test

chdir("../../..");
include_once("./include/inc.header.php");
include_once("./Services/LoadTest/classes/class.ilDataLoader.php");
	
$loader = new ilDataLoader();
$loader->setEnableLog(true);
//$loader->loadSqlTemplate("./Services/LoadTest/data/usr_1000/db.sql");
//$loader->generateUsers("learner", 1, 1000);
//$loader->generateCategories(1, 200, 5);
//$loader->generateCourses(1, 300, 5);
//$loader->generateFiles("/tmp/test.txt", 40);
//$loader->generateCalendarEntries(30);
//$loader->writeUserCsv("./Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/user.csv");
//$loader->writeCourseCsv("./Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/crs.csv");
//$loader->writeCategoryCsv("./Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/cat.csv");
//$loader->assignUsersAsCourseMembers("learner", 1, 100);
//$loader->removeAllDesktopItems();
//$loader->deactivateCalendarsOnPersonalDesktop();
//$loader->createDump("./Services/LoadTest/data/cat_200_crs_300_file_40_cal_30_news_1/db.sql",
//	"mysqldump");

?>