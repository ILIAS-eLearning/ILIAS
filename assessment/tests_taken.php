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
* assessment test script used to call the test objects
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package assessment
*/
define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";
require_once "./assessment/classes/class.ilObjTestGUI.php";

// for security
unset($id);
global $tpl;
global $lng;
global $ilUser;
global $ilDB;

$lng->loadLanguageModule("assessment");
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
// catch feedback message
sendInfo();
$ilias_locator = new ilLocatorGUI(false);
$i = 1;
$ilias_locator->navigate($i++, $lng->txt("personal_desktop"), ILIAS_HTTP_PATH . "/usr_personaldesktop.php", "bottom");
$ilias_locator->navigate($i++, $lng->txt("tst_already_taken"), ILIAS_HTTP_PATH . "/assessment/tests_taken.php", "bottom");
$ilias_locator->output();
$tpl->setVariable("HEADER", $lng->txt("tst_already_taken"));
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_taken.html", true);
$q = sprintf("SELECT tst_active.tries, tst_active.test_fi, tst_tests.nr_of_tries, tst_tests.ref_fi FROM tst_active, tst_tests WHERE tst_active.user_fi = %s AND tst_active.test_fi = tst_tests.test_id",
	$ilDB->quote($ilUser->id)
);
$result = $ilDB->query($q);
$taken_array = array();
while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
	$test = new ilObjTestGUI("", $row->ref_fi, true, false);
	$test->object->load_from_db();
	$array_result =& $test->object->get_test_result($ilUser->id);
	$mark = $test->object->mark_schema->get_matching_mark(100*($array_result["test"]["total_reached_points"]/$array_result["test"]["total_max_points"]));
//	$array_result["test"]["nr_of_tries"] = $row->nr_of_tries;
	$array_result["test"]["used_tries"] = $row->tries;
	$array_result["test"]["mark"] = $mark->get_official_name();
	array_push($taken_array, $array_result);
}

$counter = 0;
$classes = array("tblrow1", "tblrow2");
$test_types = array(
	"1" => "tt_assessment",
	"2" => "tt_self_assessment",
	"3" => "tt_navigation_controlling"
);

foreach ($taken_array as $key => $value) {
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("COLOR_CLASS", $classes[$counter % 2]);
	$tpl->setVariable("TEST_TYPE", $lng->txt($test_types[$value["test"]["test"]->get_test_type()]));
	$status_image = "";
	$resume = "";
	if ($value["test"]["used_tries"] == 0) {
		// test is not completed
		$status_image = $lng->txt("tst_status_progress");
		$resume = " [<a href=\"" . ILIAS_HTTP_PATH . "/assessment/test.php?ref_id=" . $value["test"]["test"]->ref_id . "&cmd=run\">" . $lng->txt("tst_resume_test") . "]";
	} elseif (($value["test"]["test"]->get_nr_of_tries() > 0) and ($value["test"]["used_tries"] == $value["test"]["test"]->get_nr_of_tries())) {
		// test is completed
		$status_image = $lng->txt("tst_status_completed");
	} else {
		// test is completed but can be completed again
		$status_image = $lng->txt("tst_status_completed_more_tries_possible");
		$resume = " [<a href=\"" . ILIAS_HTTP_PATH . "/assessment/test.php?ref_id=" . $value["test"]["test"]->ref_id . "&cmd=run\">" . $lng->txt("tst_resume_test") . "]";
	}
	$tpl->setVariable("TEST_STATUS", $status_image);
	if (!strcmp($status_image, $lng->txt("tst_status_progress"))) {
		$tpl->setVariable("TEST_MARK", "");
	} else {
		$tpl->setVariable("TEST_MARK", $value["test"]["mark"]);
	}
	$tpl->setVariable("TEST_TITLE", $value["test"]["test"]->getTitle() . $resume);
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("adm_content");
$tpl->setVariable("TEST_TITLE", $lng->txt("title"));
$tpl->setVariable("TEST_TYPE", $lng->txt("tst_type"));
$tpl->setVariable("TEST_STATUS", $lng->txt("status"));
$tpl->setVariable("TEST_MARK", $lng->txt("tst_mark"));

$tpl->parseCurrentBlock();
$tpl->show();
?>
