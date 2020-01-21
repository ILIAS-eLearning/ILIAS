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

//conditions for storing data
global $ilDB;
$packageId=(int) $_GET['package_id'];
$lm_set = $ilDB->queryF(
    'SELECT default_lesson_mode, interactions, objectives, time_from_lms, comments FROM sahs_lm WHERE id = %s',
    array('integer'),
    array($packageId)
);
while ($lm_rec = $ilDB->fetchAssoc($lm_set)) {
    $defaultLessonMode=($lm_rec["default_lesson_mode"]);
    $interactions=(ilUtil::yn2tf($lm_rec["interactions"]));
    $objectives=(ilUtil::yn2tf($lm_rec["objectives"]));
    $time_from_lms=(ilUtil::yn2tf($lm_rec["time_from_lms"]));
    $comments=(ilUtil::yn2tf($lm_rec["comments"]));
}

if ((string) $_GET['do'] == "unload") {
    ilSCORM2004StoreData::scormPlayerUnload(null, $packageId, $time_from_lms);
} else {
    global $ilUser;
    $data = file_get_contents('php://input');
    $ilUser->setId($data->p);

    //until now only 2004
    ilSCORM2004StoreData::persistCMIData(null, $packageId, $defaultLessonMode, $comments, $interactions, $objectives, $time_from_lms, $data);
}
