<?php declare(strict_types=1);
ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

//conditions for storing data
global $DIC;
$ilDB = $DIC->database();

$packageId = $DIC->http()->wrapper()->query()->retrieve('package_id', $DIC->refinery()->kindlyTo()->int());//(int) $_GET['package_id'];
$refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
$doUnload = false;
if ($DIC->http()->wrapper()->query()->has('do')) {
    if ($DIC->http()->wrapper()->query()->retrieve('do', $DIC->refinery()->kindlyTo()->string()) == "unload") {
        $doUnload = true;
    }
}



$lm_set = $ilDB->queryF(
    'SELECT default_lesson_mode, interactions, objectives, time_from_lms, comments FROM sahs_lm WHERE id = %s',
    array('integer'),
    array($packageId)
);
while ($lm_rec = $ilDB->fetchAssoc($lm_set)) {
    $defaultLessonMode = ($lm_rec["default_lesson_mode"]);
    $interactions = ilUtil::yn2tf($lm_rec["interactions"]);
    $objectives = ilUtil::yn2tf($lm_rec["objectives"]);
    $time_from_lms = ilUtil::yn2tf($lm_rec["time_from_lms"]);
    $comments = ilUtil::yn2tf($lm_rec["comments"]);
}

if ($doUnload) {
    ilSCORM2004StoreData::scormPlayerUnload($packageId, $refId, $time_from_lms, null);
} else {
//    $data = file_get_contents('php://input');
    ilSCORM2004StoreData::persistCMIData($packageId, $refId, $defaultLessonMode, $comments, $interactions, $objectives, $time_from_lms, null, null);
}
