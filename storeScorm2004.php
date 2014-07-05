<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * for storing Data also without session
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 */


include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SCORM);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

include_once 'Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';


if ((string) $_GET['do'] == "unload") {
	include_once 'Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
	ilSCORM2004StoreData::scormPlayerUnload(null, (int)$_GET['package_id']);
} else {
	global $ilLog, $ilDB, $ilUser;
	$packageId=(int)$_GET['package_id'];
	$lm_set = $ilDB->queryF('SELECT default_lesson_mode, interactions, objectives, comments FROM sahs_lm WHERE id = %s', 
		array('integer'),array($packageId));
	
	while($lm_rec = $ilDB->fetchAssoc($lm_set))
	{
		$defaultLessonMode=($lm_rec["default_lesson_mode"]);
		$interactions=(ilUtil::yn2tf($lm_rec["interactions"]));
		$objectives=(ilUtil::yn2tf($lm_rec["objectives"]));
		$comments=(ilUtil::yn2tf($lm_rec["comments"]));
	}
	$data = file_get_contents('php://input');
	$ilUser->setId($data->p);

	//until now only 2004
	include_once 'Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
	ilSCORM2004StoreData::persistCMIData(null, $packageId, $defaultLessonMode, $comments, $interactions, $objectives, $data);
}


?>