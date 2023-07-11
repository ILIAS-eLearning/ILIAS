<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once("Services/Init/classes/class.ilInitialisation.php");
ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

//conditions for storing data
global $DIC;
$ilDB = $DIC->database();

$packageId = $DIC->http()->wrapper()->query()->retrieve('package_id', $DIC->refinery()->kindlyTo()->int());
$refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
$doUnload = false;
if ($DIC->http()->wrapper()->query()->has('do')) {
    if ($DIC->http()->wrapper()->query()->retrieve('do', $DIC->refinery()->kindlyTo()->string()) == "unload") {
        $doUnload = true;
    }
}

$defaultLessonMode = "normal";
$comments = true;
$interactions = true;
$objectives = true;
$time_from_lms = false;

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
