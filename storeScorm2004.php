<?php declare(strict_types=1);
ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

//conditions for storing data
global $ilDB;
$packageId = (int) $_GET['package_id'];
$lm_set = $ilDB->queryF(
    'SELECT default_lesson_mode, interactions, objectives, time_from_lms, comments FROM sahs_lm WHERE id = %s',
    array('integer'),
    array($packageId)
);
while ($lm_rec = $ilDB->fetchAssoc($lm_set)) {
    $defaultLessonMode = ($lm_rec["default_lesson_mode"]);
    $interactions = (ilUtil::yn2tf($lm_rec["interactions"]));
    $objectives = (ilUtil::yn2tf($lm_rec["objectives"]));
    $time_from_lms = (ilUtil::yn2tf($lm_rec["time_from_lms"]));
    $comments = (ilUtil::yn2tf($lm_rec["comments"]));
}

if ((string) $_GET['do'] == "unload") {
    ilSCORM2004StoreData::scormPlayerUnload($packageId, $time_from_lms, null);
} else {
//    $data = file_get_contents('php://input');
    ilSCORM2004StoreData::persistCMIData($packageId, $defaultLessonMode, $comments, $interactions, $objectives, $time_from_lms, null, null);
}
